<?php
session_start();

if (!isset($_SESSION['a_id'])) {
    header("Location: ../admin/login.php");
    exit();
}

include '../db/db_conn.php';

if (isset($_SESSION['update_success'])) {
    echo '<script>alert("' . htmlspecialchars($_SESSION['update_success']) . '");</script>';
    unset($_SESSION['update_success']);
}

// Fetch user info
$adminId = $_SESSION['a_id'];
$sql = "SELECT a_id, firstname, middlename, lastname, birthdate, email, role, position, department, phone_number, address, pfp FROM admin_register WHERE a_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$adminInfo = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="User Profile Dashboard" />
    <meta name="author" content="Your Name" />
    <title>User Profile - Dashboard</title>
    <link href="../css/styles.css" rel="stylesheet" />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="sb-nav-fixed bg-black">
    <nav class="sb-topnav navbar navbar-expand navbar-dark border-bottom border-1 border-warning bg-dark">
        <a class="navbar-brand ps-3 text-muted" href="../admin/dashboard.php">Microfinance</a>
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
                                    <li><a class="dropdown-item" href="../admin/logout.php" onclick="confirmLogout(event)">Logout</a></li>
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
        <main class="bg-black">
            <div class="container" id="calendarContainer" 
                style="position: fixed; top: 9%; right: 0; z-index: 1050; 
                width: 700px; display: none;">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="calendar" class="p-2"></div>
                        </div>
                    </div>
            </div>                               
            <div class="container-fluid px-4 bg-black">
                <h1 class="big text-light mb-4">Your Profile</h1>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-dark border-bottom border-1 border-warning">
                                <h5 class="card-title text-center text-light">Profile Picture</h5>
                            </div>
                            <div class="card-body text-center bg-dark">
                                 <img src="<?php echo (!empty($adminInfo['pfp']) && $adminInfo['pfp'] !== 'defaultpfp.png') 
                                    ? htmlspecialchars($adminInfo['pfp']) 
                                    : '../img/defaultpfp.jpg'; ?>" 
                                    class="rounded-circle border border-light" width="200" height="200" alt="Profile Picture" />

                                    <button class="btn btn-outline-light" type="button" id="editPictureDropdown" 
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                
                                <!-- Dropdown for editing the profile picture -->
                                <div class="dropdown mt-3">
                                    <ul class="dropdown-menu" aria-labelledby="editPictureDropdown">
                                        <li>
                                            <!-- Trigger file input for changing the profile picture -->
                                            <a class="dropdown-item" href="javascript:void(0);" id="changePictureOption">Change Profile Picture</a>
                                        </li>
                                        <li>
                                            <!-- Form to handle profile picture deletion -->
                                        <form action="../db/delete_admin_pfp.php" method="post">
                                            <!-- Hidden input to send the admin_id to the backend -->
                                            <input type="hidden" name="adminId" value="<?php echo $adminInfo['a_id']; ?>">
                                            
                                            <button class="dropdown-item" type="submit" onclick="return confirm('Are you sure you want to delete your profile picture?');">
                                                Delete Profile Picture
                                            </button>
                                        </form>
                                        </li>
                                    </ul>
                                </div>
                                
                                <!-- Hidden file input to upload a new profile picture -->
                                <form action="../db/update_admin_pfp.php" method="post" enctype="multipart/form-data" id="profilePictureForm" style="display:none;">
                                    <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" onchange="document.getElementById('profilePictureForm').submit();">
                                </form>

                                <table class="table text-light mt-3 text-start">
                                    <tr>
                                        <td>Name:</td>
                                        <td><?php echo htmlspecialchars($adminInfo['firstname'] . ' ' . $adminInfo['middlename'] . ' ' . $adminInfo['lastname']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>ID:</td>
                                        <td>#<?php echo htmlspecialchars($adminInfo['a_id']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Role:</td>
                                        <td><?php echo htmlspecialchars($adminInfo['role']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Position:</td>
                                        <td><?php echo htmlspecialchars($adminInfo['position']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Department:</td>
                                        <td><?php echo htmlspecialchars($adminInfo['department']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Email:</td>
                                        <td><?php echo htmlspecialchars($adminInfo['email']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card mb-2">
                            <div class="card-header bg-dark border-bottom border-1 border-warning">
                                <h5 class="card-title text-center text-light">Your Information</h5>
                            </div>
                            <div class="card-body bg-dark">
                                <form id="infoForm" action="../db/update_profile.php" method="post">
                                    <div class="row mb-3">
                                        <label for="inputfName" class="col-sm-2 col-form-label text-light">First Name:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control bg-dark text-light border border-light" id="inputfName" name="firstname" value="<?php echo htmlspecialchars($adminInfo['firstname']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="inputmName" class="col-sm-2 col-form-label text-light">Middle Name:</label>
                                        <div class="col-sm-9"> 
                                            <input type="text" class="form-control bg-dark text-light border border-light" id="inputmName" name="middlename" value="<?php echo htmlspecialchars($adminInfo['middlename']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="inputlName" class="col-sm-2 col-form-label text-light">Last Name:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control bg-dark text-light border border-light" id="inputlName" name="lastname" value="<?php echo htmlspecialchars($adminInfo['lastname']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="inputbirth" class="col-sm-2 col-form-label text-light">Birthdate:</label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control bg-dark text-light border border-light" id="inputbirth" name="birthdate" value="<?php echo htmlspecialchars($adminInfo['birthdate']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="inputEmail" class="col-sm-2 col-form-label text-light">Email Address:</label>
                                        <div class="col-sm-9">
                                            <input type="email" class="form-control bg-dark text-light border border-light" id="inputEmail" name="email" value="<?php echo htmlspecialchars($adminInfo['email']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="inputPhone" class="col-sm-2 col-form-label text-light">Phone Number:</label>
                                        <div class="col-sm-9">
                                            <input type="number" class="form-control bg-dark text-light border border-light" id="inputPhone" name="phone_number" value="<?php echo htmlspecialchars($adminInfo['phone_number']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="inputAddress" class="col-sm-2 col-form-label text-light">Address:</label>
                                        <div class="mb-3 col-sm-9">
                                            <textarea class="form-control border bg-dark text-light border-light" id="inputAddress" name="address" rows="1" readonly><?php echo htmlspecialchars($adminInfo['address']); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-primary border border-light d-none w-30">Save Changes</button>
                                        <button type="button" id="editButton" class="btn btn-primary w-30">Update Information</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <a href="../admin/change_pass.php" class="btn btn-primary mt-4">Change Password</a>
                    </div>
                </div>
            </div>
        </main>
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

        // Trigger file input when user clicks on "Change Profile Picture"
document.getElementById('changePictureOption').addEventListener('click', function() {
    document.getElementById('profilePictureInput').click();
});
</script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/admin.js"></script>
    <script src="../js/profile.js"></script>
</body>
</html>
