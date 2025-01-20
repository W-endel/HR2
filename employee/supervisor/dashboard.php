<?php
session_start();
if (!isset($_SESSION['e_id']))  {
    header("Location: ../../employee/login.php"); // Redirect to login if not logged in
    exit();
}

include '../../db/db_conn.php';

// Fetch user info
$employeeId = $_SESSION['e_id'];
$sql = "SELECT firstname, middlename, lastname, email, role, position, pfp FROM employee_register WHERE e_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$employeeInfo = $result->fetch_assoc();
$stmt->close();
$conn->close();

$profilePicture = !empty($employeeInfo['profile_picture']) ? $employeeInfo['profile_picture'] : '../../img/defaultpfp.png';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Employee Dashboard | HR2</title>
    <link href="../../css/styles.css" rel="stylesheet" />
    <link href="../../css/calendar.css" rel="stylesheet"/>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed bg-black">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark border-bottom border-1 border-warning">
        <a class="navbar-brand ps-3 text-muted" href="../../employee/supervisor/dashboard.php">Employee Portal</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars text-light"></i></button>
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
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                         <div class="sb-sidenav-menu-heading text-center text-muted">Profile</div>  
                        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-light d-flex justify-content-center ms-4" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="<?php echo (!empty($employeeInfo['pfp']) && $employeeInfo['pfp'] !== 'defaultpfp.png') 
                                        ? htmlspecialchars($employeeInfo['pfp']) 
                                        : '../../img/defaultpfp.jpg'; ?>" 
                                        class="rounded-circle border border-light" width="120" height="120" alt="" />
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="../../employee/supervisor/profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                                    <li><hr class="dropdown-divider" /></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a></li>
                                </ul>
                            </li>
                            <li class="nav-item text-light d-flex ms-3 flex-column align-items-center text-center">
                                <span class="big text-light mb-1">
                                    <h4><?php echo htmlspecialchars($employeeInfo['firstname'] . ' ' . $employeeInfo['middlename'] . ' ' . $employeeInfo['lastname']); ?></h4>
                                </span>
                                <span class="big text-light">
                                    <h5><?php echo htmlspecialchars($employeeInfo['position']); ?></h5>
                                </span>
                            </li>
                        </ul>
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning mt-3">Employee Dashboard</div>
                        <a class="nav-link text-light" href="../../employee/supervisor/dashboard.php">
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
                                <a class="nav-link text-light" href="../../employee/supervisor/attendance.php">Attendance Scanner</a>
                                <a class="nav-link text-light" href="">View Attendance Record</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLM" aria-expanded="false" aria-controls="collapseLM">
                            <div class="sb-nav-link-icon "><i class="fas fa-calendar-times"></i></div>
                            Leave Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../../employee/supervisor/leave_file.php">File Leave</a>
                                <a class="nav-link text-light" href="../../employee/supervisor/leave_request.php">Leave Request</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePM" aria-expanded="false" aria-controls="collapsePM">
                            <div class="sb-nav-link-icon"><i class="fas fa-line-chart"></i></div>
                            Performance Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../../employee/supervisor/evaluation.php">Evaluation Ratings</a>
                                <a class="nav-link text-light" href="../../employee/supervisor/evaluation.php">Evaluation</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSR" aria-expanded="false" aria-controls="collapseSR">
                            <div class="sb-nav-link-icon"><i class="fa fa-address-card"></i></div>
                            Social Recognition
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseSR" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="">View Your Rating</a>
                            </nav>
                        </div> 
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning mt-3">Feedback</div> 
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseFB" aria-expanded="false" aria-controls="collapseFB">
                            <div class="sb-nav-link-icon"><i class="fas fa-exclamation-circle"></i></div>
                            Report Issue
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseFB" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="">Report Issue</a>
                            </nav>
                        </div> 
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-black border-top border-1 border-warning">
                    <div class="small text-light">Logged in as: <?php echo htmlspecialchars($employeeInfo['role']); ?></div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid position-relative px-4">
                    <h1 class="mb-4 text-light">Dashboard</h1>
                    <div class="container" id="calendarContainer" 
                        style="position: fixed; top: 9%; right: 0; z-index: 1050; 
                        width: 700px; height: 300px; display: none;">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="calendar" class="p-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-3 mt-2 mb-2">
                            <div class="card bg-dark text-light border-0">
                                <div class="card-header border-bottom border-warning text-info">
                                    <h3>To Do</h3>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task1">
                                                <label class="form-check-label" for="task1">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Facial Recognition
                                                </label>
                                            </div>
                                        </li>
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task2">
                                                <label class="form-check-label" for="task2">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Attendance Record
                                                </label>
                                            </div>
                                        </li>
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task3">
                                                <label class="form-check-label" for="task3">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Leave Processing
                                                </label>
                                            </div>
                                        </li>
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task4">
                                                <label class="form-check-label" for="task4">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Performance Processing
                                                </label>
                                            </div>
                                        </li>
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task5">
                                                <label class="form-check-label" for="task5">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Payroll Processing
                                                </label>
                                            </div>
                                        </li>
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task6">
                                                <label class="form-check-label" for="task6">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Social Recognition
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2 mb-2">
                            <div class="card bg-dark text-light" style="height: 500px;">
                                <div class="card-header border-bottom border-1 border-warning text-info">
                                    <h3>Attendance</h3> <!-- Month and Year display -->
                                </div>
                                <div class="card-body p-4 overflow-auto" style="max-height: 400px;">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <h5 class="fw-bold">Today's Date:</h5>
                                            <p class="text-warning" id="todaysDate">January 18, 2025</p> <!-- fixed depends on the specific day -->
                                        </div>
                                        <div>
                                            <h5 class="fw-bold">Time in:</h5>
                                            <p class="text-warning">08:11 AM</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="mb-0">
                                        <h3 class="mb-0" id="monthYearDisplay"></h3>
                                        <div class="row text-center fw-bold">
                                            <div class="col">Sun</div>
                                            <div class="col">Mon</div>
                                            <div class="col">Tue</div>
                                            <div class="col">Wed</div>
                                            <div class="col">Thu</div>
                                            <div class="col">Fri</div>
                                            <div class="col">Sat</div>
                                        </div>

                                        <!-- Calendar rows with attendance status -->
                                        <div id="ATTENDANCEcalendar" class="pt-3 text-light bg-black"></div>
                                    </div>
                                </div>
                                <div class="card-footer text-center d-flex justify-content-around">
                                    <!-- Footer with Next and Previous buttons -->
                                    <button class="btn btn-primary" id="prevMonthBtn">&lt; Prev</button>
                                    <button class="btn btn-primary" id="nextMonthBtn">Next &gt;</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mt-2">
                            <div class="card bg-dark">
                                <div class="card-header border-bottom border-1 border-warning text-info">
                                    <h3>Performance Ratings | Graph</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Rating 1: Quality of Work -->
                                    <div class="mt-2">
                                        <h5 class="text-light">Quality of Work</h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning">Excellent</span>
                                            <span class="text-warning">85%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Rating 2: Communication Skills -->
                                    <div class="mt-2">
                                        <h5 class="text-light">Communication Skills</h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning">Good</span>
                                            <span class="text-warning">75%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Rating 3: Teamwork -->
                                    <div class="mt-2">
                                        <h5 class="text-light">Teamwork</h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning">Very Good</span>
                                            <span class="text-warning">80%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Rating 4: Punctuality -->
                                    <div class="mt-2">
                                        <h5 class="text-light">Punctuality</h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning">Average</span>
                                            <span class="text-warning">60%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 60%;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Rating 5: Initiative -->
                                    <div class="mt-2">
                                        <h5 class="text-light">Initiative</h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning">Excellent</span>
                                            <span class="text-warning">90%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 90%;" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12 mt-2 mb-2">
                            <div class="card bg-dark text-info border-0">
                                <div class="card-header border-bottom border-warning">
                                    <h3 class="mb-0">Top Performers | Graph</h3>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <!-- Performer 1 -->
                                        <li class="list-group-item bg-dark text-light d-flex align-items-center justify-content-between border-0">
                                            <div class="d-flex align-items-center">
                                                <img src="../../uploads/profile_pictures/try.jpg" alt="Performer 1" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h5 class="mb-0">John Doe</h5>
                                                    <small class="text-warning">Sales Manager</small>
                                                </div>
                                            </div>
                                            <div class="progress" style="width: 30%; height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 90%;" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </li>
                                        <!-- Performer 2 -->
                                        <li class="list-group-item bg-dark text-light d-flex align-items-center justify-content-between border-0">
                                            <div class="d-flex align-items-center">
                                                <img src="../../uploads/profile_pictures/pfp3.jpg" alt="Performer 2" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h5 class="mb-0">Jane Smith</h5>
                                                    <small class="text-warning">Marketing Specialist</small>
                                                </div>
                                            </div>
                                            <div class="progress" style="width: 30%; height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </li>
                                        <!-- Performer 3 -->
                                        <li class="list-group-item bg-dark text-light d-flex align-items-center justify-content-between border-0">
                                            <div class="d-flex align-items-center">
                                                <img src="../../uploads/profile_pictures/logo.jpg" alt="Performer 3" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h5 class="mb-0">Michael Johnson</h5>
                                                    <small class="text-warning">HR Manager</small>
                                                </div>
                                            </div>
                                            <div class="progress" style="width: 30%; height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
                <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark text-light">
                            <div class="modal-header">
                                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to log out?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn border-secondary text-light" data-bs-dismiss="modal">Cancel</button>
                                <form action="../../employee/logout.php" method="POST">
                                    <button type="submit" class="btn btn-danger">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="timeInfoModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark text-light">
                            <div class="modal-header">
                                <h5 class="modal-title" id="timeInfoModalLabel">January 1, 2025</h5>
                                <button type="button" class="btn-close bg-light" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex justify-content-around"> <!-- else if, (no data in workd days === absent)  else, (sunday or holiday === no data found)-->
                                    <div>
                                        <h6 class="fw-bold">Time In:</h6>
                                        <p class="text-success fw-bold">08:11 AM</p> <!-- Example time, can be dynamic -->
                                    </div>
                                    <div>
                                        <h6 class="fw-bold">Time Out:</h6>
                                        <p class="text-danger fw-bold">05:00 PM</p> <!-- Example time, can be dynamic -->
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <footer class="py-4 bg-light mt-auto bg-dark border-top border-1 border-warning">
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

