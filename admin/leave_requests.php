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
$sql = "SELECT lr.leave_id, e.e_id, e.firstname, e.lastname, e.department, lr.start_date, lr.end_date, lr.leave_type, lr.proof, lr.status, lr.created_at
        FROM leave_requests lr
        JOIN employee_register e ON lr.e_id = e.e_id
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
$sql = "SELECT e.department, e.e_id, e.firstname, e.lastname, lr.start_date, lr.end_date, lr.leave_type, lr.proof, lr.status, e.gender,
               el.bereavement_leave, el.emergency_leave, el.maternity_leave, el.mcw_special_leave, el.parental_leave,
               el.service_incentive_leave, el.sick_leave, el.vacation_leave, el.vawc_leave, el.bereavement_leave_male,
               el.emergency_leave_male, el.parental_leave_male, el.paternity_leave_male, el.service_incentive_leave_male,
               el.sick_leave_male, el.vacation_leave_male
        FROM leave_requests lr
        JOIN employee_register e ON lr.e_id = e.e_id
        JOIN employee_leaves el ON el.employee_id = e.e_id
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
                           JOIN employee_register e ON lr.e_id = e.e_id
                           JOIN employee_leaves el ON el.employee_id = e.e_id
                           SET lr.status = 'Approved', lr.admin_approval = 'Admin Approved', lr.admin_id = ?, el.{$balance_field} = ? 
                           WHERE lr.leave_id = ?";

            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iii", $adminId, $new_balance, $leave_id);

            if ($update_stmt->execute()) {
                // Log the activity for approval
                $action_type = "Leave Request Approved";
                $affected_feature = "Leave Information";
                $details = "Leave request from {$row['firstname']} {$row['lastname']} ({$row['e_id']}) has been approved | Leave day(s): $leave_days.";
                log_activity($adminId, $action_type, $affected_feature, $details);

                header("Location: ../admin/leave_requests.php?status=approved");
            } else {
                error_log("Error updating leave balance: " . $conn->error);
                header("Location: ../admin/leave_requests.php?status=error");
            }
        }
    } elseif ($status === 'deny') {
        $deny_sql = "UPDATE leave_requests SET status = 'Denied', admin_approval = 'Admin Denied', admin_id = ? WHERE leave_id = ?";
        $deny_stmt = $conn->prepare($deny_sql);
        $deny_stmt->bind_param("ii", $adminId, $leave_id);

        if ($deny_stmt->execute()) {
            // Log the activity for denial
            $action_type = "Leave Request Denied";
            $affected_feature = "Leave Information";
            $details = "Leave request from {$row['firstname']} {$row['lastname']} ({$row['e_id']}) has been denied.";
            log_activity($adminId, $action_type, $affected_feature, $details);

            header("Location: ../admin/leave_requests.php?status=denied");
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
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>Leave Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' /> <!-- calendar -->
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
<style>
    .btn {
        transition: transform 0.3s, background-color 0.3s; /* Smooth transition */
        border-radius: 25px; 
    }

    .btn:hover {
        transform: translateY(-2px); /* Raise the button up */
    }
</style>
</head>

<body class="sb-nav-fixed bg-black">
    <nav class="sb-topnav navbar navbar-expand navbar-dark border-bottom border-1 border-warning bg-dark">
        <a class="navbar-brand ps-3 text-muted" href="../admin/dashboard.php">Admin Portal</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars text-light"></i></button>
    
    <!-- Flex container to hold both time/date and search form -->
        <div class="d-flex ms-auto me-0 me-md-3 my-2 my-md-0 align-items-center">
            <div class="text-light me-3 p-2 rounded shadow-sm bg-gradient" id="currentTimeContainer" 
            style="background: linear-gradient(45deg, #333333, #444444); border-radius: 5px;">
                <span class="d-flex align-items-center">
                    <span class="pe-2">
                        <i class="fas fa-clock"></i> 
                        <span id="currentTime">00:00:00</span>
                    </span>
                    <button class="btn btn-outline-warning btn-sm ms-2" type="button" onclick="toggleCalendar()">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="currentDate">00/00/0000</span>
                    </button>
                </span>
            </div>
            <form class="d-none d-md-inline-block form-inline">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <button class="btn btn-warning" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
            </div>
            </form>
        </div>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion bg-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu ">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading text-center text-muted">Your Profile</div>
                        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                            <li class="nav-item dropdown text">
                                <a class="nav-link dropdown-toggle text-light d-flex justify-content-center ms-4" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="<?php echo (!empty($adminInfo['pfp']) && $adminInfo['pfp'] !== 'defaultpfp.png') 
                                        ? htmlspecialchars($adminInfo['pfp']) 
                                        : '../img/defaultpfp.jpg'; ?>" 
                                        class="rounded-circle border border-light" width="120" height="120" alt="Profile Picture" />
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="../admin/profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                                    <li><hr class="dropdown-divider" /></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a></li>
                                </ul>
                            </li>
                            <li class="nav-item text-light d-flex ms-3 flex-column align-items-center text-center">
                                <span class="big text-light mb-1">
                                    <?php
                                        if ($adminInfo) {
                                        echo htmlspecialchars($adminInfo['firstname'] . ' ' . $adminInfo['middlename'] . ' ' . $adminInfo['lastname']);
                                        } else {
                                        echo "Admin information not available.";
                                        }
                                    ?>
                                </span>      
                                <span class="big text-light">
                                    <?php
                                        if ($adminInfo) {
                                        echo htmlspecialchars($adminInfo['role']);
                                        } else {
                                        echo "User information not available.";
                                        }
                                    ?>
                                </span>
                            </li>
                        </ul>
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning mt-3">Admin Dashboard</div>
                        <a class="nav-link text-light" href="../admin/dashboard.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTAD" aria-expanded="false" aria-controls="collapseTAD">
                            <div class="sb-nav-link-icon"><i class="fa fa-address-card"></i></div>
                            Time and Attendance
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseTAD" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/attendance.php">Attendance</a>
                                <a class="nav-link text-light" href="../admin/timesheet.php">Timesheet</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLM" aria-expanded="false" aria-controls="collapseLM">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-times"></i></div>
                            Leave Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/leave_requests.php">Leave Requests</a>
                                <a class="nav-link text-light" href="../admin/leave_history.php">Leave History</a>
                                <a class="nav-link text-light"  href="../admin/leave_allocation.php">Set Leave</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePM" aria-expanded="false" aria-controls="collapsePM">
                            <div class="sb-nav-link-icon"><i class="fas fa-line-chart"></i></div>
                            Performance Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/evaluation.php">Evaluation</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSR" aria-expanded="false" aria-controls="collapseSR">
                            <div class="sb-nav-link-icon"><i class="fa fa-address-card"></i></div>
                            Social Recognition
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseSR" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                              <a class="nav-link text-light" href="../admin/awardee.php">Awardee</a>
                              <a class="nav-link text-light" href="../admin/recognition.php">Generate Certificate</a>
                            </nav>
                        </div>
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning mt-3">Account Management</div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                            Accounts
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/calendar.php">Calendar</a>
                                <a class="nav-link text-light" href="../admin/admin.php">Admin Accounts</a>
                                <a class="nav-link text-light" href="../admin/employee.php">Employee Accounts</a>
                            </nav>
                        </div>
                        <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                        </div>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-black text-light border-top border-1 border-warning">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($adminInfo['role']); ?></div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid position-relative px-4">
                    <h1 class="mb-4 text-light">Leave Requests</h1>
                    <div class="container" id="calendarContainer" 
                        style="position: fixed; top: 9%; right: 0; z-index: 1050; 
                        width: 700px; display: none;">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="calendar" class="p-2"></div>
                            </div>
                        </div>
                    </div>                     
                    <div class="container">
                        <?php if (isset($_GET['status'])): ?>
                            <div id="status-alert" class="alert 
                                <?php if ($_GET['status'] === 'success'): ?>
                                    alert-success
                                <?php elseif ($_GET['status'] === 'error'): ?>
                                    alert-danger
                                <?php elseif ($_GET['status'] === 'not_exist'): ?>
                                    alert-warning
                                <?php elseif ($_GET['status'] === 'insufficient_balance'): ?>
                                    alert-warning
                                <?php endif; ?>" role="alert">
                                <?php if ($_GET['status'] === 'success'): ?>
                                    Leave request status updated successfully.
                                <?php elseif ($_GET['status'] === 'error'): ?>
                                    Error updating leave request status. Please try again.
                                <?php elseif ($_GET['status'] === 'not_exist'): ?>
                                    The leave request ID does not exist or could not be found.
                                <?php elseif ($_GET['status'] === 'insufficient_balance'): ?>
                                    Insufficient leave balance. The request cannot be approved.
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card mb-4 bg-dark text-light">
                        <div class="card-header border-bottom border-1 border-warning">
                            <i class="fas fa-table me-1"></i>
                            Pending Request
                        </div>
                        <div class="card-body">
                            <table id="datatablesSimple" class="table text-light text-center">
                                <thead>
                                    <tr>
                                        <th>Requested On</th>
                                        <th>Employee ID</th>
                                        <th>Employee Name</th>
                                        <th>Department</th>
                                        <th>Duration of Leave</th>
                                        <th>Deduction Leave</th>
                                        <th>Reason</th>
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
                                            ?>
                                        <tr>
                                        <td>
                                            <?php 
                                                if (isset($row['created_at'])) {
                                                    echo htmlspecialchars(date("F j, Y", strtotime($row['created_at']))) . ' <span class="text-warning"> | </span> ' . htmlspecialchars(date("g:i A", strtotime($row['created_at'])));
                                                } else {
                                                    echo "Not Available";
                                                }
                                            ?>
                                        </td>
                                            <td><?php echo htmlspecialchars($row['e_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                                            <td><?php echo htmlspecialchars(date("F j, Y", strtotime($row['start_date']))) . ' <span class="text-warning"> | </span> ' . htmlspecialchars(date("F j, Y", strtotime($row['end_date']))); ?></td>
                                            <td><?php echo htmlspecialchars($leave_days); ?> day/s</td>
                                            <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                                            <td>
                                                <?php if (!empty($row['proof'])): ?>
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#proofModal<?php echo $row['proof']; ?>">View</button>
                                                <?php else: ?>
                                                    No proof provided
                                                <?php endif; ?>
                                            </td>
                                            <div class="modal fade" id="proofModal<?php echo $row['proof']; ?>" tabindex="-1" aria-labelledby="proofModalLabel<?php echo $row['proof']; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content bg-dark text-light" style="width: 600px; height: 500px;">
                                                        <div class="modal-header border-bottom border-warning">
                                                            <h5 class="modal-title" id="proofModalLabel<?php echo $row['proof']; ?>">Proof of Leave</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body d-flex align-items-center justify-content-center" style="overflow-y: auto; height: calc(100% - 80px);">
                                                            <div id="proofCarousel<?php echo $row['proof']; ?>" class="carousel slide d-flex align-items-center justify-content-center" data-bs-ride="false">
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
                                                                                echo '<img src="' . htmlspecialchars($fullFilePath) . '" alt="Proof of Leave" class="d-block w-100" style="max-height: 400px; object-fit: contain;">';
                                                                                echo '</div>';
                                                                                $isActive = false;
                                                                            }
                                                                            // Check if the file is a PDF (this will just show an embed for PDFs)
                                                                            elseif (strtolower($fileExtension) === 'pdf') {
                                                                                echo '<div class="carousel-item ' . ($isActive ? 'active' : '') . '">';
                                                                                echo '<embed src="' . htmlspecialchars($fullFilePath) . '" type="application/pdf" width="100%" height="400px" />';
                                                                                echo '</div>';
                                                                                $isActive = false;
                                                                            }
                                                                            // Handle other document types (e.g., docx, txt) – just provide a link to view the document
                                                                            else {
                                                                                echo '<div class="carousel-item ' . ($isActive ? 'active' : '') . '">';
                                                                                echo '<a href="' . htmlspecialchars($fullFilePath) . '" target="_blank" class="btn btn-primary">View Document</a>';
                                                                                echo '</div>';
                                                                                $isActive = false;
                                                                            }
                                                                        }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Check if there's only one file; if yes, hide the carousel controls -->
                                                        <?php if ($fileCount > 1): ?>
                                                            <button class="carousel-control-prev btn btn-secondary position-absolute top-50 start-0 translate-middle-y w-auto" type="button" data-bs-target="#proofCarousel<?php echo $row['proof']; ?>" data-bs-slide="prev">
                                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                                <span class="visually-hidden">Previous</span>
                                                            </button>
                                                            <button class="carousel-control-next btn btn-secondary position-absolute top-50 end-0 translate-middle-y w-auto" type="button" data-bs-target="#proofCarousel<?php echo $row['proof']; ?>" data-bs-slide="next">
                                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                                <span class="visually-hidden">Next</span>
                                                            </button>
                                                        <?php endif; ?>

                                                        <div class="modal-footer border-top border-warning">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <td class="text-center">
                                                <div class="d-flex justify-content-center mb-0">
                                                <button class="btn btn-success btn-sm me-2" onclick="confirmAction('approve', <?php echo $row['leave_id']; ?>)">Approve</button>
                                                <button class="btn btn-danger btn-sm" onclick="confirmAction('deny', <?php echo $row['leave_id']; ?>)">Deny</button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                    <!-- MODALS -->
                <div class="container">
                    <div class="text-center">
                        <a href="#" class="text-light btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#setLeaveModal">Set Leave Credit</a>
                    </div>
                    <div class="modal fade" id="setLeaveModal" tabindex="-1" aria-labelledby="setLeaveModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content bg-dark">
                                <div class="modal-header border-bottom border-warning">
                                    <h5 class="modal-title text-light" id="setLeaveModalLabel">Set Leave Allocations</h5>
                                    <button type="button" class="close text-light bg-dark" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="../db/set_leave.php">
                                        <div class="form-group">
                                            <label class="text-light mt-3 mb-1" for="employee_leaves">Leave Days for Employees:</label>
                                            <input type="number" name="employee_leaves" id="employee_leaves" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="text-light mt-3 mb-1" for="employee_id">Select Employee:</label>
                                            <select name="employee_id" id="employee_id" class="form-control">
                                                <option value="all">All Employees</option>
                                                <?php
                                                // Fetch employees from the database for the dropdown
                                                    $employees_sql = "SELECT e_id, firstname, lastname FROM employee_register";
                                                    $employees_result = $conn->query($employees_sql);
                                                    while ($employee = $employees_result->fetch_assoc()) {
                                                        echo "<option value='" . $employee['e_id'] . "'>" . $employee['firstname'] . " " . $employee['lastname'] . "</option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-3">Set Allocations</button>
                                    </form>
                                </div>
                                <div class="modal-footer border-top border-warning">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
                <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark text-light">
                            <div class="modal-header border-bottom border-warning">
                                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to log out?
                            </div>
                            <div class="modal-footer border-top border-warning">
                                <button type="button" class="btn border-secondary text-light" data-bs-dismiss="modal">Cancel</button>
                                <form action="../admin/logout.php" method="POST">
                                    <button type="submit" class="btn btn-danger">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>  
            <footer class="py-4 bg-dark text-light mt-auto border-top border-warning">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2024</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
<script>
    //CALENDAR 
    let calendar;
        function toggleCalendar() {
            const calendarContainer = document.getElementById('calendarContainer');
                if (calendarContainer.style.display === 'none' || calendarContainer.style.display === '') {
                    calendarContainer.style.display = 'block';
                    if (!calendar) {
                        initializeCalendar();
                        }
                    } else {
                        calendarContainer.style.display = 'none';
                    }
        }

        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    height: 440,  
                    events: {
                    url: '../db/holiday.php',  
                    method: 'GET',
                    failure: function() {
                    alert('There was an error fetching events!');
                    }
                    }
                });

                calendar.render();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const currentDateElement = document.getElementById('currentDate');
            const currentDate = new Date().toLocaleDateString(); 
            currentDateElement.textContent = currentDate; 
        });

        document.addEventListener('click', function(event) {
            const calendarContainer = document.getElementById('calendarContainer');
            const calendarButton = document.querySelector('button[onclick="toggleCalendar()"]');

                if (!calendarContainer.contains(event.target) && !calendarButton.contains(event.target)) {
                    calendarContainer.style.display = 'none';
                    }
        });
        //CALENDAR END

        //TIME 
        function setCurrentTime() {
            const currentTimeElement = document.getElementById('currentTime');
            const currentDateElement = document.getElementById('currentDate');

            const currentDate = new Date();
    
            currentDate.setHours(currentDate.getHours() + 0);
                const hours = currentDate.getHours();
                const minutes = currentDate.getMinutes();
                const seconds = currentDate.getSeconds();
                const formattedHours = hours < 10 ? '0' + hours : hours;
                const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
                const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

            currentTimeElement.textContent = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
            currentDateElement.textContent = currentDate.toLocaleDateString();
        }
        setCurrentTime();
        setInterval(setCurrentTime, 1000);
        //TIME END

        //LEAVE STATUS 
        function confirmAction(action, requestId) {
            let confirmation = confirm(`Are you sure you want to ${action} this leave request?`);
                if (confirmation) {
                window.location.href = `leave_requests.php?leave_id=${requestId}&status=${action}`;
                }
        }
        //LEAVE STATUS END


    // Automatically hide the alert after 10 seconds (10,000 milliseconds)
    setTimeout(function() {
        var alertElement = document.getElementById('status-alert');
        if (alertElement) {
            alertElement.style.transition = "opacity 1s ease"; // Add transition for smooth fade-out
            alertElement.style.opacity = 0; // Set the opacity to 0 (fade out)
            
            setTimeout(function() {
                alertElement.remove(); // Remove the element from the DOM after fade-out
            }, 1000); // Wait 1 second after fade-out to remove the element completely
        }
    }, 5000); // 10 seconds delay
    


</script>
<!-- Only keep the latest Bootstrap 5 version -->
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script src="../js/datatables-simple-demo.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
<script src="../js/admin.js"></script>
</body>
</html>
