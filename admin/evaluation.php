<?php
session_start();

if (!isset($_SESSION['a_id'])) {
    header("Location: ../admin/login.php");
    exit();
}

include '../db/db_conn.php';

// Fetch the admin's ID
$adminId = $_SESSION['a_id'];

// Fetch the admin's info
$sql = "SELECT a_id, firstname, middlename, lastname, birthdate, email, role, department, phone_number, address, pfp FROM admin_register WHERE a_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$adminInfo = $result->fetch_assoc();

// Function to get evaluation progress by department for a specific admin
function getAdminEvaluationProgress($conn, $department, $adminId) {
    // Get total employees in the department
    $employeeQuery = "SELECT COUNT(*) as total FROM employee_register WHERE department = '$department'";
    $employeeResult = $conn->query($employeeQuery);
    $totalEmployees = $employeeResult->fetch_assoc()['total'];

    // Get total employees evaluated by the admin in the department
    $evaluatedQuery = "SELECT COUNT(*) as evaluated FROM admin_evaluations WHERE department = '$department' AND a_id = '$adminId'";
    $evaluatedResult = $conn->query($evaluatedQuery);
    $evaluated = $evaluatedResult->fetch_assoc()['evaluated'];

    $pendingEmployees = $totalEmployees - $evaluated;

    return array('total' => $totalEmployees, 'evaluated' => $evaluated, 'pending' => $pendingEmployees);
}

