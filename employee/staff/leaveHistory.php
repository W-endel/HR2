<?php
// Start the session
session_start();

// Include database connection
include '../../db/db_conn.php';

// Ensure session variable is set
if (!isset($_SESSION['employee_id'])) {
    die("Error: Employee ID is not set in the session.");
}

// Fetch user info
$employeeId = $_SESSION['employee_id'];

// Correct SQL query
$sql = "SELECT employee_id, first_name, middle_name, last_name, role, position, department, phone_number
        FROM employee_register WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$employeeInfo = $result->fetch_assoc();

// Pagination variables
$recordsPerPage = 10;
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Query for leave requests specific to the logged-in employee
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';
$fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : '';
$toDate = isset($_GET['toDate']) ? $_GET['toDate'] : '';
$statusFilter = isset($_GET['statusFilter']) ? $_GET['statusFilter'] : '';
$timeFrame = isset($_GET['timeFrame']) ? $_GET['timeFrame'] : '';
$specificMonth = isset($_GET['specificMonth']) ? $_GET['specificMonth'] : '';
$specificYear = isset($_GET['specificYear']) ? $_GET['specificYear'] : '';

// Adjust SQL query to always show the latest history at the top
$sql = "
    SELECT lr.*, e.first_name, e.last_name, s.first_name AS supervisor_first_name, s.last_name AS supervisor_last_name
    FROM leave_requests lr
    JOIN employee_register e ON lr.employee_id = e.employee_id
    LEFT JOIN employee_register s ON lr.supervisor_id = s.employee_id
    WHERE lr.employee_id = ?";

if ($searchTerm) {
    $sql .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR lr.employee_id LIKE ?)";
}
if ($fromDate) {
    $sql .= " AND lr.start_date >= ?";
}
if ($toDate) {
    $sql .= " AND lr.end_date <= ?";
}
if ($statusFilter) {
    $sql .= " AND lr.status = ?";
}
if ($timeFrame) {
    if ($timeFrame == 'day') {
        $sql .= " AND lr.created_at >= CURDATE()";
    } elseif ($timeFrame == 'week') {
        $sql .= " AND lr.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
    } elseif ($timeFrame == 'month') {
        $sql .= " AND lr.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
    } elseif ($timeFrame == 'specific' && $specificMonth && $specificYear) {
        $sql .= " AND MONTH(lr.created_at) = ? AND YEAR(lr.created_at) = ?";
    }
}

$sql .= " ORDER BY lr.created_at DESC LIMIT ? OFFSET ?"; // Add LIMIT and OFFSET for pagination

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing the query: " . $conn->error);
}

$bindParams = [$employeeId];
$bindTypes = "i";

if ($searchTerm) {
    $searchTerm = "%$searchTerm%";
    $bindParams[] = $searchTerm;
    $bindParams[] = $searchTerm;
    $bindParams[] = $searchTerm;
    $bindTypes .= "sss";
}
if ($fromDate) {
    $bindParams[] = $fromDate;
    $bindTypes .= "s";
}
if ($toDate) {
    $bindParams[] = $toDate;
    $bindTypes .= "s";
}
if ($statusFilter) {
    $bindParams[] = $statusFilter;
    $bindTypes .= "s";
}
if ($timeFrame == 'specific' && $specificMonth && $specificYear) {
    $bindParams[] = $specificMonth;
    $bindParams[] = $specificYear;
    $bindTypes .= "ii";
}

// Add pagination parameters
$bindParams[] = $recordsPerPage;
$bindParams[] = $offset;
$bindTypes .= "ii";

$stmt->bind_param($bindTypes, ...$bindParams);
if (!$stmt->execute()) {
    die("Error executing the query: " . $stmt->error);
}

$result = $stmt->get_result();

