<?php
date_default_timezone_set('Asia/Manila');
include '../db/db_conn.php';

// Function to check if the current date is a holiday
function isHoliday($conn, $currentDate) {
    $query = "SELECT * FROM non_working_days WHERE date = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        error_log("Error preparing holiday check query: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    return $result->num_rows > 0; // Returns true if the date is a holiday
}

// Function to check if the employee is on approved leave
function isOnLeave($conn, $employeeId, $currentDate) {
    $query = "SELECT * FROM leave_requests WHERE employee_id = ? AND ? BETWEEN start_date AND end_date AND status = 'Approved'";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        error_log("Error preparing leave check query for employee ID $employeeId: " . $conn->error);
        return false;
    }

    $stmt->bind_param("ss", $employeeId, $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    return $result->num_rows > 0; // Returns true if the employee is on approved leave
}

// Function to insert default records for all employees
function insertDefaultRecords($conn, $currentDate) {
    // Fetch all employees from employee_register
    $query = "SELECT employee_id FROM employee_register";
    $result = $conn->query($query);

    if ($result === false) {
        error_log("Error fetching employees: " . $conn->error);
        return;
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $employeeId = $row['employee_id'];

            // Check if the employee already has an attendance record for today
            $checkQuery = "SELECT * FROM attendance_log WHERE employee_id = ? AND attendance_date = ?";
            $checkStmt = $conn->prepare($checkQuery);

            if ($checkStmt === false) {
                error_log("Error preparing check query for employee ID $employeeId: " . $conn->error);
                continue;
            }

            $checkStmt->bind_param("ss", $employeeId, $currentDate);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            // If no record exists, insert a default record
            if ($checkResult->num_rows == 0) {
                // Check if the current date is a holiday
                if (isHoliday($conn, $currentDate)) {
                    $status = 'Holiday'; // Set status to 'Holiday'
                } 
                // Check if the employee is on approved leave
                elseif (isOnLeave($conn, $employeeId, $currentDate)) {
                    $status = 'On Leave'; // Set status to 'On Leave'
                } 
                // Otherwise, set status to 'Absent'
                else {
                    $status = 'Absent';
                }

                // Insert the record with the appropriate status
                $insertQuery = "INSERT INTO attendance_log (employee_id, attendance_date, status) VALUES (?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);

                if ($insertStmt === false) {
                    error_log("Error preparing insert query for employee ID $employeeId: " . $conn->error);
                    continue;
                }

                $insertStmt->bind_param("sss", $employeeId, $currentDate, $status);

                if (!$insertStmt->execute()) {
                    error_log("Failed to insert default record for employee ID: $employeeId");
                }

                $insertStmt->close();
            }

            $checkStmt->close();
        }
    } else {
        error_log("No employees found in employee_register.");
    }
}

// Check database connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed: " . $conn->connect_error);
}

// Current date
$currentDate = date('Y-m-d');

// Insert default records for all employees
insertDefaultRecords($conn, $currentDate);

// Close the database connection
$conn->close();

echo "Default records inserted successfully.";
?>