// Fetch data for different departments for the logged-in admin
$financeData = getAdminEvaluationProgress($conn, 'Finance Department', $adminId);
$hrData = getAdminEvaluationProgress($conn, 'Human Resource Department', $adminId);
$administrationData = getAdminEvaluationProgress($conn, 'Administration Department', $adminId);
$salesData = getAdminEvaluationProgress($conn, 'Sales Department', $adminId);
$creditData = getAdminEvaluationProgress($conn, 'Credit Department', $adminId);
$itData = getAdminEvaluationProgress($conn, 'IT Department', $adminId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Dashboard | HR2</title>
    <link href="../css/styles.css" rel="stylesheet" />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="sb-nav-fixed bg-black">
    <?php include 'navbar.php'; ?>
    <div id="layoutSidenav">
        <?php include 'sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main class="bg-black">
                <div class="container-fluid position-relative px-4">
                    <h1 class="mb-4 text-light">Evaluation</h1>
                </div>
                <div class="container" id="calendarContainer" 
                    style="position: fixed; top: 9%; right: 0; z-index: 1050; 
                    width: 700px; display: none;">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="calendar" class="p-2"></div>
                        </div>
                    </div>
                </div>     
                <div class="container-fluid px-4">
                    <div class="row justify-content-center">
                        <div class="col-xl-4 col-md-6 mt-5">
                            <div class="card mb-4">
                                <div class="card-body bg-secondary text-center">
                                    <a href="../admin/finance.php" class="btn card-button text-light font-weight-bold bg-dark border border-dark w-100">Finance Department</a>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between bg-dark border-bottom border-light department-toggle">
                                    <div class="small text-warning">Details</div>
                                </div>
                                <div id="financeInfo" class=" bg-dark text-dark">
                                    <div class="card-body">
                                        <h5 class="text-center mb-4 text-light">Finance Evaluation Status</h5>
                                        <div class="text-center mb-3">
                                            <span class="badge badge-primary mx-1">Total Employees: <?php echo $financeData['total']; ?></span>
                                            <span class="badge badge-success mx-1">Evaluated: <?php echo $financeData['evaluated']; ?></span>
                                            <span class="badge badge-warning mx-1">Pending: <?php echo $financeData['pending']; ?></span>
                                        </div>
                                        <div class="progress mb-2">
                                        <?php if ($financeData['total'] > 0): ?>
                                    <div class="progress-bar bg-success font-weight-bold" role="progressbar" 
                                        style="width: <?php echo ($financeData['evaluated'] / $financeData['total']) * 100; ?>%;" 
                                        aria-valuenow="<?php echo $financeData['evaluated']; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="<?php echo $financeData['total']; ?>">
                                        Evaluated (<?php echo $financeData['evaluated']; ?>)
                                    </div>
                                    <div class="progress-bar bg-warning text-dark font-weight-bold" role="progressbar" 
                                        style="width: <?php echo ($financeData['pending'] / $financeData['total']) * 100; ?>%;" 
                                        aria-valuenow="<?php echo $financeData['pending']; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="<?php echo $financeData['total']; ?>">
                                        Pending (<?php echo $financeData['pending']; ?>)
                                    </div>
                                <?php else: ?>
                                    <div class="progress-bar bg-secondary font-weight-bold w-100" role="progressbar" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        No employees available
                                    </div>
                                <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mt-5">
                            <div class="card mb-4">
                                <div class="card-body bg-secondary text-center">
                                    <a href="../admin/hr.php" class="btn card-button text-light font-weight-bold bg-dark border border-dark w-100">Human Resource Department</a>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between bg-dark border-bottom border-light department-toggle">
                                    <div class="small text-warning">Details</div>
                                </div>
                                <div id="hrInfo" class="bg-dark text-dark">
                                    <div class="card-body">
                                        <h5 class="text-center mb-4 text-light">Human Resource Evaluation Status</h5>
                                        <div class="text-center mb-3">
                                            <span class="badge badge-primary mx-1">Total Employees: <?php echo $hrData['total']; ?></span>
                                            <span class="badge badge-success mx-1">Evaluated: <?php echo $hrData['evaluated']; ?></span>
                                            <span class="badge badge-warning mx-1">Pending: <?php echo $hrData['pending']; ?></span>
                                        </div>
                                        <div class="progress mb-2">
                                        <?php if ($hrData['total'] > 0): ?>
                                    <div class="progress-bar bg-success font-weight-bold" role="progressbar" 
                                        style="width: <?php echo ($hrData['evaluated'] / $hrData['total']) * 100; ?>%;" 
                                        aria-valuenow="<?php echo $hrData['evaluated']; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="<?php echo $hrData['total']; ?>">
                                        Evaluated (<?php echo $hrData['evaluated']; ?>)
                                    </div>
                                    <div class="progress-bar bg-warning text-dark font-weight-bold" role="progressbar" 
                                        style="width: <?php echo ($hrData['pending'] / $hrData['total']) * 100; ?>%;" 
                                        aria-valuenow="<?php echo $hrData['pending']; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="<?php echo $hrData['total']; ?>">
                                        Pending (<?php echo $hrData['pending']; ?>)
                                    </div>
                                <?php else: ?>
                                    <div class="progress-bar bg-secondary font-weight-bold w-100" role="progressbar" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        No employees available
                                    </div>
                                <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mt-5">
                            <div class="card mb-4">
                                <div class="card-body bg-secondary text-center">
                                    <a href="../admin/administration.php" class="btn card-button text-light font-weight-bold bg-dark border border-dark w-100">Administration Department</a>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between bg-dark border-bottom border-light department-toggle">
                                    <div class="small text-warning">Details</div>
                                </div>
                                <div id="administrationInfo" class="bg-dark text-dark">
                                    <div class="card-body">
                                        <h5 class="text-center mb-4 text-light">Administration Evaluation Status</h5>
                                        <div class="text-center mb-3">
                                            <span class="badge badge-primary mx-1">Total Employees: <?php echo $administrationData['total']; ?></span>
                                            <span class="badge badge-success mx-1">Evaluated: <?php echo $administrationData['evaluated']; ?></span>
                                            <span class="badge badge-warning mx-1">Pending: <?php echo $administrationData['pending']; ?></span>
                                        </div>
                                        <div class="progress mb-2">
                                        <?php if ($administrationData['total'] > 0): ?>
                                    <div class="progress-bar bg-success font-weight-bold" role="progressbar" 
                                        style="width: <?php echo ($administrationData['evaluated'] / $administrationData['total']) * 100; ?>%;" 
                                        aria-valuenow="<?php echo $administrationData['evaluated']; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="<?php echo $administrationData['total']; ?>">
                                        Evaluated (<?php echo $administrationData['evaluated']; ?>)
                                    </div>
                                    <div class="progress-bar bg-warning text-dark font-weight-bold" role="progressbar" 
                                        style="width: <?php echo ($administrationData['pending'] / $administrationData['total']) * 100; ?>%;" 
                                        aria-valuenow="<?php echo $administrationData['pending']; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="<?php echo $administrationData['total']; ?>">
                                        Pending (<?php echo $administrationData['pending']; ?>)
                                    </div>
                                <?php else: ?>
                                    <div class="progress-bar bg-secondary font-weight-bold w-100" role="progressbar" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        No employees available
                                    </div>
                                <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mt-5">
                            <div class="card mb-4">
                                <div class="card-body bg-secondary text-center">
                                    <a href="../admin/sales.php" class="btn card-button text-light font-weight-bold bg-dark border border-dark w-100">Sales Department</a>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between bg-dark border-bottom border-light department-toggle">
                                    <div class="small text-warning">Details</div>
                                </div>
                                <div id="salesInfo" class="bg-dark text-dark">
                                    <div class="card-body">
                                        <h5 class="text-center mb-4 text-light">Sales Evaluation Status</h5>
                                        <div class="text-center mb-3">
                                            <span class="badge badge-primary mx-1">Total Employees: <?php echo $salesData['total']; ?></span>
                                            <span class="badge badge-success mx-1">Evaluated: <?php echo $salesData['evaluated']; ?></span>
                                            <span class="badge badge-warning mx-1">Pending: <?php echo $salesData['pending']; ?></span>
                                        </div>
                                        <div class="progress mb-2">
                                        <?php if ($salesData['total'] > 0): ?>
                                    <div class="progress-bar bg-success font-weight-bold" role="progressbar" 
                                        style="width: <?php echo ($salesData['evaluated'] / $salesData['total']) * 100; ?>%;" 
                                        aria-valuenow="<?php echo $salesData['evaluated']; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="<?php echo $salesData['total']; ?>">
                                        Evaluated (<?php echo $salesData['evaluated']; ?>)
                                    </div>
                                    <div class="progress-bar bg-warning text-dark font-weight-bold" role="progressbar" 
                                        style="width: <?php echo ($salesData['pending'] / $salesData['total']) * 100; ?>%;" 
                                        aria-valuenow="<?php echo $salesData['pending']; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="<?php echo $salesData['total']; ?>">
                                        Pending (<?php echo $salesData['pending']; ?>)
                                    </div>
                                <?php else: ?>
                                    <div class="progress-bar bg-secondary font-weight-bold w-100" role="progressbar" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        No employees available
                                    </div>
                                <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mt-5">
                            <div class="card mb-4">
                                <div class="card-body bg-secondary text-center">
                                    <a href="../admin/credit.php" class="btn card-button text-light font-weight-bold bg-dark border border-dark w-100">Credit Department</a>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between bg-dark border-bottom border-light department-toggle">
                                    <div class="small text-warning">Details</div>
                                </div>
                                <div id="creditInfo" class="bg-dark text-dark">
                                    <div class="card-body">
                                        <h5 class="text-center mb-4 text-light">Credit Evaluation Status</h5>
                                        <div class="text-center mb-3">
                                            <span class="badge badge-primary mx-1">Total Employees: <?php echo $creditData['total']; ?></span>
                                            <span class="badge badge-success mx-1">Evaluated: <?php echo $creditData['evaluated']; ?></span>
                                            <span class="badge badge-warning mx-1">Pending: <?php echo $creditData['pending']; ?></span>
                                        </div>
                                        <div class="progress mb-2">
                                        <?php if ($creditData['total'] > 0): ?>
                                    <div class="progress-bar bg-success font-weight-bold" role="progressbar" 
                                        style="width: <?php echo ($creditData['evaluated'] / $creditData['total']) * 100; ?>%;" 
                                        aria-valuenow="<?php echo $creditData['evaluated']; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="<?php echo $creditData['total']; ?>">
                                        Evaluated (<?php echo $creditData['evaluated']; ?>)
                                    </div>
                                    <div class="progress-bar bg-warning text-dark font-weight-bold" role="progressbar" 
                                        style="width: <?php echo ($creditData['pending'] / $creditData['total']) * 100; ?>%;" 
                                        aria-valuenow="<?php echo $creditData['pending']; ?>" 
                                        aria-valuemin="0" 
                                        aria-valuemax="<?php echo $creditData['total']; ?>">
                                        Pending (<?php echo $creditData['pending']; ?>)
                                    </div>
                                <?php else: ?>
                                    <div class="progress-bar bg-secondary font-weight-bold w-100" role="progressbar" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100">
                                        No employees available
                                    </div>
                                <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mt-5">
                            <div class="card mb-4">
                                <div class="card-body bg-secondary text-center">
                                    <a href="../admin/it.php" class="btn card-button text-light font-weight-bold bg-dark border border-dark w-100">IT Department</a>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between bg-dark border-bottom border-light department-toggle">
                                    <div class="small text-warning">Details</div>
                                </div>
                                <div id="itInfo" class="bg-dark text-dark">
                                    <div class="card-body">
                                        <h5 class="text-center mb-4 text-light">IT Evaluation Status</h5>
                                        <div class="text-center mb-3">
                                            <span class="badge badge-primary mx-1">Total Employees: <?php echo $itData['total']; ?></span>
                                            <span class="badge badge-success mx-1">Evaluated: <?php echo $itData['evaluated']; ?></span>
                                            <span class="badge badge-warning mx-1">Pending: <?php echo $itData['pending']; ?></span>
                                        </div>
                                        <div class="progress mb-2">
                                            <?php if ($itData['total'] > 0): ?>
                                                <div class="progress-bar bg-success font-weight-bold" role="progressbar" 
                                                    style="width: <?php echo ($itData['evaluated'] / $itData['total']) * 100; ?>%;" 
                                                    aria-valuenow="<?php echo $itData['evaluated']; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="<?php echo $itData['total']; ?>">
                                                    Evaluated (<?php echo $itData['evaluated']; ?>)
                                                </div>
                                                <div class="progress-bar bg-warning text-dark font-weight-bold" role="progressbar" 
                                                    style="width: <?php echo ($itData['pending'] / $itData['total']) * 100; ?>%;" 
                                                    aria-valuenow="<?php echo $itData['pending']; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="<?php echo $itData['total']; ?>">
                                                    Pending (<?php echo $itData['pending']; ?>)
                                                </div>
                                            <?php else: ?>
                                                <div class="progress-bar bg-secondary font-weight-bold w-100" role="progressbar" 
                                                    aria-valuenow="0" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    No employees available
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
                <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark text-light">
                            <div class="modal-header border-bottom border-secondary">
                                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to log out?
                            </div>
                            <div class="modal-footer border-top border-secondary">
                                <button type="button" class="btn border-secondary text-light" data-bs-dismiss="modal">Cancel</button>
                                <form action="../admin/logout.php" method="POST">
                                    <button type="submit" class="btn btn-danger">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>  
            <?php include 'footer.php'; ?>                                 
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

        //EVALUATION TOGGLE
            // Add event listener to all elements with class "department-toggle"
        document.querySelectorAll('.department-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const target = this.getAttribute('data-target');
                const icon = this.querySelector('i');

                // Toggle the collapse
                $(target).collapse('toggle');

                // Toggle the icon classes between angle-down and angle-up
                icon.classList.toggle('fa-angle-down');
                icon.classList.toggle('fa-angle-up');
            });
        });
        //EVALUATION TOGGLE END
</script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>