// Calculate total leave days excluding Sundays and holidays
function calculateLeaveDays($start_date, $end_date) {
    $leave_days = 0;
    $current_date = strtotime($start_date);
    $end_date = strtotime($end_date);

    while ($current_date <= $end_date) {
        $day_of_week = date('w', $current_date);
        if ($day_of_week != 0) { // Exclude Sundays
            $leave_days++;
        }
        $current_date = strtotime('+1 day', $current_date);
    }
    return $leave_days;
}

// Query to get total number of records for pagination
$totalRecordsSql = "
    SELECT COUNT(*) as total
    FROM leave_requests lr
    JOIN employee_register e ON lr.employee_id = e.employee_id
    LEFT JOIN employee_register s ON lr.supervisor_id = s.employee_id
    WHERE lr.employee_id = ?";

if ($searchTerm) {
    $totalRecordsSql .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR lr.employee_id LIKE ?)";
}
if ($fromDate) {
    $totalRecordsSql .= " AND lr.start_date >= ?";
}
if ($toDate) {
    $totalRecordsSql .= " AND lr.end_date <= ?";
}
if ($statusFilter) {
    $totalRecordsSql .= " AND lr.status = ?";
}
if ($timeFrame) {
    if ($timeFrame == 'day') {
        $totalRecordsSql .= " AND lr.created_at >= CURDATE()";
    } elseif ($timeFrame == 'week') {
        $totalRecordsSql .= " AND lr.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
    } elseif ($timeFrame == 'month') {
        $totalRecordsSql .= " AND lr.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
    } elseif ($timeFrame == 'specific' && $specificMonth && $specificYear) {
        $totalRecordsSql .= " AND MONTH(lr.created_at) = ? AND YEAR(lr.created_at) = ?";
    }
}

$totalStmt = $conn->prepare($totalRecordsSql);
if (!$totalStmt) {
    die("Error preparing the total records query: " . $conn->error);
}

$totalBindParams = [$employeeId];
$totalBindTypes = "i";

if ($searchTerm) {
    $totalBindParams[] = $searchTerm;
    $totalBindParams[] = $searchTerm;
    $totalBindParams[] = $searchTerm;
    $totalBindTypes .= "sss";
}
if ($fromDate) {
    $totalBindParams[] = $fromDate;
    $totalBindTypes .= "s";
}
if ($toDate) {
    $totalBindParams[] = $toDate;
    $totalBindTypes .= "s";
}
if ($statusFilter) {
    $totalBindParams[] = $statusFilter;
    $totalBindTypes .= "s";
}
if ($timeFrame == 'specific' && $specificMonth && $specificYear) {
    $totalBindParams[] = $specificMonth;
    $totalBindParams[] = $specificYear;
    $totalBindTypes .= "ii";
}

$totalStmt->bind_param($totalBindTypes, ...$totalBindParams);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRecords = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get leave statistics for the dashboard
$leaveStatsSql = "
    SELECT 
        COUNT(*) as total_leaves,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Denied' THEN 1 ELSE 0 END) as denied,
        SUM(CASE WHEN status = 'Supervisor Approved' THEN 1 ELSE 0 END) as supervisor_approved
    FROM leave_requests
    WHERE employee_id = ?";
$leaveStatsStmt = $conn->prepare($leaveStatsSql);
$leaveStatsStmt->bind_param("i", $employeeId);
$leaveStatsStmt->execute();
$leaveStatsResult = $leaveStatsStmt->get_result();
$leaveStats = $leaveStatsResult->fetch_assoc();

// Get leave types for the chart
$leaveTypesSql = "
    SELECT 
        leave_type,
        COUNT(*) as count
    FROM leave_requests
    WHERE employee_id = ?
    GROUP BY leave_type";
