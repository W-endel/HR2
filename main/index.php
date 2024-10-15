<?php
session_start();


if (!isset($_SESSION['a_id'])) {
    header("Location: ../main/adminlogin.php");
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
$profilePicture = !empty($adminInfo['pfp']) ? $adminInfo['pfp'] : '../img/defaultpfp.png';

// Close statement and connection
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  </head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark border-bottom border-2 border-warning bg-dark">
        <a class="navbar-brand ps-3 text-light" href="../main/index.php">Microfinance</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars text-light"></i></button>
           <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                </div>
           </form>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
             <li class="nav-item text-light d-flex flex-column align-items-start">
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
            <li class="nav-item dropdown text">
                <a class="nav-link dropdown-toggle text-light" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="../img/defaultpfp.png" class="rounded-circle border border-dark" width="40" height="40" />
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="../main/profile.php">Profile</a></li>
                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li><a class="dropdown-item" href="../main/adminlogout.php" onclick="confirmLogout(event)">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion bg-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu ">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading text-center bg-black text-info border-bottom border-2 border-warning">Logo</div>
                        <a class="nav-link text-light" href="../main/index.php">
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
                                <a class="nav-link text-light" href="../main/tad_display.php">Attendance Report</a>
                                <a class="nav-link text-light" href="../main/tad_timesheet.php">Timesheet</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLM" aria-expanded="false" aria-controls="collapseLM">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-times"></i></div>
                            Leave Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../main/leave_status.php">Leave Status</a>
                                <a class="nav-link text-light" href="../main/leave_history.php">Leave History</a>
                                <a class="nav-link text-light"  href="../main/leave_allocation.php">Set Leave</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePM" aria-expanded="false" aria-controls="collapsePM">
                            <div class="sb-nav-link-icon"><i class="fas fa-line-chart"></i></div>
                            Performance Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../main/admin_department.php">Evaluation</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSR" aria-expanded="false" aria-controls="collapseSR">
                            <div class="sb-nav-link-icon"><i class="fa fa-address-card"></i></div>
                            Social Recognition
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseSR" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../main/rating.php">View Ratings</a>
                            </nav>
                        </div>
                        <div class="sb-sidenav-menu-heading text-center bg-black text-info border-top border-bottom border-2 border-warning">Account Management</div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                            Accounts
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../main/calendar.php">Calendar</a>
                                <a class="nav-link text-light" href="../main/admin.php">Admin Accounts</a>
                                <a class="nav-link text-light" href="../main/employee.php">Employee Accounts</a>
                            </nav>
                        </div>
                        <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                        </div>
                        <div class="sb-sidenav-menu-heading bg-black text-info text-center border-top border-bottom border-2 border-warning">Addons</div>
                        <a class="nav-link text-light" href="../main/charts.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                            Charts
                        </a>
                        <a class="nav-link text-light" href="../main/tables.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                            Tables
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-black text-light border-top border-2 border-warning">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($adminInfo['firstname'] . ' ' . $adminInfo['lastname']); ?></div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
<main class="bg-black">
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-light">Dashboard</h1>
        <div class="container mb-5">
    <div class="row justify-content-end">
        <div class="col-md-8 ms-3 col-lg-5 text-light"> <!-- Adjust the column size as needed -->
            <div id="calendar" class="small"></div>
        </div>
    </div>
</div>
        <div class="row mb-4">
            <div class="col-xl-6">
                <div class="card mb-4">
                    <div class="card-header bg-black text-light border-bottom border-2 border-warning">
                        <i class="fas fa-chart-pie me-1"></i> 
                        Leave Request Status
                    </div>
                    <div class="card-body bg-dark">
                        <canvas id="leaveStatusChart" width="300" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                DataTable Example
            </div>
            <div class="card-body">
                <table id="datatablesSimple">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Office</th>
                            <th>Age</th>
                            <th>Start date</th>
                            <th>Salary</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Office</th>
                            <th>Age</th>
                            <th>Start date</th>
                            <th>Salary</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <tr>
                            <td>Zorita Serrano</td>
                            <td>Software Engineer</td>
                            <td>San Francisco</td>
                            <td>56</td>
                            <td>2012/06/01</td>
                            <td>$115,000</td>
                        </tr>
                        <tr>
                            <td>Jennifer Acosta</td>
                            <td>Junior Javascript Developer</td>
                            <td>Edinburgh</td>
                            <td>43</td>
                            <td>2013/02/01</td>
                            <td>$75,650</td>
                        </tr>
                        <tr>
                            <td>Cara Stevens</td>
                            <td>Sales Assistant</td>
                            <td>New York</td>
                            <td>46</td>
                            <td>2011/12/06</td>
                            <td>$145,600</td>
                        </tr>
                        <tr>
                            <td>Hermione Butler</td>
                            <td>Regional Director</td>
                            <td>London</td>
                            <td>47</td>
                            <td>2011/03/21</td>
                            <td>$356,250</td>
                        </tr>
                        <tr>
                            <td>Lael Greer</td>
                            <td>Systems Administrator</td>
                            <td>London</td>
                            <td>21</td>
                            <td>2009/02/27</td>
                            <td>$103,500</td>
                        </tr>
                        <tr>
                            <td>Jonas Alexander</td>
                            <td>Developer</td>
                            <td>San Francisco</td>
                            <td>30</td>
                            <td>2010/07/14</td>
                            <td>$86,500</td>
                        </tr>
                        <tr>
                            <td>Shad Decker</td>
                            <td>Regional Director</td>
                            <td>Edinburgh</td>
                            <td>51</td>
                            <td>2008/11/13</td>
                            <td>$183,000</td>
                        </tr>
                        <tr>
                            <td>Michael Bruce</td>
                            <td>Javascript Developer</td>
                            <td>Singapore</td>
                            <td>29</td>
                            <td>2011/06/27</td>
                            <td>$183,000</td>
                        </tr>
                        <tr>
                            <td>Donna Snider</td>
                            <td>Customer Support</td>
                            <td>New York</td>
                            <td>27</td>
                            <td>2011/01/25</td>
                            <td>$112,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- for leaveStatusChart -->
    <?php
    // Database configuration
include '../db/db_conn.php';

    // Fetch leave status counts
    $sql = "SELECT status, COUNT(*) as count FROM leave_requests GROUP BY status";
    $result = $conn->query($sql);

    // Initialize counts
    $status_counts = [
        'Approved' => 0,
        'Pending' => 0,
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
</main>
            <footer class="py-4 bg-black mt-auto border-top border-2 border-warning">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Doughnut chart data
        const data = {
            labels: ['Approved', 'Pending', 'Denied'],
            datasets: [{
                data: [
                    <?php echo $status_counts['Approved']; ?>,
                    <?php echo $status_counts['Pending']; ?>,
                    <?php echo $status_counts['Denied']; ?>
                ],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
            }]
        };

        // Doughnut chart configuration
        const leaveStatusCtx = document.getElementById('leaveStatusChart').getContext('2d');
        const leaveStatusChart = new Chart(leaveStatusCtx, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Leave Request Statuses'
                }
            }
        });
        //for leaveStatusChart

        //for calendar only
        document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: {
            url: 'holiday.php',  // Endpoint created in step 2
            method: 'GET',
            failure: function() {
                alert('There was an error fetching events!');
            }
        },
        eventDidMount: function(info) {
            // Additional customization of events if needed
        },
    });

    calendar.render();
});
        //for calendar only

        //for leave request (error)
 document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid'],
                initialView: 'dayGridMonth',
                dateClick: function(info) {
                    fetchLeaveData(info.dateStr);
                },
                events: '/path/to/your/events/api', // Update with your API endpoint that returns leave data
            });

            calendar.render();

            function fetchLeaveData(date) {
                fetch(`leave_data.php?date=${date}`)
                .then(response => response.json())
                .then(data => {
                    let leaveDetails = 'Employees on leave:\n';
                    if (data.length > 0) {
                        data.forEach(employee => {
                            leaveDetails += `${employee.name} (${employee.leave_type})\n`;
                        });
                    } else {
                        leaveDetails = 'No employees on leave for this day.';
                    }
                    alert(leaveDetails); // You can replace this with a modal or a more styled output
                })
                .catch(error => {
                    console.error('Error fetching leave data:', error);
                    alert('An error occurred while fetching leave data.');
                });
            }
        });
            //for leave request (error)

    </script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/admin.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="../js/datatables-simple-demo.js"></script>

</body>

</html>
