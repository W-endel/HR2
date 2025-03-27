<?php
// Set the default timezone to Asia/Manila (Philippines)
date_default_timezone_set('Asia/Manila');

session_start();

if (!isset($_SESSION['a_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../db/db_conn.php';

// Fetch user info
$adminId = $_SESSION['a_id'];
$sql = "SELECT firstname, middlename, lastname, email, role, pfp FROM admin_register WHERE a_id = ?";
$stmt = $conn->prepare($sql);

// Check if statement preparation failed
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user information
$adminInfo = $result->fetch_assoc();

// Set profile picture or use default if not set
$profilePicture = !empty($adminInfo['pfp']) ? $adminInfo['pfp'] : '../img/defaultpfp.jpg';

// Close statement
$stmt->close();

// First, get the total number of employees from employee_register
$sqlTotalEmployees = "SELECT COUNT(*) as total FROM employee_register";
$resultTotal = $conn->query($sqlTotalEmployees);
$totalEmployeeCount = $resultTotal->fetch_assoc()['total'];

// Fetch attendance data for the current day
try {
    // Get today's date in the Philippines timezone
    $currentDate = date('Y-m-d'); // This will now use Asia/Manila timezone

    // Fetch attendance data for the current day
    $sql = "
        SELECT 
            status,
            COUNT(*) AS count
        FROM attendance_log
        WHERE DATE(attendance_date) = ?
        GROUP BY status
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize counts
    $presentCount = 0;
    $lateCount = 0;
    $absentCount = 0;
    $leaveCount = 0;

    // Calculate counts for each status
    while ($row = $result->fetch_assoc()) {
        switch ($row['status']) {
            case 'Present':
                $presentCount += $row['count'];
                break;
            case 'Late':
            case 'Undertime':
            case 'Overtime':
                $lateCount += $row['count']; // Count Late, Undertime, and Overtime separately
                $presentCount += $row['count']; // Also count them as present
                break;
            case 'Absent':
                $absentCount = $row['count'];
                break;
            case 'Leave':
                $leaveCount = $row['count'];
                break;
        }
    }

    // Calculate total employees for the day (those who have attendance records)
    $totalEmployeesWithAttendance = $presentCount + $absentCount;

    // Calculate percentages for progress bars based on total employee count
    $presentPercentage = $totalEmployeeCount > 0 ? ($presentCount / $totalEmployeeCount) * 100 : 0;
    $leavePercentage = $totalEmployeeCount > 0 ? ($leaveCount / $totalEmployeeCount) * 100 : 0;
    $absentPercentage = $totalEmployeeCount > 0 ? ($absentCount / $totalEmployeeCount) * 100 : 0;
} catch (Exception $e) {
    die("Error fetching attendance data: " . $e->getMessage());
} finally {
    $conn->close();
}

// Fetch leave status data
include '../db/db_conn.php';
$sql = "SELECT status, COUNT(*) as count FROM leave_requests GROUP BY status";
$result = $conn->query($sql);

$status_counts = [
    'Approved' => 0,
    'Supervisor Approved' => 0,
    'Denied' => 0,
];
while ($row = $result->fetch_assoc()) {
    $status = $row['status'];
    if (isset($status_counts[$status])) {
        $status_counts[$status] = $row['count'];
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="HR2 Admin Dashboard" />
    <meta name="author" content="HR2 Team" />
    <title>Admin Dashboard | HR2</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            background-color: rgba(16, 17, 18);
        }
        
        .sb-nav-fixed #layoutSidenav #layoutSidenav_content {
            background-color: rgba (16, 17, 18);
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            background: rgba(33, 37, 41);
        }
        
        .card-body {
            padding: 1.5rem;
            background-color: rgba(33, 37, 41);
        }
        
        /* Stats Cards */
        .stats-card {
            padding: 1.5rem;
            border-radius: 15px;
            position: relative;
            overflow: hidden;
            height: 100%;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: linear-gradient(135deg, #1e1e1e, #2d2d2d);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.05), transparent);
            z-index: 1;
        }
        
        .stats-card .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
            opacity: 0.9;
        }
        
        .stats-card .stats-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            position: relative;
            z-index: 2;
        }
        
        .stats-card .stats-label {
            font-size: 1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
            opacity: 0.9;
        }
        
        .stats-card .progress {
            height: 8px;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.1);
            margin-top: auto;
            position: relative;
            z-index: 2;
            overflow: hidden;
        }
        
        .stats-card .progress-bar {
            border-radius: 4px;
            transition: width 1s ease-in-out;
        }
        
        /* Card Variants */
        .stats-present {
            background: linear-gradient(135deg, #1e3a2f, #2d4f3e);
        }
        
        .stats-present .stats-icon {
            color: #4ade80;
        }
        
        .stats-late {
            background: linear-gradient(135deg, #3a2e1e, #4f3d2d);
        }
        
        .stats-late .stats-icon {
            color: #fbbf24;
        }
        
        .stats-absent {
            background: linear-gradient(135deg, #3a1e1e, #4f2d2d);
        }
        
        .stats-absent .stats-icon {
            color: #f87171;
        }

        .stats-total{
            background: linear-gradient(135deg, #1a2a4a, #2d3b5f);
        }

        .stats-total .stats-icon {
            color: #60a5fa;
        }
        
        
        /* Chart Container */
        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
            width: 100%;
        }
        
        /* Modal Styles */
        .custom-modal {
            background: #1a1a1a;
            color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }
        
        .custom-modal .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
        }
        
        .custom-modal .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
        }
        
        .custom-body {
            max-height: 400px;
            overflow-y: auto;
            padding: 1.5rem;
            scrollbar-width: thin;
            scrollbar-color: #555 #1a1a1a;
        }
        
        .custom-body::-webkit-scrollbar {
            width: 8px;
        }
        
        .custom-body::-webkit-scrollbar-track {
            background: #1a1a1a;
        }
        
        .custom-body::-webkit-scrollbar-thumb {
            background-color: #555;
            border-radius: 10px;
        }
        
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    
        
        /* Date Display */
        .date-display {
            font-size: 1rem;
            color: #adb5bd;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .date-display i {
            margin-right: 0.5rem;
            color: #4ade80;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .stats-card {
                min-height: 150px;
                margin-bottom: 1rem;
            }
            
            .stats-card .stats-value {
                font-size: 2rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
        
<body class="sb-nav-fixed">
    <?php include 'navbar.php'; ?>
    <div id="layoutSidenav">
        <?php include 'sidebar.php';?>        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <div class="mb-4 text-light">
                        <h1>Dashboard</h1>
                    </div>
                    
                    <div class="container-fluid" id="calendarContainer" 
                        style="position: fixed; top: 7%; right: 40; z-index: 1050; 
                        max-width: 100%; display: none;">
                        <div class="row">
                            <div class="col-md-9 mx-auto">
                                <div id="calendar" class="p-2"></div>
                            </div>
                        </div>
                    </div> 
                    
                    <!-- Attendance Stats Section -->
                    <div class="row mb-4 mt-2">
                        <div class="col-12 mb-4">
                            <h5 class="text-light mb-3"><i class="fas fa-user-clock me-2"></i>Today's Attendance</h5>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="stats-card stats-total">
                                <div class="stats-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stats-value text-light">
                                    <?php echo $totalEmployeeCount; ?>
                                </div>
                                <div class="stats-label text-light">Total Employees</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="stats-card stats-present">
                                <div class="stats-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stats-value text-light">
                                    <?php echo $presentCount; ?>
                                </div>
                                <div class="stats-label text-light">Present</div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: <?php echo $presentPercentage; ?>%" role="progressbar" aria-valuenow="<?php echo $presentPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="stats-card stats-absent">
                                <div class="stats-icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stats-value text-light">
                                    <?php echo $absentCount; ?>
                                </div>
                                <div class="stats-label text-light">Absent</div>
                                <div class="progress">
                                    <div class="progress-bar bg-danger" style="width: <?php echo $absentPercentage; ?>%" role="progressbar" aria-valuenow="<?php echo $absentPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="stats-card stats-late">
                                <div class="stats-icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div class="stats-value text-light">
                                    <?php echo $leaveCount; ?>
                                </div>
                                <div class="stats-label text-light">On Leave</div>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" style="width: <?php echo $leavePercentage; ?>%" role="progressbar" aria-valuenow="<?php echo $leavePercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <h5 class="text-light mb-3"><i class="fas fa-chart-pie me-2"></i>Analytics Overview</h5>
                        </div>
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <a class="text-light" href="../admin/leave_requests.php">
                                        <i class="fas fa-chart-pie me-1"></i> 
                                        Leave Request Status
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="leaveStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <a class="text-light" href="../admin/employee.php">
                                        <i class="fas fa-users me-1"></i>
                                        Employee Count per Department
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="employeeDepartmentChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Performance Summary Section -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <a class="text-light" href="#">
                                        <i class="fas fa-chart-line me-1"></i>
                                        Employees' Performance Summary
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="employeePerformanceChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            
            <!-- All Notifications Modal -->
            <div class="modal fade" id="allNotificationsModal" tabindex="-1" aria-labelledby="allNotificationsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content custom-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="allNotificationsModalLabel">
                                <i class="fas fa-bell me-2"></i> All Notifications
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body custom-body" id="allNotificationsModalBody">
                            <!-- All notifications will be loaded here -->
                            <ul class="list-group">
                                <?php if(isset($notifications) && !empty($notifications)): ?>
                                    <?php foreach ($notifications as $notification): ?>
                                        <li class="list-group-item custom-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                                            <div class="d-flex align-items-center">
                                                <div class="notif-icon">
                                                    <?php if (!$notification['is_read']): ?>
                                                        <i class="fas fa-circle text-success"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-circle text-secondary"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <?php echo htmlspecialchars($notification['message']); ?>
                                                    <div class="time-stamp">
                                                        <i class="far fa-clock me-1"></i> Just now
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="list-group-item custom-item">
                                        <div class="text-center py-4">
                                            <i class="fas fa-bell-slash fa-3x mb-3 text-secondary"></i>
                                            <p>No notifications at this time</p>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                            <?php if(isset($notifications) && !empty($notifications)): ?>
                                <button type="button" class="btn btn-success" id="markAllReadBtn">
                                    <i class="fas fa-check-double me-1"></i> Mark All as Read
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Logout Modal -->
            <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content custom-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="logoutModalLabel">
                                <i class="fas fa-sign-out-alt me-2"></i> Confirm Logout
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center py-4">
                                <i class="fas fa-question-circle fa-3x mb-3 text-warning"></i>
                                <p>Are you sure you want to log out?</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                            <form action="../admin/logout.php" method="POST">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Loading Modal -->
            <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-transparent border-0">
                        <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                            <div class="coin-spinner"></div>
                            <div class="mt-3 text-light fw-bold">Please wait...</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Error Modal -->
            <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content custom-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="errorModalLabel">
                                <i class="fas fa-exclamation-triangle me-2 text-danger"></i> Error
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center py-3">
                                <i class="fas fa-times-circle fa-3x mb-3 text-danger"></i>
                                <p id="errorMessage" class="lead"></p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'footer.php'; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Loading Modal Handler
            const buttons = document.querySelectorAll('.loading');
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));

            // Loop through each button and add a click event listener
            buttons.forEach(button => {
                button.addEventListener('click', function (event) {
                    // Show the loading modal
                    loadingModal.show();

                    // Disable the button to prevent multiple clicks
                    this.classList.add('disabled');

                    // Handle form submission buttons
                    if (this.closest('form')) {
                        event.preventDefault(); // Prevent the default form submit

                        // Submit the form after a short delay
                        setTimeout(() => {
                            this.closest('form').submit();
                        }, 1500);
                    }
                    // Handle links
                    else if (this.tagName.toLowerCase() === 'a') {
                        event.preventDefault(); // Prevent the default link behavior

                        // Redirect after a short delay
                        setTimeout(() => {
                            window.location.href = this.href;
                        }, 1500);
                    }
                });
            });

            // Hide the loading modal when navigating back and enable buttons again
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) { // Check if the page was loaded from cache (back button)
                    loadingModal.hide();

                    // Re-enable all buttons when coming back
                    buttons.forEach(button => {
                        button.classList.remove('disabled');
                    });
                }
            });

            // Animate stats cards on page load
            const statsCards = document.querySelectorAll('.stats-card');
            statsCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
            });

            // Leave Status Chart
            const leaveStatusCtx = document.getElementById('leaveStatusChart').getContext('2d');
            const leaveStatusData = {
                labels: ['Approved', 'Pending', 'Denied'],
                datasets: [{
                    data: [
                        <?php echo $status_counts['Approved']; ?>,
                        <?php echo $status_counts['Supervisor Approved']; ?>,
                        <?php echo $status_counts['Denied']; ?>
                    ],
                    backgroundColor: ['#4ade80', '#fbbf24', '#f87171'],
                    borderWidth: 0,
                    borderRadius: 5,
                    hoverOffset: 10
                }]
            };

            // Check if all data values are zero (no data)
            const totalRequests = leaveStatusData.datasets[0].data.reduce((sum, value) => sum + value, 0);

            if (totalRequests === 0) {
                // Display a message and icon if there is no data
                const canvas = leaveStatusCtx.canvas;
                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2;

                // Draw an icon
                leaveStatusCtx.font = '48px FontAwesome';
                leaveStatusCtx.fillStyle = 'white';
                leaveStatusCtx.textAlign = 'center';
                leaveStatusCtx.textBaseline = 'middle';
                leaveStatusCtx.fillText('😐', centerX, centerY - 30);

                // Display the message
                leaveStatusCtx.font = '16px Poppins';
                leaveStatusCtx.fillText('No pending request at this time...', centerX, centerY + 20);
            } else {
                // Create the chart if there is data
                new Chart(leaveStatusCtx, {
                    type: 'doughnut',
                    data: leaveStatusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'left',
                                labels: {
                                    color: 'white',
                                    font: {
                                        size: 14,
                                        family: 'Poppins',
                                        weight: '500'
                                    },
                                    padding: 20
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 15,
                                bodyFont: {
                                    size: 14,
                                    family: 'Poppins'
                                },
                                titleFont: {
                                    size: 16,
                                    family: 'Poppins',
                                    weight: 'bold'
                                },
                                cornerRadius: 8,
                                caretSize: 6
                            }
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true,
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }

            // Fetch Average Performance Data from the Backend
            async function fetchAveragePerformance() {
                try {
                    const response = await fetch('../db/evaluationAverage.php');
                    if (!response.ok) {
                        throw new Error('Failed to fetch average performance data');
                    }
                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error fetching average performance data:', error);
                    return {};
                }
            }

            // Get current year
            const currentYear = new Date().getFullYear();

            // Render Average Performance Line Chart
            async function renderAveragePerformanceChart() {
                // Fetch average performance data
                const monthlyAverages = await fetchAveragePerformance();
                
                // Create array of all months in the current year
                const allMonths = Array.from({length: 12}, (_, i) => {
                    return new Date(currentYear, i, 1).toLocaleString('default', { month: 'long' });
                });
                
                // Initialize data for all months with 0
                const monthlyData = {};
                allMonths.forEach(month => {
                    monthlyData[month] = 0;
                });
                
                // Fill in actual data where available
                Object.keys(monthlyAverages).forEach(monthYear => {
                    // Extract month name from the key (assuming format like "January 2023")
                    const [monthName, year] = monthYear.split(' ');
                    
                    // Only include data for the current year
                    if (year == currentYear && allMonths.includes(monthName)) {
                        const monthData = monthlyAverages[monthYear];
                        const total = (monthData.quality +
                                    monthData.communication_skills +
                                    monthData.punctuality +
                                    monthData.initiative +
                                    monthData.teamwork) / 5;
                        monthlyData[monthName] = total.toFixed(2);
                    }
                });
                
                // Convert the monthly data to an array in month order
                const totalAverages = allMonths.map(month => monthlyData[month]);

                // Get the canvas context
                const employeePerformanceCtx = document.getElementById("employeePerformanceChart").getContext("2d");

                // Gradient for the line
                const gradient = employeePerformanceCtx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(74, 222, 128, 1)');
                gradient.addColorStop(1, 'rgba(74, 222, 128, 0.1)');

                // Render the chart
                new Chart(employeePerformanceCtx, {
                    type: "line",
                    data: {
                        labels: allMonths, // All 12 months
                        datasets: [{
                            label: "Total Average Performance",
                            data: totalAverages,
                            borderColor: "#4ade80",
                            backgroundColor: gradient,
                            borderWidth: 3,
                            pointBackgroundColor: "#4ade80",
                            pointBorderColor: "#fff",
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            tension: 0.4,
                            fill: true
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: "top",
                                labels: {
                                    color: "#fff",
                                    font: {
                                        family: 'Poppins',
                                        size: 14
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 15,
                                bodyFont: {
                                    size: 14,
                                    family: 'Poppins'
                                },
                                titleFont: {
                                    size: 16,
                                    family: 'Poppins',
                                    weight: 'bold'
                                },
                                cornerRadius: 8,
                                caretSize: 6,
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.raw}`;
                                    },
                                    title: function(context) {
                                        return `${context[0].label} ${currentYear}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 6, // Assuming performance is rated out of 5
                                grid: {
                                    color: "rgba(255, 255, 255, 0.1)",
                                },
                                ticks: {
                                    color: "#fff",
                                    font: {
                                        family: 'Poppins',
                                        size: 12
                                    }
                                },
                                title: {
                                    display: true,
                                    text: "Average Score",
                                    color: "#fff",
                                    font: {
                                        family: 'Poppins',
                                        size: 14,
                                        weight: 'bold'
                                    }
                                },
                            },
                            x: {
                                grid: {
                                    color: "rgba(255, 255, 255, 0.1)",
                                },
                                ticks: {
                                    color: "#fff",
                                    font: {
                                        family: 'Poppins',
                                        size: 12
                                    }
                                },
                                title: {
                                    display: true,
                                    text: `Month (${currentYear})`,
                                    color: "#fff",
                                    font: {
                                        family: 'Poppins',
                                        size: 14,
                                        weight: 'bold'
                                    }
                                },
                            },
                        },
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    },
                });
            }

            // Fetch Employee Count Data from the Backend
            async function fetchEmployeeCount() {
                try {
                    const response = await fetch('../db/employeeCount.php');
                    if (!response.ok) {
                        throw new Error('Failed to fetch employee count data');
                    }
                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error fetching employee count data:', error);
                    return [];
                }
            }

            // Render Employee Count Bar Chart
            async function renderEmployeeCountChart() {
                // Fetch real employee count data
                const departments = await fetchEmployeeCount();

                // Get the canvas context
                const employeeDepartmentCtx = document.getElementById("employeeDepartmentChart").getContext("2d");

                // Render the chart
                new Chart(employeeDepartmentCtx, {
                    type: "bar",
                    data: {
                        labels: departments.map(dept => dept.name),
                        datasets: [{
                            label: "Employee Count",
                            data: departments.map(dept => dept.count),
                            backgroundColor: [
                                '#4ade80',
                                '#60a5fa',
                                '#f472b6',
                                '#fbbf24',
                                '#a78bfa',
                                '#34d399'
                            ],
                            borderColor: 'rgba(0, 0, 0, 0.1)',
                            borderWidth: 1,
                            borderRadius: 8,
                            maxBarThickness: 50
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 15,
                                bodyFont: {
                                    size: 14,
                                    family: 'Poppins'
                                },
                                titleFont: {
                                    size: 16,
                                    family: 'Poppins',
                                    weight: 'bold'
                                },
                                cornerRadius: 8,
                                caretSize: 6,
                                callbacks: {
                                    label: function (context) {
                                        const value = context.raw || 0;
                                        return `${value} employees`;
                                    },
                                },
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: "rgba(255, 255, 255, 0.1)",
                                },
                                ticks: {
                                    color: "#fff",
                                    font: {
                                        family: 'Poppins',
                                        size: 12
                                    }
                                },
                                title: {
                                    display: true,
                                    text: "Number of Employees",
                                    color: "#fff",
                                    font: {
                                        family: 'Poppins',
                                        size: 14,
                                        weight: 'bold'
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    color: "rgba(255, 255, 255, 0.1)",
                                    display: false
                                },
                                ticks: {
                                    color: "#fff",
                                    font: {
                                        family: 'Poppins',
                                        size: 12
                                    }
                                }
                            },
                        },
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    },
                });
            }

            // Initialize all charts
            renderAveragePerformanceChart();
            renderEmployeeCountChart();

            // Mark all notifications as read
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', async function() {
                    try {
                        const response = await fetch('../db/mark_all_read.php', {
                            method: 'POST'
                        });
                        
                        if (response.ok) {
                            const unreadItems = document.querySelectorAll('.custom-item.unread');
                            unreadItems.forEach(item => {
                                item.classList.remove('unread');
                                item.classList.add('read');
                                const icon = item.querySelector('.notif-icon i');
                                if (icon) {
                                    icon.className = 'far fa-circle text-secondary';
                                }
                            });
                            
                            // Update notification counter in navbar if it exists
                            const notifCounter = document.getElementById('notificationCounter');
                            if (notifCounter) {
                                notifCounter.textContent = '0';
                                notifCounter.style.display = 'none';
                            }
                        }
                    } catch (error) {
                        console.error('Error marking notifications as read:', error);
                    }
                });
            }
        });
    </script>
</body>
</html>