$leaveTypesStmt = $conn->prepare($leaveTypesSql);
$leaveTypesStmt->bind_param("i", $employeeId);
$leaveTypesStmt->execute();
$leaveTypesResult = $leaveTypesStmt->get_result();
$leaveTypes = [];
while ($row = $leaveTypesResult->fetch_assoc()) {
    $leaveTypes[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- AOS - Animate On Scroll Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --secondary-color: #333333;
            --dark-bg: #000000;
            --card-bg: #111111;
            --text-primary: #ffffff;
            --text-secondary: #ffffff;
            --text-muted: #cccccc;
            --border-color: #444444;
            --success-color: #4caf50;
            --warning-color: #ffc107;
            --danger-color: #f44336;
            --info-color: #2196f3;
            --gradient-start: #6366f1;
            --gradient-end: #8b5cf6;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header & Navigation */
        .main-header {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-right: 0.75rem;
            font-size: 1.75rem;
        }

        .navbar-nav .nav-link {
            color: var(--text-primary);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
            margin: 0 0.25rem;
        }

        .navbar-nav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .navbar-nav .nav-link.active {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            color: white;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }

        .navbar-nav .nav-link i {
            margin-right: 0.5rem;
        }

        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--text-primary);
        }

        .user-dropdown .dropdown-toggle:hover,
        .user-dropdown .dropdown-toggle:focus {
            background-color: rgba(255, 255, 255, 0.15);
        }

        .user-dropdown .dropdown-menu {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            margin-top: 0.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .user-dropdown .dropdown-item {
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .user-dropdown .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .user-dropdown .dropdown-item i {
            margin-right: 0.5rem;
            width: 1.25rem;
            text-align: center;
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }

        /* Main Content */
        .main-content {
            padding: 2rem 0;
        }

        /* Content Header */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }

        .dashboard-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
        }

        .card-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .card-title {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        /* Charts */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        .chart-container {
            position: relative;
            height: 250px;
        }

        /* Filter Panel */
        .filter-panel {
            background-color: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .filter-panel:hover {
            border-color: var(--primary-color);
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            cursor: pointer;
        }

        .filter-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .filter-title i {
            margin-right: 0.75rem;
            color: var(--primary-color);
        }

        .filter-toggle {
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }

        .filter-toggle:hover {
            color: var(--primary-color);
        }

        .filter-body {
            transition: all 0.3s ease;
            max-height: 1000px;
            overflow: hidden;
        }

        .filter-body.collapsed {
            max-height: 0;
        }

        /* Table View */
        .table-container {
            background-color: var(--card-bg);
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .table-container:hover {
            border-color: var(--primary-color);
        }

        .table {
            color: var(--text-primary);
            border-color: var(--border-color);
            margin-bottom: 0;
        }

        .table th {
            background-color: var(--card-bg);
            color: var(--text-primary);
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: var(--border-color);
            background-color: var(--dark-bg);
            color: var(--text-primary);
        }


        /* Empty State */
        .empty-state {
            background-color: var(--card-bg);
            border-radius: 1rem;
            padding: 3rem 2rem;
            text-align: center;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .empty-state:hover {
            border-color: var(--primary-color);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .empty-text {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .page-item {
            margin: 0 0.25rem;
        }

        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }

        /* Form Controls */
        .form-control, .form-select {
            background-color: var(--dark-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--dark-bg);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
            color: var(--text-primary);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .input-group-text {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        /* Buttons */
        .btn {
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 0;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            z-index: -1;
        }

        .btn:hover:after {
            height: 100%;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            border: none;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-dark {
            background-color: var(--dark-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        .btn-dark:hover {
            background-color: var(--secondary-color);
            border-color: var(--border-color);
        }

        .btn-icon {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0;
        }

        /* Modal */
        .modal-content {
            background-color: var(--card-bg);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
        }

        .modal-header, .modal-footer {
            border-color: var(--border-color);
        }

        .modal-title {
            color: var(--text-primary);
        }

        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .navbar-collapse {
                background-color: var(--card-bg);
                border-radius: 0.5rem;
                padding: 1rem;
                margin-top: 1rem;
                border: 1px solid var(--border-color);
            }

            .navbar-toggler {
                border: none;
                color: var(--text-primary);
                padding: 0.5rem;
            }

            .navbar-toggler:focus {
                box-shadow: none;
            }

            .navbar-toggler-icon {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            }

            .dashboard-cards, .charts-container {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        .slide-in-up {
            animation: slideInUp 0.5s ease-in-out;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--card-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Make sure all text is white */
        p, span, h1, h2, h3, h4, h5, h6, a, button, input, select, textarea, label, th, td {
            color: var(--text-primary);
        }

        .text-muted {
            color: var(--text-muted) !important;
        }
        
        /* Export button styling */
        .export-btn {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.4);
        }

        /* Tooltip */
        .tooltip-container {
            position: relative;
            display: inline-block;
        }

        .tooltip-text {
            visibility: hidden;
            width: 200px;
            background-color: var(--card-bg);
            color: var(--text-primary);
            text-align: center;
            border-radius: 0.5rem;
            padding: 0.75rem;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .tooltip-container:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        /* Progress Bar */
        .progress {
            height: 0.5rem;
            background-color: var(--dark-bg);
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .progress-bar {
            height: 100%;
            border-radius: 1rem;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
        }
        
        /* Specific month/year selector styles */
        .specific-date-container {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .specific-date-container.hidden {
            display: none;
        }
        /* Main Content */
.main-content {
    padding: 2rem 0;
    background-color: #000000; /* Ensure the outside background is black */
}

/* Table View */
.table-container {
    background-color: #1e1e1e; /* Dark background for the table container */
    border-radius: 1rem;
    overflow: hidden;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.table-container:hover {
    border-color: var(--primary-color);
}

.table {
    color: var(--text-primary);
    border-color: var(--border-color);
    margin-bottom: 0;
}

.table th {
    background-color: #1e1e1e; /* Dark background for table headers */
    color: var(--text-primary);
    font-weight: 600;
    border-bottom: 1px solid var(--border-color);
    padding: 1rem;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
    border-color: var(--border-color);
    background-color: #1e1e1e; /* Dark background for table cells */
    color: var(--text-primary);
}

    </style>
</head>
<body>
    <!-- Main Header with Navigation -->
    <header class="main-header">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="leave_file.php">
                                    <i class="fas fa-file-alt"></i> File Leave
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="leave_history.php">
                                    <i class="fas fa-history"></i> Leave History
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="leave_details.php">
                                    <i class="fas fa-info-circle"></i> Leave Details
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Content Header -->
            <div class="content-header" data-aos="fade-down">
                <h1 class="page-title">
                    <i class="fas fa-history me-2"></i> Leave History
                </h1>
                <div class="header-actions">
                    <form method="POST" action="export_leave_history.php" style="display:inline;">
                        <input type="hidden" name="searchTerm" value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <input type="hidden" name="fromDate" value="<?php echo htmlspecialchars($fromDate); ?>">
                        <input type="hidden" name="toDate" value="<?php echo htmlspecialchars($toDate); ?>">
                        <input type="hidden" name="statusFilter" value="<?php echo htmlspecialchars($statusFilter); ?>">
                        <input type="hidden" name="timeFrame" value="<?php echo htmlspecialchars($timeFrame); ?>">
                        <input type="hidden" name="specificMonth" value="<?php echo htmlspecialchars($specificMonth); ?>">
                        <input type="hidden" name="specificYear" value="<?php echo htmlspecialchars($specificYear); ?>">
                        <button type="submit" class="export-btn">
                            <i class="fas fa-file-export me-2"></i> Export
                        </button>
                    </form>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards" data-aos="fade-up">
                <div class="dashboard-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="card-title">Total Leaves</div>
                    <div class="card-value"><?php echo $leaveStats['total_leaves']; ?></div>
                </div>
                <div class="dashboard-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-title">Approved</div>
                    <div class="card-value"><?php echo $leaveStats['approved']; ?></div>
                </div>
                <div class="dashboard-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-title">Pending</div>
                    <div class="card-value"><?php echo $leaveStats['pending']; ?></div>
                </div>
                <div class="dashboard-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="card-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="card-title">Denied</div>
                    <div class="card-value"><?php echo $leaveStats['denied']; ?></div>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" data-aos="fade-up">
                <div class="filter-header" id="filterHeader">
                    <h2 class="filter-title">
                        <i class="fas fa-filter"></i> Filter Leave Records
                    </h2>
                    <button class="filter-toggle" id="filterToggle">
                        <i class="fas fa-chevron-up" id="filterIcon"></i>
                    </button>
                </div>
                <div class="filter-body" id="filterBody">
                    <form class="row g-3" method="GET" action="">
                        <input type="hidden" name="page" value="1">
                        <div class="col-md-6 col-lg-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" name="searchTerm" placeholder="Search by Name/ID" value="<?php echo htmlspecialchars($searchTerm); ?>">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <input type="date" class="form-control" name="fromDate" placeholder="From Date" value="<?php echo htmlspecialchars($fromDate); ?>">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <input type="date" class="form-control" name="toDate" placeholder="To Date" value="<?php echo htmlspecialchars($toDate); ?>">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <select class="form-select" name="statusFilter">
                                <option value="">Filter by Status</option>
                                <option value="Approved" <?php if ($statusFilter == 'Approved') echo 'selected'; ?>>Approved</option>
                                <option value="Pending" <?php if ($statusFilter == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Denied" <?php if ($statusFilter == 'Denied') echo 'selected'; ?>>Denied</option>
                                <option value="Supervisor Approved" <?php if ($statusFilter == 'Supervisor Approved') echo 'selected'; ?>>Supervisor Approved</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <select class="form-select" id="timeFrameSelect" name="timeFrame">
                                <option value="">Filter by Time Frame</option>
                                <option value="day" <?php if ($timeFrame == 'day') echo 'selected'; ?>>Last Day</option>
                                <option value="week" <?php if ($timeFrame == 'week') echo 'selected'; ?>>Last Week</option>
                                <option value="month" <?php if ($timeFrame == 'month') echo 'selected'; ?>>Last Month</option>
                                <option value="specific" <?php if ($timeFrame == 'specific') echo 'selected'; ?>>Specific Month/Year</option>
                            </select>
                            
                            <div id="specificDateContainer" class="specific-date-container <?php echo ($timeFrame == 'specific') ? '' : 'hidden'; ?>">
                                <select class="form-select" name="specificMonth">
                                    <option value="">Select Month</option>
                                    <option value="1" <?php if ($specificMonth == '1') echo 'selected'; ?>>January</option>
                                    <option value="2" <?php if ($specificMonth == '2') echo 'selected'; ?>>February</option>
                                    <option value="3" <?php if ($specificMonth == '3') echo 'selected'; ?>>March</option>
                                    <option value="4" <?php if ($specificMonth == '4') echo 'selected'; ?>>April</option>
                                    <option value="5" <?php if ($specificMonth == '5') echo 'selected'; ?>>May</option>
                                    <option value="6" <?php if ($specificMonth == '6') echo 'selected'; ?>>June</option>
                                    <option value="7" <?php if ($specificMonth == '7') echo 'selected'; ?>>July</option>
                                    <option value="8" <?php if ($specificMonth == '8') echo 'selected'; ?>>August</option>
                                    <option value="9" <?php if ($specificMonth == '9') echo 'selected'; ?>>September</option>
                                    <option value="10" <?php if ($specificMonth == '10') echo 'selected'; ?>>October</option>
                                    <option value="11" <?php if ($specificMonth == '11') echo 'selected'; ?>>November</option>
                                    <option value="12" <?php if ($specificMonth == '12') echo 'selected'; ?>>December</option>
                                </select>
                                <select class="form-select" name="specificYear">
                                    <option value="">Select Year</option>
                                    <?php 
                                    $currentYear = date('Y');
                                    for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
                                        echo '<option value="' . $year . '"';
                                        if ($specificYear == $year) echo ' selected';
                                        echo '>' . $year . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table View -->
            <div class="table-container" data-aos="fade-up">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar-check me-1"></i> Date Applied</th>
                                <th><i class="fas fa-id-card me-1"></i> Employee ID</th>
                                <th><i class="fas fa-user me-1"></i> Employee Name</th>
                                <th><i class="fas fa-calendar-day me-1"></i> Leave Dates</th>
                                <th><i class="fas fa-tag me-1"></i> Leave Type</th>
                                <th><i class="fas fa-calculator me-1"></i> Total Days</th>
                                <th><i class="fas fa-info-circle me-1"></i> Status</th>
                                <th><i class="fas fa-user-tie me-1"></i> Supervisor</th>
                                <th><i class="fas fa-cogs me-1"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        if ($result->num_rows > 0):
                            // Reset the result pointer
                            $result->data_seek(0);
                            while ($row = $result->fetch_assoc()): 
                        ?>
                            <?php
                                $leave_days = calculateLeaveDays($row['start_date'], $row['end_date']);

                                // Status badge classes
                                $badgeClass = '';
                                if ($row['status'] === 'Approved') {
                                    $badgeClass = 'status-approved';
                                } elseif ($row['status'] === 'Denied') {
                                    $badgeClass = 'status-denied';
                                } elseif ($row['status'] === 'Pending') {
                                    $badgeClass = 'status-pending';
                                } elseif ($row['status'] === 'Supervisor Approved' || $row['status'] === 'Supervisor Denied') {
                                    $badgeClass = 'status-supervisor';
                                }
                            ?>
                            <tr>
                                <td>
                                    <?php if (isset($row['created_at'])): ?>
                                        <span class="d-block"><?= date("F j, Y", strtotime($row['created_at'])) ?></span>
                                        <small class="text-muted"><?= date("g:i A", strtotime($row['created_at'])) ?></small>
                                    <?php else: ?>
                                        Not Available
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['employee_id']) ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td>
                                    <span class="d-block"><?= date("F j, Y", strtotime($row['start_date'])) ?></span>
                                    <span class="d-block text-muted">to</span>
                                    <span class="d-block"><?= date("F j, Y", strtotime($row['end_date'])) ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['leave_type']) ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= $leave_days ?> day<?= $leave_days > 1 ? 's' : '' ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['status']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($row['supervisor_first_name'])): ?>
                                        <?= htmlspecialchars($row['supervisor_first_name'] . ' ' . $row['supervisor_last_name']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-danger btn-action" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal" 
                                            data-id="<?= $row['leave_id'] ?>" 
                                            <?= $row['status'] !== 'Supervisor Approved' ? 'disabled' : '' ?>>
                                        <i class="fas fa-times-circle"></i> Cancel
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="empty-icon pulse">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                    <h3 class="empty-title mt-3">No Leave Records Found</h3>
                                    <p class="empty-text">You don't have any leave records that match your current filters. Try adjusting your search criteria or file a new leave request.</p>
                                    <a href="leave_file.php" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-2"></i> File New Leave
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-container" data-aos="fade-up">
                    <ul class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&searchTerm=<?php echo $searchTerm; ?>&fromDate=<?php echo $fromDate; ?>&toDate=<?php echo $toDate; ?>&statusFilter=<?php echo $statusFilter; ?>&timeFrame=<?php echo $timeFrame; ?>&specificMonth=<?php echo $specificMonth; ?>&specificYear=<?php echo $specificYear; ?>" aria-label="Previous">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php if ($i == $currentPage) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&searchTerm=<?php echo $searchTerm; ?>&fromDate=<?php echo $fromDate; ?>&toDate=<?php echo $toDate; ?>&statusFilter=<?php echo $statusFilter; ?>&timeFrame=<?php echo $timeFrame; ?>&specificMonth=<?php echo $specificMonth; ?>&specificYear=<?php echo $specificYear; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&searchTerm=<?php echo $searchTerm; ?>&fromDate=<?php echo $fromDate; ?>&toDate=<?php echo $toDate; ?>&statusFilter=<?php echo $statusFilter; ?>&timeFrame=<?php echo $timeFrame; ?>&specificMonth=<?php echo $specificMonth; ?>&specificYear=<?php echo $specificYear; ?>" aria-label="Next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirm Cancellation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this leave request? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> No, Keep It
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-check me-1"></i> Yes, Cancel Leave
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- AOS - Animate On Scroll Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Store the ID of the item to be deleted
        let itemToDelete = null;

        // Set up event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Delete modal
            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    itemToDelete = button.getAttribute('data-id');
                });
            }

            // Filter toggle
            const filterHeader = document.getElementById('filterHeader');
            const filterBody = document.getElementById('filterBody');
            const filterIcon = document.getElementById('filterIcon');
            
            if (filterHeader && filterBody && filterIcon) {
                filterHeader.addEventListener('click', function() {
                    filterBody.classList.toggle('collapsed');
                    filterIcon.classList.toggle('fa-chevron-up');
                    filterIcon.classList.toggle('fa-chevron-down');
                });
            }
            
            // Specific month/year toggle
            const timeFrameSelect = document.getElementById('timeFrameSelect');
            const specificDateContainer = document.getElementById('specificDateContainer');
            
            if (timeFrameSelect && specificDateContainer) {
                timeFrameSelect.addEventListener('change', function() {
                    if (this.value === 'specific') {
                        specificDateContainer.classList.remove('hidden');
                    } else {
                        specificDateContainer.classList.add('hidden');
                    }
                });
            }

            // Initialize charts
            initCharts();
        });

        // Delete Button Function
        function confirmDelete() {
            if (itemToDelete) {
                // Here you would typically make an AJAX call to cancel the leave request
                fetch('cancel_leave.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'leave_id=' + itemToDelete
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message and reload page
                        alert('Leave request cancelled successfully');
                        window.location.reload();
                    } else {
                        // Show error message
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while cancelling the leave request');
                });
            }

            // Close the modal
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            if (deleteModal) {
                deleteModal.hide();
            }
        }

        // Initialize Charts
        function initCharts() {
            // Status Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Approved', 'Pending', 'Denied', 'Supervisor Approved'],
                        datasets: [{
                            data: [
                                <?php echo $leaveStats['approved']; ?>, 
                                <?php echo $leaveStats['pending']; ?>, 
                                <?php echo $leaveStats['denied']; ?>, 
                                <?php echo $leaveStats['supervisor_approved']; ?>
                            ],
                            backgroundColor: [
                                'rgba(76, 175, 80, 0.7)',
                                'rgba(255, 193, 7, 0.7)',
                                'rgba(244, 67, 54, 0.7)',
                                'rgba(33, 150, 243, 0.7)'
                            ],
                            borderColor: [
                                'rgba(76, 175, 80, 1)',
                                'rgba(255, 193, 7, 1)',
                                'rgba(244, 67, 54, 1)',
                                'rgba(33, 150, 243, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#ffffff'
                                }
                            }
                        }
                    }
                });
            }

            // Leave Types Chart
            const typeCtx = document.getElementById('typeChart');
            if (typeCtx) {
                new Chart(typeCtx, {
                    type: 'bar',
                    data: {
                        labels: [
                            <?php 
                            foreach ($leaveTypes as $type) {
                                echo "'" . $type['leave_type'] . "', ";
                            }
                            ?>
                        ],
                        datasets: [{
                            label: 'Number of Leaves',
                            data: [
                                <?php 
                                foreach ($leaveTypes as $type) {
                                    echo $type['count'] . ", ";
                                }
                                ?>
                            ],
                            backgroundColor: 'rgba(99, 102, 241, 0.7)',
                            borderColor: 'rgba(99, 102, 241, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#ffffff'
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#ffffff'
                                },
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#ffffff'
                                }
                            }
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>