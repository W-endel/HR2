<?php
// Start the session
session_start();

// Include the database connection file
include '../../db/db_conn.php';

// Check if the user is logged in
if (!isset($_SESSION['e_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: ../../login.php");
    exit();
}

// Get the logged-in user's ID from the session
$loggedInUserId = $_SESSION['e_id'];

// Initialize an array to store attendance logs
$attendanceLogs = [];

// Get the selected month and year from the request (default to current month and year)
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

$employeeName = 'N/A'; // Default value
$nameQuery = "SELECT firstname, lastname FROM employee_register WHERE e_id = ?";
if ($nameStmt = $conn->prepare($nameQuery)) {
    $nameStmt->bind_param("i", $loggedInUserId);
    $nameStmt->execute();
    $nameResult = $nameStmt->get_result();
    if ($nameRow = $nameResult->fetch_assoc()) {
        // Concatenate first and last name
        $employeeName = $nameRow['firstname'] . ' ' . $nameRow['lastname'];
    }
    $nameStmt->close();
} else {
    die("Error preparing name statement: " . $conn->error);
}


// Fetch data from the attendance_log table for the logged-in user and selected month/year
$sql = "SELECT e_id, name, attendance_date, time_in, time_out, status 
        FROM attendance_log 
        WHERE e_id = ? 
        AND MONTH(attendance_date) = ? 
        AND YEAR(attendance_date) = ?";

// Prepare and execute the query
if ($stmt = $conn->prepare($sql)) {
    // Bind the logged-in user's ID, selected month, and selected year to the query
    $stmt->bind_param("iii", $loggedInUserId, $selectedMonth, $selectedYear);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all rows as an associative array
    while ($row = $result->fetch_assoc()) {
        // Calculate total_hours based on time_in and time_out
        if (!empty($row['time_in']) && !empty($row['time_out'])) {
            $timeIn = new DateTime($row['time_in']);
            $timeOut = new DateTime($row['time_out']);
            $interval = $timeIn->diff($timeOut);

            // Calculate total hours and minutes
            $totalHours = ($interval->days * 24) + $interval->h; // Include days if any
            $minutes = $interval->i;

            // Format the duration display
            $durationParts = [];
            if ($totalHours > 0) {
                $durationParts[] = $totalHours . ' hour' . ($totalHours != 1 ? 's' : '');
            }
            if ($minutes > 0) {
                $durationParts[] = $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            }

            // Handle cases where both are zero
            if (empty($durationParts)) {
                $row['total_hours'] = '0 hours';
            } else {
                $row['total_hours'] = implode(' and ', $durationParts);
            }
        } else {
            // If time_in or time_out is null, set total_hours to 'N/A'
            $row['total_hours'] = 'N/A';
        }

        // Append the row to the attendanceLogs array
        $attendanceLogs[] = $row;
    }

    // Close the statement
    $stmt->close();
} else {
    // Handle query preparation error
    die("Error preparing statement: " . $conn->error);
}

// Fetch holidays from non_working_days table
$holidays = [];
$holidayQuery = "SELECT date, description FROM non_working_days 
                 WHERE MONTH(date) = ? AND YEAR(date) = ?";
if ($holidayStmt = $conn->prepare($holidayQuery)) {
    $holidayStmt->bind_param("ii", $selectedMonth, $selectedYear);
    $holidayStmt->execute();
    $holidayResult = $holidayStmt->get_result();
    while ($holidayRow = $holidayResult->fetch_assoc()) {
        $holidays[$holidayRow['date']] = $holidayRow['description'];
    }
    $holidayStmt->close();
} else {
    die("Error preparing holiday statement: " . $conn->error);
}

// Fetch leave requests from leave_requests table
$leaveRequests = [];
$leaveQuery = "SELECT start_date, end_date, leave_type FROM leave_requests 
               WHERE e_id = ? 
               AND status = 'Approved'  -- Filter by approved status
               AND ((MONTH(start_date) = ? AND YEAR(start_date) = ?) 
               OR (MONTH(end_date) = ? AND YEAR(end_date) = ?))";

if ($leaveStmt = $conn->prepare($leaveQuery)) {
    $leaveStmt->bind_param("iiiii", $loggedInUserId, $selectedMonth, $selectedYear, $selectedMonth, $selectedYear);
    $leaveStmt->execute();
    $leaveResult = $leaveStmt->get_result();
    
    while ($leaveRow = $leaveResult->fetch_assoc()) {
        $startDate = new DateTime($leaveRow['start_date']);
        $endDate = new DateTime($leaveRow['end_date']);
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        foreach ($dateRange as $date) {
            $leaveRequests[$date->format('Y-m-d')] = $leaveRow['leave_type'];
        }
    }
    $leaveStmt->close();
} else {
    die("Error preparing leave statement: " . $conn->error);
}

// Close the database connection
$conn->close();

// Generate all dates for the selected month and year
$allDatesInMonth = [];
$numberOfDays = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
$currentDate = new DateTime(); // Get current date/time in Manila timezone

for ($day = 1; $day <= $numberOfDays; $day++) {
    $dateStr = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $day);
    $dateObj = new DateTime($dateStr);
    $dayOfWeek = $dateObj->format('N'); // 1=Monday, 7=Sunday
    
    // Reset time components for accurate comparison
    $dateObj->setTime(0, 0, 0);
    $currentDate->setTime(0, 0, 0);

    // Determine status
    if ($dayOfWeek == 7) {
        $status = 'Day Off';
    } elseif (isset($holidays[$dateStr])) {
        $status = 'Holiday (' . $holidays[$dateStr] . ')';
    } elseif (isset($leaveRequests[$dateStr])) {
        $status = 'Leave (' . $leaveRequests[$dateStr] . ')';
    } else {
        $status = ($dateObj <= $currentDate) ? 'Absent' : 'No Record';
    }

    $allDatesInMonth[$dateStr] = [
        'e_id' => $loggedInUserId,
        'name' => $employeeName, // Use the fetched employee name
        'attendance_date' => $dateStr,
        'time_in' => null,
        'time_out' => null,
        'status' => $status,
        'total_hours' => 'N/A'
    ];
}


// Merge attendance logs with all dates
foreach ($attendanceLogs as $log) {
    $date = $log['attendance_date'];
    if (isset($allDatesInMonth[$date])) {
        $allDatesInMonth[$date] = $log; // Replace with actual attendance data
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timesheet</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge {
            font-size: 14px;
            padding: 5px 10px;
        }
        .month-selector {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Timesheet</h2>

        <!-- Month and Year Selector -->
        <form method="GET" class="month-selector">
            <div class="row">
                <div class="col-md-3">
                    <label for="month">Select Month:</label>
                    <select name="month" id="month" class="form-control">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($i == $selectedMonth) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $i, 10)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year">Select Year:</label>
                    <input type="number" name="year" id="year" class="form-control" value="<?php echo $selectedYear; ?>" min="2000" max="<?php echo date('Y'); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary mt-4" aria-label="Filter timesheet">Filter</button>
                </div>
            </div>
        </form>

        <!-- Attendance Log Table -->
        <table id="timesheet" class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Time-In</th>
                    <th>Time-Out</th>
                    <th>Total Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($allDatesInMonth)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No data available for the selected month and year.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($allDatesInMonth as $date => $log): ?>
                        <tr>
                            <td><?php echo date('F j, Y', strtotime($date)); ?></td>
                            <td><?php echo $log['e_id']; ?></td>
                            <td><?php echo $log['name']; ?></td>
                            <td><?php echo $log['time_in'] ? date('g:i a', strtotime($log['time_in'])) : 'N/A'; ?></td>
                            <td><?php echo $log['time_out'] ? date('g:i a', strtotime($log['time_out'])) : 'N/A'; ?></td>
                            <td><?php echo $log['total_hours']; ?></td>
                            <td>
                                <?php
                                // Set badge color based on status
                                $statusClass = '';
                                switch ($log['status']) {
                                    case 'Present':
                                        $statusClass = 'bg-success';
                                        break;
                                    case 'Late':
                                        $statusClass = 'bg-warning text-dark';
                                        break;
                                    case 'Overtime':
                                        $statusClass = 'bg-primary';
                                        break;
                                    case 'Holiday':
                                        $statusClass = 'bg-danger';
                                        break;
                                    case 'Leave':
                                        $statusClass = 'bg-danger';
                                        break;
                                    case 'Day Off':
                                        $statusClass = 'bg-secondary';
                                        break;
                                    case 'Absent':
                                        $statusClass = 'bg-danger';
                                        break;
                                    case 'No Record':
                                        $statusClass = 'bg-light text-dark';
                                        break;
                                    default:
                                        $statusClass = 'bg-light text-dark';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $log['status']; ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>