<script>
    // for calendar only
    let calendar; // Declare calendar variable globally

    function toggleCalendar() {
        const calendarContainer = document.getElementById('calendarContainer');
        if (calendarContainer.style.display === 'none' || calendarContainer.style.display === '') {
            calendarContainer.style.display = 'block';

            // Initialize the calendar if it hasn't been initialized yet
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
            height: 440,  // Set the height of the calendar to make it small
            events: {
                url: '../../db/holiday.php',  // Endpoint for fetching events
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
        const currentDate = new Date().toLocaleDateString(); // Get the current date
        currentDateElement.textContent = currentDate; // Set the date text
    });

    document.addEventListener('click', function(event) {
        const calendarContainer = document.getElementById('calendarContainer');
        const calendarButton = document.querySelector('button[onclick="toggleCalendar()"]');

        if (!calendarContainer.contains(event.target) && !calendarButton.contains(event.target)) {
            calendarContainer.style.display = 'none';
        }
    });
    // for calendar only end

    function setCurrentTime() {
        const currentTimeElement = document.getElementById('currentTime');
        const currentDateElement = document.getElementById('currentDate');

        const currentDate = new Date();

        // Convert to 12-hour format with AM/PM
        let hours = currentDate.getHours();
        const minutes = currentDate.getMinutes();
        const seconds = currentDate.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12; // If hour is 0, set to 12

        const formattedHours = hours < 10 ? '0' + hours : hours;
        const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
        const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

        currentTimeElement.textContent = `${formattedHours}:${formattedMinutes}:${formattedSeconds} ${ampm}`;

        // Format the date in text form (e.g., "January 12, 2025")
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        currentDateElement.textContent = currentDate.toLocaleDateString('en-US', options);
    }

    setCurrentTime();
    setInterval(setCurrentTime, 1000);


const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
let currentMonth = new Date().getMonth(); // January is 0, December is 11
let currentYear = new Date().getFullYear();

// Function to render the calendar for a specific month and year
function renderCalendar(month, year) {
    const daysInMonth = new Date(year, month + 1, 0).getDate(); // Get total days in the current month
    const firstDay = new Date(year, month, 1).getDay(); // Get the starting day (0 = Sunday, 1 = Monday, etc.)

let attendanceRecords = {
    1: 'Present',
    2: 'Absent',
    3: 'Present',
    4: 'Present',
    5: 'Present',
    6: 'Present',
    7: 'Present',
    8: 'Present',
    9: 'Present',
    10: 'Absent',
    11: 'Present',
    12: 'Present',
    13: 'Present',
    14: 'Absent',
    15: 'Present',
    16: 'Absent',
    17: 'Present',
    18: 'Present',
    19: 'Present',
    20: 'Absent',
    21: 'Present',
    22: 'Present',
    23: 'Present',
    24: 'Absent',
    25: 'Present',
    26: 'Present',
    27: 'Absent',
    28: 'Present',
    29: 'Present',
    30: 'Present',
    31: 'Present'
};


let calendarHTML = '<div class="row text-center pt-3">';

// Add empty columns before the first day of the month
for (let i = 0; i < firstDay; i++) {
    calendarHTML += '<div class="col"></div>';
}

// Fill in the days of the month
let dayCounter = 1;
for (let i = firstDay; i < 7; i++) {
    const status = (i === 0) ? 'Day Off' : attendanceRecords[dayCounter] || ''; // Set "Day Off" for Sundays (day 0)
    
    // Wrap the day inside a button that triggers the modal
    calendarHTML += `
        <div class="col">
            <button class="btn text-light p-0" data-bs-toggle="modal" data-bs-target="#attendanceModal" onclick="(${dayCounter})">
                <span class="fw-bold">${dayCounter}</span>
                <div class="${status === 'Present' ? 'text-success' : status === 'Absent' ? 'text-danger' : status === 'No Data' ? 'text-warning' : status === 'Day Off' ? 'text-muted' : ''}">
                    ${status}
                </div>
            </button>
        </div>
    `;
    dayCounter++;
}
calendarHTML += '</div>';

// Continue filling rows for the remaining days
while (dayCounter <= daysInMonth) {
    calendarHTML += '<div class="row text-center pt-3">';
    let dayOfWeek = 0; // Reset for each row

    for (let i = 0; i < 7 && dayCounter <= daysInMonth; i++) {
        const status = (dayOfWeek === 0) ? 'Day Off' : attendanceRecords[dayCounter] || ''; 
        
        // Wrap the day inside a button that triggers the modal
        calendarHTML += `
            <div class="col">
                <button class="btn text-light p-0" data-bs-toggle="modal" data-bs-target="#attendanceModal" onclick="(${dayCounter})">
                    <span class="fw-bold">${dayCounter}</span>
                    <div class="${status === 'Present' ? 'text-success' : status === 'Absent' ? 'text-danger' : status === 'No Data' ? 'text-warning' : status === 'Day Off' ? 'text-muted' : ''}">
                        ${status}
                    </div>
                </button>
            </div>
        `;
        dayCounter++;
        dayOfWeek++;
    }

    // If the last row is not complete (less than 7 days), add empty columns
    if (dayOfWeek < 7) {
        for (let j = dayOfWeek; j < 7; j++) {
            calendarHTML += '<div class="col"></div>';
        }
    }

    calendarHTML += '</div>';
}

    // Update the calendar container with the new content
    document.getElementById('ATTENDANCEcalendar').innerHTML = calendarHTML;
    
    // Update the displayed month and year
    document.getElementById('monthYearDisplay').textContent = `${monthNames[month]} ${year}`;
    document.getElementById('todaysDate').textContent = `${monthNames[month]} ${new Date().getDate()}, ${year}`;
}

// Event listeners for next and previous month buttons
document.getElementById('nextMonthBtn').addEventListener('click', function() {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    renderCalendar(currentMonth, currentYear);
});

document.getElementById('prevMonthBtn').addEventListener('click', function() {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    renderCalendar(currentMonth, currentYear);
});

// Render the initial calendar for the current month and year
renderCalendar(currentMonth, currentYear);

</script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/employee.js"></script>



</body>

</html>