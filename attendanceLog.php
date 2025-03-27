<?php
date_default_timezone_set('Asia/Manila');
include 'db/db_conn.php';

// Function to insert default records for all employees
function insertDefaultRecords($conn, $currentDate) {
    // Fetch all employees from employee_register
    $query = "SELECT employee_id FROM employee_register";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $employeeId = $row['employee_id'];

            // Check if the employee already has an attendance record for today
            $checkQuery = "SELECT * FROM attendance_log WHERE employee_id = ? AND attendance_date = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("ss", $employeeId, $currentDate);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            // If no record exists, insert a default record
            if ($checkResult->num_rows == 0) {
                // Check if the current date is a holiday
                if (isHoliday($conn, $currentDate)) {
                    $status = 'Holiday'; // Set status to 'Holiday'
                } 
                // Check if the employee is on leave
                elseif (isOnLeave($conn, $employeeId, $currentDate)) {
                    $status = 'On Leave'; // Set status to 'Leave'
                } 
                // Otherwise, set status to 'Absent'
                else {
                    $status = 'Absent';
                }

                // Insert the record with the appropriate status
                $insertQuery = "INSERT INTO attendance_log (employee_id, attendance_date, status) VALUES (?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param("sss", $employeeId, $currentDate, $status);

                if (!$insertStmt->execute()) {
                    error_log("Failed to insert default record for employee ID: $employeeId");
                }
            }
        }
    }
}

// Function to check if the current date is a holiday
function isHoliday($conn, $currentDate) {
    $query = "SELECT * FROM non_working_days WHERE date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0; // Returns true if the date is a holiday
}

// Function to check if the employee is on leave
function isOnLeave($conn, $employeeId, $currentDate) {
    $query = "SELECT * FROM leave_requests WHERE employee_id = ? AND ? BETWEEN start_date AND end_date";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $employeeId, $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0; // Returns true if the employee is on leave
}

// Current date and time
$currentDate = date('Y-m-d');
$currentTime = date('Y-m-d H:i:s');

// Insert default records for all employees
insertDefaultRecords($conn, $currentDate);

if (isset($_POST['employeeId'])) {
    $employeeId = $_POST['employeeId']; // employee_id is VARCHAR

    // Check if the current date is a holiday
    $isHoliday = isHoliday($conn, $currentDate);

    // Check if the employee is on leave
    if (isOnLeave($conn, $employeeId, $currentDate)) {
        // Update the status to 'Leave' if it's not already set
        $updateQuery = "UPDATE attendance_log SET status = 'Leave' WHERE employee_id = ? AND attendance_date = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ss", $employeeId, $currentDate);
        $updateStmt->execute();

        echo json_encode(["success" => true, "message" => "Employee is on leave.", "status" => "Leave"]);
        exit;
    }

    // Check existing attendance for today
    $query = "SELECT * FROM attendance_log WHERE employee_id = ? AND attendance_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $employeeId, $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $attendance = $result->fetch_assoc();

        // If time_in is null, treat this as a time-in event
        if ($attendance['time_in'] === null) {
            $updateQuery = "UPDATE attendance_log SET time_in = ?, status = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
        
            // Corrected line with proper grouping
            $isLate = (new DateTime($currentTime)) > (new DateTime($currentDate . ' 08:00:00'));
            $status = $isHoliday ? 'Holiday Duty' : ($isLate ? 'Late' : 'Present');
            
            $updateStmt->bind_param("ssi", $currentTime, $status, $attendance['id']);
            $updateStmt->execute();
            echo json_encode(["success" => true, "message" => "Time-in recorded.", "status" => $status]);
        }
        // If time_out is null, treat this as a time-out event
        elseif ($attendance['time_out'] === null) {
            $updateQuery = "UPDATE attendance_log SET time_out = ?, status = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);

            // Define operation hours and break time
            $operationStart = new DateTime($currentDate . ' 08:00:00');
            $operationEnd = new DateTime($currentDate . ' 17:00:00');
            $breakStart = new DateTime($currentDate . ' 12:00:00');
            $breakEnd = new DateTime($currentDate . ' 13:00:00');

            // Adjust time_in and time_out based on operation hours
            $timeIn = new DateTime($attendance['time_in']);
            $timeOut = new DateTime($currentTime);

            // If time_in is before operation start, set it to operation start
            if ($timeIn < $operationStart) {
                $timeIn = $operationStart;
            }

            // If time_out is after operation end, set it to operation end
            if ($timeOut > $operationEnd) {
                $timeOut = $operationEnd;
            }

            // Calculate total worked time (including minutes)
            $workedTime = $timeOut->diff($timeIn);
            $workedHours = $workedTime->h; // Total worked hours
            $workedMinutes = $workedTime->i; // Total worked minutes

            // Check if the working period overlaps with break time
            $isBreakTimeOverlap = ($timeIn < $breakEnd && $timeOut > $breakStart);

            // Subtract 1 hour only if there is an overlap with break time
            if ($isBreakTimeOverlap) {
                $workedHours -= 1; // Subtract 1 hour for break time
            }

            // Convert total worked time to minutes for easier comparison
            $totalWorkedMinutes = ($workedHours * 60) + $workedMinutes;

            // Determine status
            if ($isHoliday) {
                $status = 'Holiday Duty'; // Always set to 'Holiday Duty' if it's a holiday
            } elseif ($totalWorkedMinutes < 120) { // Less than 2 hours (120 minutes)
                $status = 'Absent';
            } elseif ($totalWorkedMinutes < 360) { // 2 to 6 hours (120 to 360 minutes)
                $status = 'Half-Day';
            } elseif ($timeOut > $operationEnd) {
                $status = 'Overtime'; // Time-out after 5:00 PM
            } elseif ($timeOut < $operationEnd) {
                $status = 'Undertime'; // Time-out before 5:00 PM but worked 6 hours or more
            } else {
                $status = 'Present'; // Worked 6 hours or more
            }

            $updateStmt->bind_param("ssi", $currentTime, $status, $attendance['id']);
            $updateStmt->execute();
            echo json_encode(["success" => true, "message" => "Time-out recorded.", "status" => $status]);
        } else {
            echo json_encode(["success" => false, "message" => "Attendance already logged."]);
        }
    } else {
        // If no record exists (unlikely due to default records), insert a new time-in record
        $insertQuery = "INSERT INTO attendance_log (employee_id, time_in, attendance_date, status) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);

        // If it's a holiday, set status to 'Holiday Duty'
        $isLate = (new DateTime($currentTime)) > (new DateTime($currentDate . ' 08:00:00'));
        $status = $isHoliday ? 'Holiday Duty' : ($isLate ? 'Late' : 'Present');
        
        $insertStmt->bind_param("ssss", $employeeId, $currentTime, $currentDate, $status);

        if ($insertStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Time-in recorded.", "status" => $status]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to log time-in."]);
        }
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "No employee ID."]);
}

$conn->close();
?>