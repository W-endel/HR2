<?php
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../employee/employeelogin.php");
    exit();
}

// Include the database connection  
include '../../db/db_conn.php'; 

$role = $_SESSION['role']; // Ensure this is set during login (e.g., supervisor, staff, admin, fieldworker, contractual)
$department = $_SESSION['department']; // Ensure this is set during login

// Fetch user info from the employee_register table
$employeeId = $_SESSION['employee_id'];
$sql = "SELECT employee_id, first_name, middle_name, last_name, birthdate, gender, email, role, position, department, phone_number, address, pfp 
        FROM employee_register 
        WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$employeeInfo = $result->fetch_assoc();

if (!$employeeInfo) {
    die("Error: Employee information not found.");
}

// Define the position
$position = 'employee'; // Position is now 'employee' only

// Fetch employee records where position is 'employee' and department matches the logged-in employee's department
$sql = "SELECT employee_id, first_name, last_name, role, position 
        FROM employee_register 
        WHERE position = ? AND department = ? AND role IN ('supervisor', 'fieldworker')";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $position, $department);  // Bind the parameters for position and department
$stmt->execute();
$result = $stmt->get_result();

// Fetch evaluations for this employee from the evaluations table, filtered by the current month and year based on evaluated_at
$evaluatedEmployees = [];
$currentMonth = date('m'); // Current month (1-12)
$currentYear = date('Y');  // Current year (e.g., 2024)
$evalSql = "SELECT employee_id FROM ptp_evaluations 
            WHERE evaluator_id = ? 
            AND MONTH(evaluated_at) = ? 
            AND YEAR(evaluated_at) = ?";
$evalStmt = $conn->prepare($evalSql);
$evalStmt->bind_param('sii', $employeeId, $currentMonth, $currentYear);
$evalStmt->execute();
$evalResult = $evalStmt->get_result();
if ($evalResult->num_rows > 0) {
    while ($row = $evalResult->fetch_assoc()) {
        $evaluatedEmployees[] = $row['employee_id']; // Store employee IDs who were evaluated
    }
}

// Fetch evaluation questions from the database for each category and role
$categories = ['Quality of Work', 'Communication Skills', 'Teamwork', 'Punctuality', 'Initiative'];
$questions = [];

foreach ($categories as $category) {
    // Fetch questions for the specific category and role
    $categorySql = "SELECT question FROM evaluation_questions WHERE category = ? AND role = ?";
    $categoryStmt = $conn->prepare($categorySql);
    $categoryStmt->bind_param('ss', $category, $role); // $role is the role being evaluated
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
    $questions[$category] = [];

    if ($categoryResult->num_rows > 0) {
        while ($row = $categoryResult->fetch_assoc()) {
            $questions[$category][] = $row['question'];
        }
    }
}

// Check if any records are found
$employees = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Exclude the logged-in employee from the list
        if ($row['employee_id'] != $employeeId) {
            $employees[] = $row;
        }
    }
}

// Calculate evaluation statistics
$totalEmployees = count($employees);
$evaluatedCount = count($evaluatedEmployees);
$pendingCount = $totalEmployees - $evaluatedCount;
$completionPercentage = $totalEmployees > 0 ? round(($evaluatedCount / $totalEmployees) * 100) : 0;

// Get the current month and year
$currentMonth = date('m'); // Current month (1-12)
$currentYear = date('Y');  // Current year (e.g., 2024)
$currentDay = date('j');   // Current day of the month (1-31)

// Define the evaluation deadline (e.g., 15th of the month)
$evaluationDeadline = 30;

