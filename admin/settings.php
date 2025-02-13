<?php
session_start();

if (!isset($_SESSION['a_id'])) {
    header("Location: ../admin/login.php");
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
$sql = "SELECT lr.leave_id, e.e_id, e.firstname, e.lastname, e.department, e.available_leaves, lr.start_date, lr.end_date, lr.leave_type, lr.proof, lr.status, lr.created_at
        FROM leave_requests lr
        JOIN employee_register e ON lr.e_id = e.e_id
        WHERE lr.supervisor_approval = 'Supervisor Approved' AND lr.status = 'Supervisor Approved' ORDER BY created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();


// Handle adding, editing, or deleting questions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_question'])) {
        $category = $_POST['category'];
        $question = $_POST['question'];

        $sql = "INSERT INTO evaluation_questions (category, question) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $category, $question);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['edit_question'])) {
        $id = $_POST['id'];
        $new_question = $_POST['new_question'];

        $sql = "UPDATE evaluation_questions SET question = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_question, $id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['delete_question'])) {
        $id = $_POST['id'];

        $sql = "DELETE FROM evaluation_questions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all questions and categories
$sql = "SELECT * FROM evaluation_questions ORDER BY category, id";
$result = $conn->query($sql);

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="User Profile Dashboard" />
    <meta name="author" content="Your Name" />
    <title>Settings</title>
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
                                <a class="nav-link text-light" href="../admin/admin.php">admin Accounts</a>
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
                    <div class="container-fluid position-relative px-4">
                        <div class="container-fluid" id="calendarContainer" 
                            style="position: fixed; top: 9%; right: 0; z-index: 1050; 
                            max-width: 100%; display: none;">
                            <div class="row">
                                <div class="col-12 col-md-10 col-lg-8 mx-auto">
                                    <div id="calendar" class="p-2"></div>
                                </div>
                            </div>
                        </div>
                        <h1 class="big mb-2 text-light">Admin Settings</h1>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <div class="card-header bg-dark text-light">
                                        <h3 class="card-title text-start">Time and attendance</h3>
                                        <hr>
                                    </div>
                                    <div class="card-body bg-dark">
                                        <div class="row">
                                            <div class="col-xl-6">
                                                <form method="POST" action="../db/set_leave.php">
                                                    <!-- Leave Days Input -->
                                                    <div class="form-group mb-3">
                                                        <label for="employee_leaves" class="form-label text-light">Leave Days for Employees:</label>
                                                        <input type="number" name="employee_leaves" id="employee_leaves" class="form-control" required>
                                                    </div>

                                                    <!-- Employee Selection -->
                                                    <div class="form-group mb-3">
                                                        <label for="employee_id" class="form-label text-light">Select Employee:</label>
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
                                                    <div class="text-start">
                                                        <button type="submit" class="btn btn-primary mt-3">Set Allocations</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <div class="card-header bg-dark text-light">
                                        <h3 class="card-title text-start">Leave Management</h3>
                                        <hr>
                                    </div>
                                    <div class="card-body bg-dark">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <form method="POST" action="../db/set_leave.php">
                                                    <div class="row">
                                                        <!-- Add Leave Credit Input -->
                                                        <div class="col-sm-6">
                                                            <div class="form-group mb-3 position-relative">
                                                                <label for="employee_leaves" class="fw-bold position-absolute text-light" 
                                                                    style="top: -10px; left: 15px; background-color: RGBA(33, 37, 41); padding: 0 5px;">Add Leave Credit:</label>
                                                                <input type="number" name="employee_leaves" id="employee_leaves" 
                                                                    class="form-control bg-dark border border-2 border-secondary text-light" 
                                                                    min="1" max="20" required 
                                                                    style="height: 55px; padding-top: 15px; padding-bottom: 15px;">
                                                                <small class="form-text text-warning">Enter a value between 1 and 20.</small>
                                                            </div>
                                                        </div>

                                                        <!-- Employee Selection Dropdown -->
                                                        <div class="col-sm-6">
                                                            <div class="form-group mb-3 position-relative">
                                                                <label for="employee_id" class="fw-bold position-absolute text-light" 
                                                                    style="top: -10px; left: 15px; background-color: #212529; padding: 0 5px;">Select Employee:</label>
                                                                <select name="employee_id" id="employee_id" class="form-control form-select bg-dark border border-2 border-secondary text-light" 
                                                                    style="height: 55px; padding-top: 15px; padding-bottom: 15px;" required>
                                                                    <option value="" disabled selected>Select Employee</option>
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
                                                        </div>
                                                    </div>
                                                    <div class="text-start">
                                                        <button type="submit" class="btn btn-primary mt-3">Set Allocations</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <div class="card bg-dark text-light">
                                    <div class="card-header">
                                        <h3 class="mb-0">Performance Management</h3>
                                        <hr>
                                    </div>
                                    <div class="card-body">
                                        <div class="row d-flex justify-content-around">
                                            <div class="col-xl-7 rounded">
                                                <h2 class="text-center text-light mb-4">Manage Evaluation Questions</h2>

                                                <!-- Add New Question Form -->
                                                <div class="mb-4">
                                                    <h4>Add New Question</h4>
                                                    <form method="POST" action="../admin/manageQuestions.php">
                                                        <div class="form-group mt-3 mb-3 position-relative">
                                                        <label for="category" class="fw-bold position-absolute text-light" 
                                                            style="top: -10px; left: 15px; background-color: #212529; padding: 0 5px;">Category:</label>
                                                            <select name="category" class="form-control bg-dark form-select border border-2 border-secondary text-light" 
                                                                style="height: 55px; padding-top: 15px; padding-bottom: 15px;" required>
                                                                <option value="Quality of Work">Quality of Work</option>
                                                                <option value="Communication Skills">Communication Skills</option>
                                                                <option value="Teamwork">Teamwork</option>
                                                                <option value="Punctuality">Punctuality</option>
                                                                <option value="Initiative">Initiative</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group mt-4 mb-3 position-relative">
                                                        <label for="category" class="fw-bold position-absolute text-light" 
                                                        style="top: -10px; left: 15px; background-color: #212529; padding: 0 5px;">Question:</label>
                                                            <textarea name="question" class="form-control bg-dark border border-2 border-secondary text-light" 
                                                            style="height: 100px; padding-top: 15px; padding-bottom: 15px;" rows="3" required></textarea>
                                                        </div>
                                                        <button type="submit" name="add_question" class="btn btn-primary mt-1">Add Question</button>
                                                    </form>
                                                </div>

                                                <!-- Questions Accordion -->
                                                <h4>Current Questions</h4>
                                                <h5>(Click the category to see the questions)</h5>
                                                <div class="accordion" id="questionAccordion">
                                                    <?php if (!empty($questions)): ?>
                                                        <?php 
                                                        // Group questions by category
                                                        $categories = [];
                                                        foreach ($questions as $question) {
                                                            $categories[$question['category']][] = $question;
                                                        }
                                                        ?>

                                                        <?php foreach ($categories as $category => $questionsList): ?>
                                                        <?php 
                                                            // Sanitize category names for use in id and data-target
                                                            $categoryId = str_replace(' ', '_', $category); 
                                                        ?>
                                                        <div class="accordion-item ">
                                                            <h2 class="accordion-header" id="heading-<?php echo htmlspecialchars($categoryId); ?>">
                                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo htmlspecialchars($categoryId); ?>" aria-expanded="false" aria-controls="collapse-<?php echo htmlspecialchars($categoryId); ?>">
                                                                    <?php echo htmlspecialchars($category); ?>
                                                                </button>
                                                            </h2>
                                                            <div id="collapse-<?php echo htmlspecialchars($categoryId); ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo htmlspecialchars($categoryId); ?>" data-bs-parent="#questionAccordion">
                                                                <div class="accordion-body">
                                                                    <ul class="list-group">
                                                                        <?php foreach ($questionsList as $question): ?>
                                                                        <li class="list-group-item bg-dark text-light">
                                                                            <span><?php echo htmlspecialchars($question['question']); ?></span>
                                                                            <div class="mt-2">
                                                                                <!-- Edit Button -->
                                                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editQuestionModal" 
                                                                                    data-qid="<?php echo $question['id']; ?>"
                                                                                    data-question="<?php echo htmlspecialchars($question['question']); ?>">Edit</button>
                                                                                
                                                                                <!-- Delete Form -->
                                                                                <form method="POST" action="../admin/manageQuestions.php" class="d-inline">
                                                                                    <input type="hidden" name="id" value="<?php echo $question['id']; ?>">
                                                                                    <button type="submit" name="delete_question" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this question?')">Delete</button>
                                                                                </form>
                                                                            </div>
                                                                        </li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <div class="text-center text-light">No questions found.</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 rounded">
                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                    <div class="modal fade" id="editQuestionModal" tabindex="-1" role="dialog" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="editQuestionModalLabel">Edit Question</h5>
                                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="manage_questions.php">
                                        <input type="hidden" name="id" id="editQId">
                                        <div class="form-group">
                                            <label for="new_question">New Question:</label>
                                            <textarea name="new_question" id="editNewQuestion" class="form-control" rows="3" required></textarea>
                                        </div>
                                        <button type="submit" name="edit_question" class="btn btn-primary mt-3">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
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
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
        <script src="../js/admin.js"></script>
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
                //TIME END

                //EVALUATION QUESTIONS
                // Populate the edit modal with question data
                $('#editQuestionModal').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget); // Button that triggered the modal
                    var qid = button.data('qid'); // Extract info from data-* attributes
                    var question = button.data('question'); // Extract the question

                    var modal = $(this);
                    modal.find('#editQId').val(qid); // Insert question ID into the modal's input
                    modal.find('#editNewQuestion').val(question); // Insert question into the modal's textarea
                });
        </script>
    </body>
</html>