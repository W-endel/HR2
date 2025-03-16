<?php
session_start();

if (!isset($_SESSION['a_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../db/db_conn.php';

// Fetch admin's ID from the session
$adminId = $_SESSION['a_id']; 

// Fetch admin info
$sql = "SELECT a_id, firstname, middlename, lastname, birthdate, email, role, department, phone_number, address, pfp FROM admin_register WHERE a_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$adminInfo = $result->fetch_assoc();

// Fetch all leave requests that have been approved or denied by the supervisor
$sql = "SELECT lr.leave_id, e.employee_id, e.first_name, e.last_name, e.department, lr.start_date, lr.end_date, lr.leave_type, lr.leave_category, lr.proof, lr.status, lr.created_at
        FROM leave_requests lr
        JOIN employee_register e ON lr.employee_id = e.employee_id
        WHERE lr.supervisor_approval = 'Supervisor Approved' AND lr.status = 'Supervisor Approved' ORDER BY created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Fetch holidays from the database
$holidays = [];
$holiday_sql = "SELECT date FROM non_working_days";
$holiday_stmt = $conn->prepare($holiday_sql);
$holiday_stmt->execute();
$holiday_result = $holiday_stmt->get_result();
while ($holiday_row = $holiday_result->fetch_assoc()) {
    $holidays[] = $holiday_row['date']; 
}

// Handle approve/deny actions by the admin
if (isset($_GET['leave_id']) && isset($_GET['status'])) {
    $leave_id = $_GET['leave_id'];
    $status = $_GET['status'];

    // Validate the status input
    if (!in_array($status, ['approve', 'deny'])) {
        header("Location: ../admin/leave_requests.php?status=invalid_status");
        exit();
    }

// Fetch the specific leave request
$sql = "SELECT e.department, e.employee_id, e.first_name, e.last_name, lr.start_date, lr.end_date, lr.leave_type, lr.proof, lr.status, e.gender,
               el.bereavement_leave, el.emergency_leave, el.maternity_leave, el.mcw_special_leave, el.parental_leave,
               el.service_incentive_leave, el.sick_leave, el.vacation_leave, el.vawc_leave, el.bereavement_leave_male,
               el.emergency_leave_male, el.parental_leave_male, el.paternity_leave_male, el.service_incentive_leave_male,
               el.sick_leave_male, el.vacation_leave_male
        FROM leave_requests lr
        JOIN employee_register e ON lr.employee_id = e.employee_id
        JOIN employee_leaves el ON el.employee_id = e.employee_id
        WHERE lr.leave_id = ?";
$action_stmt = $conn->prepare($sql);
$action_stmt->bind_param("i", $leave_id);
$action_stmt->execute();
$action_result = $action_stmt->get_result();

if ($action_result->num_rows > 0) {
    $row = $action_result->fetch_assoc();
    
    // Determine which leave type column to use based on gender and leave_type
    $leave_type = $row['leave_type'];
    $gender = $row['gender'];
    
    switch ($leave_type) {
        case 'Bereavement Leave':
            $available_balance = ($gender === 'Male') ? $row['bereavement_leave_male'] : $row['bereavement_leave'];
            break;
        case 'Emergency Leave':
            $available_balance = ($gender === 'Male') ? $row['emergency_leave_male'] : $row['emergency_leave'];
            break;
        case 'Maternity Leave':
            $available_balance = $row['maternity_leave'];
            break;
        case 'MCW Special Leave':
            $available_balance = $row['mcw_special_leave'];
            break;
        case 'Parental Leave':
            $available_balance = ($gender === 'Male') ? $row['parental_leave_male'] : $row['parental_leave'];
            break;
        case 'Paternity Leave':
            $available_balance = $row['paternity_leave_male']; // For male employees only
            break;
        case 'Service Incentive Leave':
            $available_balance = ($gender === 'Male') ? $row['service_incentive_leave_male'] : $row['service_incentive_leave'];
            break;
        case 'Sick Leave':
            $available_balance = ($gender === 'Male') ? $row['sick_leave_male'] : $row['sick_leave'];
            break;
        case 'Vacation Leave':
            $available_balance = ($gender === 'Male') ? $row['vacation_leave_male'] : $row['vacation_leave'];
            break;
        case 'VAWC Leave':
            $available_balance = $row['vawc_leave'];
            break;
        default:
            $available_balance = 0; // Default to 0 if leave_type is not recognized
            break;
    }

    $start_date = $row['start_date'];
    $end_date = $row['end_date'];

    // Calculate total leave days excluding Sundays and holidays
    $leave_days = 0;
    $current_date = strtotime($start_date);

    while ($current_date <= strtotime($end_date)) {
        $current_date_str = date('Y-m-d', $current_date);
        if (date('N', $current_date) != 7 && !in_array($current_date_str, $holidays)) {
            $leave_days++;
        }
        $current_date = strtotime("+1 day", $current_date);
    }

    if ($status === 'approve') {
        if ($leave_days > $available_balance) {
            header("Location: ../admin/leave_requests.php?status=insufficient_balance");
            exit();
        } else {
            $new_balance = $available_balance - $leave_days;

            // Prepare the update query based on the leave type
            switch ($leave_type) {
                case 'Bereavement Leave':
                    $balance_field = ($gender === 'Male') ? 'bereavement_leave_male' : 'bereavement_leave';
                    break;
                case 'Emergency Leave':
                    $balance_field = ($gender === 'Male') ? 'emergency_leave_male' : 'emergency_leave';
                    break;
                case 'Maternity Leave':
                    $balance_field = 'maternity_leave';
                    break;
                case 'MCW Special Leave':
                    $balance_field = 'mcw_special_leave';
                    break;
                case 'Parental Leave':
                    $balance_field = ($gender === 'Male') ? 'parental_leave_male' : 'parental_leave';
                    break;
                case 'Paternity Leave':
                    $balance_field = 'paternity_leave_male'; // Only for male employees
                    break;
                case 'Service Incentive Leave':
                    $balance_field = ($gender === 'Male') ? 'service_incentive_leave_male' : 'service_incentive_leave';
                    break;
                case 'Sick Leave':
                    $balance_field = ($gender === 'Male') ? 'sick_leave_male' : 'sick_leave';
                    break;
                case 'Vacation Leave':
                    $balance_field = ($gender === 'Male') ? 'vacation_leave_male' : 'vacation_leave';
                    break;
                case 'VAWC Leave':
                    $balance_field = 'vawc_leave';
                    break;
                default:
                    $balance_field = ''; // Default to an empty field
                    break;
            }

            $update_sql = "UPDATE leave_requests lr 
                           JOIN employee_register e ON lr.employee_id = e.employee_id
                           JOIN employee_leaves el ON el.employee_id = e.employee_id
                           SET lr.status = 'Approved', lr.admin_approval = 'Admin Approved', lr.admin_id = ?, el.{$balance_field} = ? 
                           WHERE lr.leave_id = ?";

            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iii", $adminId, $new_balance, $leave_id);

            if ($update_stmt->execute()) {
                // Log the activity for approval
                $action_type = "Leave Request Approved";
                $affected_feature = "Leave Information";
                $details = "Leave request from {$row['first_name']} {$row['last_name']} ({$row['employee_id']}) has been approved | Leave day(s): $leave_days.";
                log_activity($adminId, $action_type, $affected_feature, $details);
            
                // Notify the employee
                $employeeId = $row['employee_id']; // Get the employee's ID
                $message = "Your leave request has been approved. Leave days: $leave_days.";
                $notificationSql = "INSERT INTO notifications (employee_id, message) VALUES (?, ?)";
                $notificationStmt = $conn->prepare($notificationSql);
                $notificationStmt->bind_param("ss", $employeeId, $message);
                $notificationStmt->execute();
            
                header("Location: ../admin/leave_requests.php?status=approved");
            } else {
                error_log("Error updating leave balance: " . $conn->error);
                header("Location: ../admin/leave_requests.php?status=error");
            }
        }
    } elseif ($status === 'deny') {
        // Check if admin_comments is provided and not empty
        if (isset($_GET['admin_comments']) && !empty($_GET['admin_comments'])) {
            $admin_comments = $_GET['admin_comments'];
    
            // Deny the leave request and add the admin's comment
            $deny_sql = "UPDATE leave_requests 
                         SET status = 'Denied', 
                             admin_approval = 'Admin Denied', 
                             admin_id = ?, 
                             admin_comments = ? 
                         WHERE leave_id = ?";
            $deny_stmt = $conn->prepare($deny_sql);
            $deny_stmt->bind_param("isi", $adminId, $admin_comments, $leave_id);
        } else {
            // Deny the leave request without comments
            $deny_sql = "UPDATE leave_requests 
                         SET status = 'Denied', 
                             admin_approval = 'Admin Denied', 
                             admin_id = ? 
                         WHERE leave_id = ?";
            $deny_stmt = $conn->prepare($deny_sql);
            $deny_stmt->bind_param("ii", $adminId, $leave_id);
        }
    
        // Execute the prepared statement
        if ($deny_stmt->execute()) {
            // Log the activity for admin denial
            $action_type = "Leave Request Denied";
            $affected_feature = "Leave Information";
            $details = "Leave request from {$row['first_name']} {$row['last_name']} ({$row['employee_id']}) has been denied by the admin.";
            log_activity($adminId, $action_type, $affected_feature, $details);
        
            // Notify the employee
            $employeeId = $row['employee_id']; // Get the employee's ID
            $message = "Your leave request has been denied.";
            if (!empty($admin_comments)) {
                $message .= " Reason: " . $admin_comments;
            }
            $notificationSql = "INSERT INTO notifications (employee_id, message) VALUES (?, ?)";
            $notificationStmt = $conn->prepare($notificationSql);
            $notificationStmt->bind_param("ss", $employeeId, $message);
            $notificationStmt->execute();
        
            // Redirect to admin's leave request page
            header("Location: ../admin/leave_requests.php?status=success");
        } else {
            header("Location: ../admin/leave_requests.php?status=error");
        }
    }
} else {
    header("Location: ../admin/leave_requests.php?status=not_found");
}
exit();

}

// Function to log activity
function log_activity($adminId, $action_type, $affected_feature, $details) {
    global $conn;

    // Get the admin's name
    $admin_query = "SELECT firstname, lastname FROM admin_register WHERE a_id = ?";
    $admin_stmt = $conn->prepare($admin_query);
    $admin_stmt->bind_param("i", $adminId);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();
    $admin = $admin_result->fetch_assoc();
    $admin_name = $admin['firstname'] . ' ' . $admin['lastname'];

    // Capture admin's IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Insert the log entry into activity_logs table
    $log_query = "INSERT INTO activity_logs (admin_id, admin_name, action_type, affected_feature, details, ip_address) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bind_param("isssss", $adminId, $admin_name, $action_type, $affected_feature, $details, $ip_address);
    $log_stmt->execute();
}




// Prepare and execute the query for leave statistics
$count_sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') AS month,
                COUNT(*) AS total_leaves,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS total_approved,
                SUM(CASE WHEN status = 'Denied' THEN 1 ELSE 0 END) AS total_denied,                                                     
                SUM(CASE WHEN status = 'Supervisor Approved' THEN 1 ELSE 0 END) AS total_pending
            FROM leave_requests
            WHERE supervisor_approval = 'Supervisor Approved'
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month";

$count_stmt = $conn->prepare($count_sql);
if (!$count_stmt) {
    die("Error in SQL query: " . $conn->error);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();

// Fetch the results into an array
$leave_stats = [];
while ($count_row = $count_result->fetch_assoc()) {
    $leave_stats[] = $count_row;
}

// Calculate the total pending, approved, denied, and total leave requests
$total_pending = 0;
$total_approved = 0;
$total_denied = 0;
$total_leave = 0;
foreach ($leave_stats as $stats) {
    $total_pending += $stats['total_pending'];
    $total_approved += $stats['total_approved'];
    $total_denied += $stats['total_denied'];
    $total_leave += $stats['total_leaves'];
}

// Get the current year and month
$current_year = date("Y"); // Current year (e.g., 2023)
$current_month = date("m"); // Current month (e.g., 03 for March)

// Calculate the first and last day of the current month
$first_day_of_month = date("Y-m-01"); // First day of the month (e.g., 2023-03-01)
$last_day_of_month = date("Y-m-t"); // Last day of the month (e.g., 2023-03-31)

// Prepare and execute the query
$ongoing_sql = "SELECT COUNT(*) AS ongoing_leaves
                FROM leave_requests
                WHERE (
                    (start_date <= ? AND end_date >= ?) -- Ongoing today
                    OR
                    (start_date > ? AND start_date <= ?) -- Starts after today but before the end of the month
                )
                AND status = 'Approved'";
$ongoing_stmt = $conn->prepare($ongoing_sql);
if (!$ongoing_stmt) {
    die("Error in SQL query: " . $conn->error);
}
$ongoing_stmt->bind_param("ssss", $last_day_of_month, $first_day_of_month, $first_day_of_month, $last_day_of_month);
$ongoing_stmt->execute();
$ongoing_result = $ongoing_stmt->get_result();
$ongoing_row = $ongoing_result->fetch_assoc();
$ongoing_leave = $ongoing_row['ongoing_leaves']; // Total ongoing leaves

echo "Ongoing Leaves (from $first_day_of_month to $last_day_of_month): $ongoing_leave";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="HR2 Leave Request Management System" />
    <meta name="author" content="HR2 Team" />
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>Leave Requests | HR2</title>
    <link href="../css/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../css/calendar.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --secondary-color: #10b981;
            --bg-dark: rgba(33, 37, 41) !important;
            --bg-black: rgba(16, 17, 18) !important;
            --card-bg: rgba(33, 37, 41) !important;
            --border-color: #333333;
            --text-primary: #ffffff;
            --text-secondary: #e0e0e0;
            --text-muted: #a0a0a0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        body {
            background-color: var(--bg-black);
            color: var(--text-primary);
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
        }

        .card-body {
            background-color: var(--card-bg);
            padding: 1.5rem;
        }

        /* Tables */
        .table {
            color: var(--text-primary);
            border-color: var(--border-color);
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
            background-color: #1a1a1a;
            padding: 0.75rem 1rem;
            white-space: nowrap;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: var(--border-color);
        }

        .table tbody tr {
            transition: background-color 0.15s ease-in-out;
        }

        .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        /* Buttons */
        .btn {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }

        .btn-danger {
            background-color: var(--danger);
            border-color: var(--danger);
        }

        /* Alerts */
        .alert {
            border-radius: 0.5rem;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            animation: fadeIn 0.3s ease-in-out;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-left: 4px solid #ef4444;
        }

        .alert-warning {
            background-color: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
            border-left: 4px solid #f59e0b;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        

        /* Form controls */
        .form-control {
            background-color: var(--bg-black);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            background-color: var(--bg-black);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
        }

        /* Status badges */
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            border-radius: 0.375rem;
        }

        /* Leave type badges */
        .leave-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .leave-badge-status {
            background-color: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        /* Dashboard stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 0.75rem;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            margin-right: 1rem;
            font-size: 1.5rem;
        }

        .stat-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            color: var(--text-muted);
            margin-bottom: 0;
        }

        /* Employee info */
        .employee-info {
            display: flex;
            align-items: center;
        }

        .employee-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.75rem;
            font-size: 1rem;
        }

        .employee-details {
            display: flex;
            flex-direction: column;
        }

        .employee-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .employee-id {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        /* Date range display */
        .date-range {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }

        .date-range-separator {
            margin: 0 0.5rem;
            color: var(--text-muted);
        }

        /* Action buttons container */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .table-responsive {
                border-radius: 0.75rem;
                overflow: hidden;
            }
        }

        /* Proof viewer */
        .proof-viewer {
            max-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .proof-viewer img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
        }

        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.2em;
        }

        /* Tooltip styling */
        .tooltip-inner {
            background-color: var(--bg-black);
            border: 1px solid var(--border-color);
            padding: 0.5rem 0.75rem;
            max-width: 200px;
        }

        .bs-tooltip-auto[data-popper-placement^=top] .tooltip-arrow::before, 
        .bs-tooltip-top .tooltip-arrow::before {
            border-top-color: var(--border-color);
        }       
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include 'navbar.php'; ?>'
    <div id="layoutSidenav">
        <?php include 'sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="fw-bold mb-1">Leave Requests</h1>
                        </div>
                    </div>

                    <!-- Calendar Container -->
                    <div class="container-fluid" id="calendarContainer"
                        style="position: fixed; top: 7%; right: 40; z-index: 1050;
                        max-width: 100%; display: none;">
                        <div class="row">
                            <div class="col-md-9 mx-auto">
                                <div id="calendar" class="p-2 rounded shadow"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Overview -->
                    <div class="stats-container">
                        <div class="stat-card">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $total_pending; ?></h3>
                                <p>Pending</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $total_approved; ?></h3>
                                <p>Approved</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $total_denied; ?></h3>
                                <p>Denied</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="stat-info">
                            <h3><?php echo $ongoing_leave; ?></h3>
                            <p>Ongoing Leave</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Alerts -->
                    <div class="container px-0">
                        <?php if (isset($_GET['status'])): ?>
                            <div id="status-alert" class="alert
                                <?php if ($_GET['status'] === 'success' || $_GET['status'] === 'approved'): ?>
                                    alert-success
                                <?php elseif ($_GET['status'] === 'error'): ?>
                                    alert-danger
                                <?php elseif ($_GET['status'] === 'not_exist' || $_GET['status'] === 'insufficient_balance'): ?>
                                    alert-warning
                                <?php endif; ?>" role="alert">
                                <div class="d-flex align-items-center">
                                    <?php if ($_GET['status'] === 'success' || $_GET['status'] === 'approved'): ?>
                                        <i class="fas fa-check-circle me-2"></i>
                                        <span>Leave request status updated successfully.</span>
                                    <?php elseif ($_GET['status'] === 'error'): ?>
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <span>Error updating leave request status. Please try again.</span>
                                    <?php elseif ($_GET['status'] === 'not_exist'): ?>
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span>The leave request ID does not exist or could not be found.</span>
                                    <?php elseif ($_GET['status'] === 'insufficient_balance'): ?>
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span>Insufficient leave balance. The request cannot be approved.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pending Requests Table -->
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock me-2"></i>
                                <span>Leave Requests</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm" placeholder="Search..." id="requestSearch">
                                    <button class="btn btn-outline-light btn-sm" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="datatablesSimple" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Requested On</th>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Duration</th>
                                            <th>Days</th>
                                            <th>Type</th>
                                            <th>Category</th>
                                            <th>Proof</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result->num_rows > 0): ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <?php
                                                    // Calculate total leave days excluding Sundays and holidays
                                                    $leave_days = 0;
                                                    $current_date = strtotime($row['start_date']);
                                                    $end_date = strtotime($row['end_date']);

                                                    while ($current_date <= $end_date) {
                                                    $current_date_str = date('Y-m-d', $current_date);
                                                    // Check if the current day is not a Sunday (0 = Sunday) and not a holiday
                                                    if (date('N', $current_date) != 7 && !in_array($current_date_str, $holidays)) {
                                                            $leave_days++; // Count this day as a leave day
                                                    }
                                                    $current_date = strtotime("+1 day", $current_date); // Move to the next day
                                                    }

                                                    // Determine leave type badge class
                                                    $leave_badge_class = 'leave-badge-status';
                                                    switch ($row['leave_type']) {
                                                        case 'Vacation Leave':
                                                            $leave_badge_class = 'leave-badge-status';
                                                            break;
                                                        case 'Sick Leave':
                                                            $leave_badge_class = 'leave-badge-status';
                                                            break;
                                                        case 'Emergency Leave':
                                                            $leave_badge_class = 'leave-badge-status';
                                                            break;
                                                        case 'Bereavement Leave':
                                                            $leave_badge_class = 'leave-badge-status';
                                                            break;
                                                        case 'Maternity Leave':
                                                            $leave_badge_class = 'leave-badge-status';
                                                            break;
                                                        case 'Paternity Leave':
                                                            $leave_badge_class = 'leave-badge-status';
                                                            break;
                                                    }

                                                    // Get employee initials for avatar
                                                    $initials = strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1));
                                                ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span><?php echo htmlspecialchars(date("M j, Y", strtotime($row['created_at']))); ?></span>
                                                        <small class="text-muted"><?php echo htmlspecialchars(date("g:i A", strtotime($row['created_at']))); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="employee-info">
                                                        <div class="employee-avatar"><?php echo $initials; ?></div>
                                                        <div class="employee-details">
                                                            <span class="employee-name"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></span>
                                                            <span class="employee-id">#<?php echo htmlspecialchars($row['employee_id']); ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span><?php echo htmlspecialchars($row['department']); ?></span>
                                                        <small class="text-muted">
                                                    </div>                    
                                                </td>
                                                <td>
                                                    <div class="date-range">
                                                        <span><?php echo htmlspecialchars(date("M j, Y", strtotime($row['start_date']))); ?></span>
                                                        <span class="date-range-separator">→</span>
                                                        <span><?php echo htmlspecialchars(date("M j, Y", strtotime($row['end_date']))); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-light">
                                                        <?php echo htmlspecialchars($leave_days); ?> day<?php echo $leave_days > 1 ? 's' : ''; ?>
                                                </div>
                                                </td>
                                                <td>
                                                    <span class="leave-badge <?php echo $leave_badge_class; ?>">
                                                        <?php echo htmlspecialchars($row['leave_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span><?php echo htmlspecialchars($row['leave_category']); ?></span>
                                                        <small class="text-muted">
                                                    </div>                    
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['proof'])): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#proofModal<?php echo $row['leave_id']; ?>">
                                                            <i class="fas fa-file-alt me-1"></i> View
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">No proof</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="btn btn-success btn-sm" onclick="confirmAction('approve', <?php echo $row['leave_id']; ?>)" data-bs-toggle="tooltip" title="Approve Request">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" onclick="confirmAction('deny', <?php echo $row['leave_id']; ?>)" data-bs-toggle="tooltip" title="Deny Request">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Proof Modal -->
                                            <div class="modal fade" id="proofModal<?php echo $row['leave_id']; ?>" tabindex="-1" aria-labelledby="proofModalLabel<?php echo $row['leave_id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content bg-dark">
                                                        <div class="modal-header border-bottom border-secondary">
                                                            <h5 class="modal-title" id="proofModalLabel<?php echo $row['leave_id']; ?>">
                                                                <i class="fas fa-file-alt me-2"></i>Proof of Leave
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div id="proofCarousel<?php echo $row['leave_id']; ?>" class="carousel slide" data-bs-ride="false">
                                                                <div class="carousel-inner">
                                                                    <?php
                                                                        // Assuming proof field contains a comma-separated list of file names
                                                                        $filePaths = explode(',', $row['proof']);
                                                                        $isActive = true;  // To set the first item as active
                                                                        $fileCount = count($filePaths);  // Count the number of files
                                                                        $baseURL = 'http://localhost/HR2/proof/';  // Define the base URL for file access

                                                                        foreach ($filePaths as $filePath) {
                                                                            $filePath = trim($filePath);  // Clean the file path
                                                                            $fullFilePath = $baseURL . $filePath;  // Construct the full URL for the file
                                                                            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

                                                                            // Check if the file is an image (e.g., jpg, jpeg, png, gif)
                                                                            $imageTypes = ['jpg', 'jpeg', 'png', 'gif'];
                                                                            if (in_array(strtolower($fileExtension), $imageTypes)) {
                                                                                echo '<div class="carousel-item ' . ($isActive ? 'active' : '') . '">';
                                                                                echo '<div class="proof-viewer">';
                                                                                echo '<img src="' . htmlspecialchars($fullFilePath) . '" alt="Proof of Leave" class="img-fluid">';
                                                                                echo '</div>';
                                                                                echo '<div class="text-center mt-3">';
                                                                                echo '<a href="' . htmlspecialchars($fullFilePath) . '" class="btn btn-sm btn-primary" target="_blank">Open in New Tab</a>';
                                                                                echo '</div>';
                                                                                echo '</div>';
                                                                                $isActive = false;
                                                                            }
                                                                            // Check if the file is a PDF
                                                                            elseif (strtolower($fileExtension) === 'pdf') {
                                                                                echo '<div class="carousel-item ' . ($isActive ? 'active' : '') . '">';
                                                                                echo '<div class="proof-viewer">';
                                                                                echo '<embed src="' . htmlspecialchars($fullFilePath) . '" type="application/pdf" width="100%" height="400px" />';
                                                                                echo '</div>';
                                                                                echo '<div class="text-center mt-3">';
                                                                                echo '<a href="' . htmlspecialchars($fullFilePath) . '" class="btn btn-sm btn-primary" target="_blank">Open in New Tab</a>';
                                                                                echo '</div>';
                                                                                echo '</div>';
                                                                                $isActive = false;
                                                                            }
                                                                            // Handle other document types
                                                                            else {
                                                                                echo '<div class="carousel-item ' . ($isActive ? 'active' : '') . '">';
                                                                                echo '<div class="d-flex flex-column align-items-center justify-content-center" style="height: 300px;">';
                                                                                echo '<i class="fas fa-file-alt fa-5x mb-4 text-muted"></i>';
                                                                                echo '<h5>' . htmlspecialchars($filePath) . '</h5>';
                                                                                echo '<a href="' . htmlspecialchars($fullFilePath) . '" target="_blank" class="btn btn-primary mt-3">Download Document</a>';
                                                                                echo '</div>';
                                                                                echo '</div>';
                                                                                $isActive = false;
                                                                            }
                                                                        }
                                                                    ?>
                                                                </div>
                                                                <?php if ($fileCount > 1): ?>
                                                                    <button class="carousel-control-prev" type="button" data-bs-target="#proofCarousel<?php echo $row['leave_id']; ?>" data-bs-slide="prev">
                                                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                                        <span class="visually-hidden">Previous</span>
                                                                    </button>
                                                                    <button class="carousel-control-next" type="button" data-bs-target="#proofCarousel<?php echo $row['leave_id']; ?>" data-bs-slide="next">
                                                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                                        <span class="visually-hidden">Next</span>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top border-secondary">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                                        <h5>No pending leave request</h5>
                                                        <p class="text-muted">All leave requests have been processed</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Deny Reason Modal -->
            <div class="modal fade" id="denyReasonModal" tabindex="-1" aria-labelledby="denyReasonModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-bottom border-secondary">
                            <h5 class="modal-title" id="denyReasonModalLabel">
                                <i class="fas fa-comment-alt me-2"></i>Reason for Denial
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-light mb-3">Please provide a reason for denying this leave request. This will be shared with the employee.</p>
                            <textarea id="denyReason" class="form-control" placeholder="Enter reason for denial..." rows="4"></textarea>
                        </div>
                        <div class="modal-footer border-top border-secondary">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="submitDeny()">
                                Continue <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approve Confirmation Modal -->
            <div class="modal fade" id="approveConfirmationModal" tabindex="-1" aria-labelledby="approveConfirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-bottom border-secondary">
                            <h5 class="modal-title" id="approveConfirmationModalLabel">
                                <i class="fas fa-check-circle text-success me-2"></i>Confirm Approval
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to approve this leave request?</p>
                        </div>
                        <div class="modal-footer border-top border-secondary">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" onclick="proceedWithApproval()">
                                <i class="fas fa-check me-1"></i>Approve
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deny Confirmation Modal -->
            <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-bottom border-secondary">
                            <h5 class="modal-title" id="confirmationModalLabel">
                                <i class="fas fa-times-circle text-danger me-2"></i>Confirm Denial
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to deny this leave request?</p>
                            <p class="text-muted">The employee will be notified with the reason you provided.</p>
                        </div>
                        <div class="modal-footer border-top border-secondary">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" onclick="proceedWithDenial()">
                                <i class="fas fa-times me-1"></i>Deny
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logout Modal -->
            <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-bottom border-secondary">
                            <h5 class="modal-title" id="logoutModalLabel">
                                Confirm Logout
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to log out?</p>
                        </div>
                        <div class="modal-footer border-top border-secondary">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form action="../admin/logout.php" method="POST">
                                <button type="submit" class="btn btn-danger">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="../js/admin.js"></script>
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Calendar toggle functionality
            const calendarToggle = document.getElementById('calendarToggle');
            const calendarContainer = document.getElementById('calendarContainer');
            
            if (calendarToggle && calendarContainer) {
                calendarToggle.addEventListener('click', function() {
                    if (calendarContainer.style.display === 'none') {
                        calendarContainer.style.display = 'block';
                        initCalendar();
                    } else {
                        calendarContainer.style.display = 'none';
                    }
                });
            }

            // Initialize FullCalendar
            function initCalendar() {
                const calendarEl = document.getElementById('calendar');
                if (calendarEl) {
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        events: [
                            // You can populate this with actual leave request data
                        ]
                    });
                    calendar.render();
                }
            }

            // Search functionality
            const searchInput = document.getElementById('requestSearch');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const tableRows = document.querySelectorAll('#datatablesSimple tbody tr');
                    
                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Automatically hide the alert after 5 seconds
            setTimeout(function() {
                const alertElement = document.getElementById('status-alert');
                if (alertElement) {
                    alertElement.style.transition = "opacity 1s ease";
                    alertElement.style.opacity = 0;

                    setTimeout(function() {
                        alertElement.remove();
                    }, 1000);
                }
            }, 5000);
        });

        // Variables to store current leave ID and deny reason
        let currentLeaveId = null;
        let denyReason = '';

        // Function to handle approve/deny actions
        function confirmAction(action, leaveId) {
            currentLeaveId = leaveId;
            
            if (action === 'approve') {
                const approveModal = new bootstrap.Modal(document.getElementById('approveConfirmationModal'));
                approveModal.show();
            } else if (action === 'deny') {
                const denyReasonModal = new bootstrap.Modal(document.getElementById('denyReasonModal'));
                denyReasonModal.show();
            }
        }

        // Function to proceed with approval
        function proceedWithApproval() {
            const approveBtn = document.querySelector('#approveConfirmationModal .btn-success');
            approveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...';
            approveBtn.disabled = true;
            
            // Send the approval request to the server
            fetch(`leave_requests.php?leave_id=${currentLeaveId}&status=approve`, {
                method: 'GET',
            })
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    throw new Error('Failed to approve leave request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                approveBtn.innerHTML = '<i class="fas fa-check me-1"></i>Approve';
                approveBtn.disabled = false;
                alert('An error occurred while processing your request.');
            });
        }

        // Function to submit denial reason
        function submitDeny() {
            denyReason = document.getElementById('denyReason').value;

            if (!denyReason.trim()) {
                // Highlight the textarea with an error style
                const textarea = document.getElementById('denyReason');
                textarea.classList.add('is-invalid');
                
                // Add error message if it doesn't exist
                if (!document.getElementById('denyReasonError')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'denyReasonError';
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Please enter a reason for denial';
                    textarea.parentNode.appendChild(errorDiv);
                }
                
                return;
            }

            // Remove any error styling
            document.getElementById('denyReason').classList.remove('is-invalid');
            
            // Hide the reason modal
            const denyReasonModal = bootstrap.Modal.getInstance(document.getElementById('denyReasonModal'));
            denyReasonModal.hide();

            // Show the confirmation modal
            const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            confirmationModal.show();
        }

        // Function to proceed with denial
        function proceedWithDenial() {
            const denyBtn = document.querySelector('#confirmationModal .btn-danger');
            denyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...';
            denyBtn.disabled = true;
            
            // Send the data to the server
            fetch(`leave_requests.php?leave_id=${currentLeaveId}&status=deny&admin_comments=${encodeURIComponent(denyReason)}`, {
                method: 'GET',
            })
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    throw new Error('Failed to deny leave request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                denyBtn.innerHTML = '<i class="fas fa-times me-1"></i>Deny';
                denyBtn.disabled = false;
                alert('An error occurred while processing your request.');
            });
        }
    </script>
</body>
</html>