// Check if the evaluation period is still open
$isFirstWeek = ($currentDay <= $evaluationDeadline); // Evaluations are open in the first 15 days of the month
if ($isFirstWeek) {
    // Evaluations are still open for the current month
    $evaluationPeriod = date('F Y'); // Format: March 2024
    $evaluationEndDate = date('F j, Y', strtotime(date('Y-m-' . $evaluationDeadline))); // Format: March 15, 2024
} else {
    // Evaluations are closed for the current month
    $evaluationPeriod = null;
    $evaluationEndDate = null;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Evaluation | HR2</title>
    <link href='../../css/styles.css' rel='stylesheet' />
    <link href='../../css/star.css' rel='stylesheet' />
    <link href='../../css/calendar.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-hover: #3a0ca3;
            --secondary: #4cc9f0;
            --success: #4ade80;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --bg-dark: rgba(33, 37, 41) !important;
            --card-bg: rgba(33, 37, 41) !important;
            --bg-black: rgba(16, 17, 18) !important;
            --text-light: #f8f9fa;
            --text-muted: #adb5bd;
            --border-color: #333;
            --gradient-start: #4361ee;
            --gradient-end: #3a0ca3;
        }
        
        body {
            background-color: var(--bg-black);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .navbar-dark {
            background-color: var(--card-bg) !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .evaluation-status {
            padding: 0.75rem 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .evaluation-status.active {
            background-color: var(--warning);
            color: #000;
        }
        
        /* Evaluation Notice */
        .evaluation-notice {
            background: rgba(33, 37, 41) !important;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .evaluation-notice.active {
            border-left: 4px solid var(--warning);
        }
        
        .notice-icon {
            font-size: 2rem;
        }
        
        .evaluation-notice.active .notice-icon {
            color: var(--warning);
        }
        
        /* Status Cards */
        .status-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .status-card {
            background: rgba(33, 37, 41) !important;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .status-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .status-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1;
        }
        
        .status-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .status-total .status-icon {
            color: var(--info);
        }
        
        .status-evaluated .status-icon {
            color: var(--success);
        }
        
        .status-pending .status-icon {
            color: var(--danger);
        }
        
        /* Progress Bar */
        .progress-container {
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }
        
        .progress {
            height: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .progress-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
            transition: width 0.6s ease;
        }
        
        /* Department Navigation */
        .department-nav {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 70px;
            z-index: 100;
        }
        
        .nav-tabs {
            border-bottom: none;
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) var(--dark-light);
            gap: 0.5rem;
            padding-bottom: 0.5rem;
        }
        
        .nav-tabs::-webkit-scrollbar {
            height: 5px;
        }
        
        .nav-tabs::-webkit-scrollbar-track {
            background: var(--dark-light);
            border-radius: 10px;
        }
        
        .nav-tabs::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 10px;
        }
        
        .nav-tabs .nav-link {
            color: var(--text-light);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            margin: 0 0.25rem;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }
        
        .nav-tabs .nav-link:hover {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }
        
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .department-icon {
            font-size: 1.2rem;
        }
        
        .badge-notification {
            background-color: var(--danger);
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
        }
        
        /* Employee Table */
        .employee-table-container {
            background: rgba(33, 37, 41) !important;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .employee-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .employee-search {
            position: relative;
            max-width: 300px;
        }
        
        .employee-search input {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            color: var(--text-light);
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .employee-search input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.3);
        }
        
        .employee-search i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        
        .table {
            color: var(--text-light);
            border-color: var(--border-color);
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: var(--border-color);
            color: var(--text-light);
            padding: 1rem;
            font-weight: 600;
        }
        
        .table tbody td {
            border-color: var(--border-color);
            padding: 1rem;
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .employee-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.75rem;
        }
        
        .badgeT {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .badge-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .badge-success {
            background: linear-gradient(135deg, #4ade80, #22c55e);
            color: white;
        }
        
        .btn-evaluate {
            min-width: 100px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--gradient-end), var(--gradient-start));
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-outline-success {
            color: var(--success);
            border-color: var(--success);
            background-color: transparent;
        }
        
        .btn-outline-success:hover {
            background-color: var(--success);
            color: white;
        }
        
        /* Modal Styling */
        .modal-content {
            background-color: var(--card-bg);
            border: none;
            border-radius: 12px;
        }
        
        .modal-header, .modal-footer {
            border-color: var(--border-color);
        }
        
        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        
        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .status-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .evaluation-status {
                align-self: flex-start;
            }
            
            .status-cards {
                grid-template-columns: 1fr;
            }
            
            .employee-table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .employee-search {
                width: 100%;
                max-width: none;
            }
            
            .nav-tabs .nav-link {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <?php include 'navbar.php'; ?>
    <div id="layoutSidenav">
        <?php include 'sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main class="text-light">
                <div class="container-fluid px-4">
                    <!-- Page Header -->
                    <div class="">
                        <h1 class="mb-5">Evaluation</h1>
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
                    
                    <!-- Evaluation Period Notice -->
                    <div class="evaluation-notice active">
                        <i class="fas fa-info-circle notice-icon"></i>
                        <div>
                            <h5 class="mb-1"><?php echo $isFirstWeek ? 'Evaluation Period Open' : 'Evaluations Closed'; ?></h5>
                            <p class="mb-0">
                                <?php echo $isFirstWeek 
                                    ? "Evaluation is open for $evaluationPeriod until $evaluationEndDate." 
                                    : "Evaluations are closed. They will open in the first 15 days of the next month."; 
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Status Cards -->
                    <div class="status-cards">
                        <div class="status-card status-total">
                            <i class="fas fa-users status-icon"></i>
                            <div class="status-value"><?php echo $totalEmployees; ?></div>
                            <div class="status-label">Total Employees</div>
                        </div>
                        <div class="status-card status-evaluated">
                            <i class="fas fa-check-circle status-icon"></i>
                            <div class="status-value"><?php echo $evaluatedCount; ?></div>
                            <div class="status-label">Evaluated</div>
                        </div>
                        <div class="status-card status-pending">
                            <i class="fas fa-clock status-icon"></i>
                            <div class="status-value"><?php echo $pendingCount; ?></div>
                            <div class="status-label">Pending</div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress-container bg-dark">
                        <h5 class="mb-3">Evaluation Progress</h5>
                        <?php if ($totalEmployees > 0 && $evaluationPeriod): ?>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" 
                                    style="width: <?php echo ($evaluatedCount / $totalEmployees) * 100; ?>%;" 
                                    aria-valuenow="<?php echo $evaluatedCount; ?>" 
                                    aria-valuemin="0" 
                                    aria-valuemax="<?php echo $totalEmployees; ?>">
                                    <?php echo $evaluatedCount; ?> Evaluated
                                </div>
                                <div class="progress-bar bg-danger" role="progressbar" 
                                    style="width: <?php echo ($pendingCount / $totalEmployees) * 100; ?>%;" 
                                    aria-valuenow="<?php echo $pendingCount; ?>" 
                                    aria-valuemin="0" 
                                    aria-valuemax="<?php echo $totalEmployees; ?>">
                                    <?php echo $pendingCount; ?> Pending
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-success"><?php echo round(($evaluatedCount / $totalEmployees) * 100); ?>% Complete</small>
                                <small class="text-danger"><?php echo round(($pendingCount / $totalEmployees) * 100); ?>% Pending</small>
                            </div>
                        <?php else: ?>
                            <div class="progress">
                                <div class="progress-bar bg-secondary w-100" role="progressbar">
                                    <?php echo $evaluationPeriod ? 'No evaluations yet' : 'Evaluations closed'; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Employee Table -->
                    <div class="employee-table-container">
                        <div class="employee-table-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user-check me-2"></i>
                                <?php echo htmlspecialchars($department); ?>
                            </h5>
                            <div class="employee-search">
                                <input type="text" placeholder="Search employees..." onkeyup="searchEmployees(this)">
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover" id="employees-table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Position</th>
                                        <th>Role</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($employees)): ?>
                                        <?php foreach ($employees as $employee): ?>
                                            <tr class="employee-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="employee-avatar">
                                                            <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                                                        </div>
                                                        <span class="employee-name"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="employee-position"><?php echo htmlspecialchars($employee['role']); ?></td>
                                                <td class="employee-role"><?php echo htmlspecialchars($employee['position']); ?></td>
                                                <td class="text-center">
                                                    <?php if (in_array($employee['employee_id'], $evaluatedEmployees)): ?>
                                                        <span class="badgeT badge-success">
                                                            <i class="fas fa-check-circle me-1"></i> Evaluated
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badgeT badge-danger">
                                                            <i class="fas fa-clock me-1"></i> Pending
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm <?php echo in_array($employee['employee_id'], $evaluatedEmployees) ? 'btn-outline-success' : 'btn-primary'; ?> btn-evaluate" 
                                                        onclick="evaluateEmployee(
                                                            <?php echo $employee['employee_id']; ?>, 
                                                            '<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>', 
                                                            '<?php echo htmlspecialchars($employee['position']); ?>'
                                                        )"
                                                        <?php echo in_array($employee['employee_id'], $evaluatedEmployees) || !$evaluationPeriod ? 'disabled' : ''; ?>>
                                                        <?php echo in_array($employee['employee_id'], $evaluatedEmployees) ? '<i class="fas fa-check me-1"></i> Done' : '<i class="fas fa-star me-1"></i> Evaluate'; ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <!-- Empty state row (hidden by default) -->
                                        <tr id="no-results-row" style="display: none;">
                                            <td colspan="5" class="text-center">No employees found matching your search.</td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No employees found for evaluation in <?php echo htmlspecialchars($department); ?>.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Evaluation Modal -->
                <div class="modal fade" id="evaluationModal" tabindex="-1" aria-labelledby="evaluationModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-light" id="evaluationModalLabel">Employee Evaluation</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="employeeDetails"></div>
                                <input type="hidden" id="employee_id" value="<?php echo $_SESSION['employee_id']; ?>">
                                <div id="questions"></div>
                                
                                <div class="progress-container bg-black">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-light">Evaluation Progress</span>
                                        <span id="progressPercentage" class="text-light">0%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" id="evaluationProgress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="submitEvaluation()">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Evaluation
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Toast Container for Notifications -->
                <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
            </main>
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script>
        let currentEmployeeId;
        let currentEmployeeName;
        let currentEmployeePosition;
        let currentEmployeeDepartment;
        let totalQuestions = 0;
        let answeredQuestions = 0;

        // The categories and questions fetched from the PHP script
        const questions = <?php echo json_encode($questions); ?>;
        const categoryIcons = {
            'Quality of Work': 'fa-award',
            'Communication Skills': 'fa-comments',
            'Teamwork': 'fa-people-group',
            'Punctuality': 'fa-clock',
            'Initiative': 'fa-bolt'
        };

        // Function to fetch questions based on the evaluated employee's position
        async function evaluateEmployee(employee_id, employeeName, employeePosition, employeeDepartment) {
            currentEmployeeId = employee_id;
            currentEmployeeName = employeeName;
            currentEmployeePosition = employeePosition;
            currentEmployeeDepartment = employeeDepartment;

            // Reset progress tracking
            totalQuestions = 0;
            answeredQuestions = 0;
            updateProgress();

            // Show loading state
            document.getElementById('employeeDetails').innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm text-primary me-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span>Loading evaluation form...</span>
                </div>
            `;
            
            const evaluationModal = new bootstrap.Modal(document.getElementById('evaluationModal'));
            evaluationModal.show();

            const employeeDetails = `
                <div class="d-flex align-items-center mb-4">
                    <div class="employee-avatar me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                        ${employeeName.split(' ')[0][0]}${employeeName.split(' ')[1][0]}
                    </div>
                    <div>
                        <h5 class="mb-1 text-light">${employeeName}</h5>
                        <p class="mb-0 text-muted">${employeePosition} - ${employeeDepartment}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('employeeDetails').innerHTML = employeeDetails;

            const questionsDiv = document.getElementById('questions');
            questionsDiv.innerHTML = ''; 

            // Start the table structure
            let tableHtml = `
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                Please rate the employee on a scale of 1-6 for each question (6 being the highest).
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="20%">Category</th>
                        <th width="50%">Question</th>
                        <th width="30%">Rating</th>
                    </tr>
                </thead>
                <tbody>`;

            // Loop through categories and questions to add them into the table
            for (const [category, categoryQuestions] of Object.entries(questions)) {
                totalQuestions += categoryQuestions.length;
                
                categoryQuestions.forEach((question, index) => {
                    const questionName = `${category.replace(/\s/g, '')}q${index}`; // Unique name per question
                    tableHtml += `
                    <tr>
                        <td class="align-middle">${index === 0 ? `<strong>${category}</strong>` : ''}</td>
                        <td class="align-middle">${question}</td>
                        <td>
                            <div class="star-rating">
                                ${[6, 5, 4, 3, 2, 1].map(value => `
                                    <input type="radio" name="${questionName}" value="${value}" id="${questionName}star${value}" onchange="updateQuestionStatus(this)">
                                    <label for="${questionName}star${value}" title="${value} stars">&#9733;</label>
                                `).join('')}
                            </div>
                        </td>
                    </tr>`;
                });
            }

            // Close the table structure
            tableHtml += `
                </tbody>
            </table>`;

            questionsDiv.innerHTML = tableHtml;
        }

        function updateQuestionStatus(input) {
            if (input.checked) {
                answeredQuestions++;
                updateProgress();
            }
        }

        function updateProgress() {
            const percentage = totalQuestions > 0 ? Math.round((answeredQuestions / totalQuestions) * 100) : 0;

            // Update progress bar in modal
            document.getElementById('evaluationProgress').style.width = `${percentage}%`;
            document.getElementById('progressPercentage').textContent = `${percentage}%`;
        }

        function submitEvaluation() {
            const evaluations = [];
            const questionsDiv = document.getElementById('questions');

            questionsDiv.querySelectorAll('input[type="radio"]:checked').forEach(input => {
                evaluations.push({
                    question: input.name,  
                    rating: input.value    
                });
            });

            const totalQuestions = questionsDiv.querySelectorAll('.star-rating').length;

            if (evaluations.length !== totalQuestions) {
                showToast('Warning', 'Please complete the evaluation before submitting.', 'warning');
                return;
            }

            const categoryAverages = {
                QualityOfWork: calculateAverage('QualityofWork', evaluations),
                CommunicationSkills: calculateAverage('CommunicationSkills', evaluations),
                Teamwork: calculateAverage('Teamwork', evaluations),
                Punctuality: calculateAverage('Punctuality', evaluations),
                Initiative: calculateAverage('Initiative', evaluations)
            };

            const employeeId = document.getElementById('employee_id').value;
            const department = '<?php echo $department; ?>';

            // Show loading indicator
            const submitBtn = document.querySelector('#evaluationModal .btn-primary');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Submitting...';
            submitBtn.disabled = true;

            $.ajax({
                type: 'POST',
                url: '../../employee_db/fieldworker/submit_evaluation.php',
                data: {
                    employee_id: currentEmployeeId,
                    employeeName: currentEmployeeName,
                    employeePosition: currentEmployeePosition,
                    categoryAverages: categoryAverages,
                    employeeId: employeeId,
                    department: department
                },
                success: function (response) {
                    // Hide modal
                    const evaluationModal = bootstrap.Modal.getInstance(document.getElementById('evaluationModal'));
                    evaluationModal.hide();
                    
                    // Show success message
                    if (response === 'You have already evaluated this employee.') {
                        showToast('Warning', response, 'warning');
                    } else {
                        showToast('Success', 'Evaluation submitted successfully!', 'success');
                        
                        // Refresh the page after a short delay
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }
                },
                error: function (err) {
                    console.error(err);
                    showToast('Error', 'An error occurred while submitting the evaluation.', 'danger');
                    
                    // Reset button
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                }
            });
        }

        function calculateAverage(category, evaluations) {
            const categoryEvaluations = evaluations.filter(evaluation => evaluation.question.startsWith(category));

            if (categoryEvaluations.length === 0) {
                return 0; 
            }

            const total = categoryEvaluations.reduce((sum, evaluation) => sum + parseInt(evaluation.rating), 0);
            return total / categoryEvaluations.length;
        }
        
        function showToast(title, message, type) {
            // Create toast container if it doesn't exist
            const toastContainer = document.querySelector('.toast-container');
            
            // Create toast
            const toastId = 'toast-' + Date.now();
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.id = toastId;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}:</strong> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            // Initialize and show toast
            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: 5000
            });
            bsToast.show();
            
            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', function () {
                toast.remove();
            });
        }
        
        // Search functionality for employee tables
        function searchEmployees(input) {
            const filter = input.value.toUpperCase();
            const table = document.getElementById('employees-table');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header row
                const nameCell = rows[i].getElementsByTagName('td')[0];
                const positionCell = rows[i].getElementsByTagName('td')[1];
                const roleCell = rows[i].getElementsByTagName('td')[2];
                
                if (nameCell && positionCell && roleCell) {
                    const nameText = nameCell.textContent || nameCell.innerText;
                    const positionText = positionCell.textContent || positionCell.innerText;
                    const roleText = roleCell.textContent || roleCell.innerText;
                    
                    if (nameText.toUpperCase().indexOf(filter) > -1 || 
                        positionText.toUpperCase().indexOf(filter) > -1 || 
                        roleText.toUpperCase().indexOf(filter) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        }


        function searchEmployees(input) {
            const searchTerm = input.value.toLowerCase();
            const rows = document.querySelectorAll('.employee-row');
            let hasVisibleRows = false;

            rows.forEach(row => {
                const name = row.querySelector('.employee-name').textContent.toLowerCase();
                const position = row.querySelector('.employee-position').textContent.toLowerCase();
                const role = row.querySelector('.employee-role').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || position.includes(searchTerm) || role.includes(searchTerm)) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide the "no results" message
            const noResultsRow = document.getElementById('no-results-row');
            if (rows.length > 0) {
                noResultsRow.style.display = hasVisibleRows ? 'none' : '';
            }
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/employee.js"></script>
</body>
</html>

