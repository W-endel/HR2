<?php
session_start();
include '../../db/db_conn.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../../login.php");
    exit();
}

$employeeId = $_SESSION['employee_id'];

// Fetch employee info
$sql = "SELECT employee_id, first_name, middle_name, last_name, birthdate, email, role, position, department, phone_number, address, pfp 
        FROM employee_register 
        WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$employeeInfo = $result->fetch_assoc();

// Get current date for defaults
$currentDate = new DateTime();
$currentYear = $currentDate->format('Y');
$currentMonth = $currentDate->format('m');
$currentMonthName = $currentDate->format('F');

// Get filter params
$selectedYear = $_GET['year'] ?? $currentYear;
$selectedMonth = $_GET['month'] ?? $currentMonth;

// Fetch available years
$sqlYears = "SELECT DISTINCT YEAR(evaluated_at) as year FROM evaluations WHERE employee_id = ?
             UNION
             SELECT DISTINCT YEAR(evaluated_at) as year FROM ptp_evaluations WHERE employee_id = ?
             ORDER BY year DESC";
$stmtYears = $conn->prepare($sqlYears);
$stmtYears->bind_param("ss", $employeeId, $employeeId);
$stmtYears->execute();
$yearsResult = $stmtYears->get_result();
$availableYears = [];
while ($row = $yearsResult->fetch_assoc()) {
    $availableYears[] = $row['year'];
}

// Fetch evaluation data
$sqlEval = "SELECT 
                'combined' as source,
                AVG((quality + communication_skills + teamwork + punctuality + initiative) / 5) AS overall_avg,
                AVG(quality) AS avg_quality, 
                AVG(communication_skills) AS avg_communication_skills, 
                AVG(teamwork) AS avg_teamwork, 
                AVG(punctuality) AS avg_punctuality, 
                AVG(initiative) AS avg_initiative,
                COUNT(*) AS total_evaluations 
            FROM (
                SELECT quality, communication_skills, teamwork, punctuality, initiative, evaluated_at
                FROM evaluations
                WHERE employee_id = ? 
                AND YEAR(evaluated_at) = ?
                AND MONTH(evaluated_at) = ?
                
                UNION ALL
                
                SELECT quality, communication_skills, teamwork, punctuality, initiative, evaluated_at
                FROM ptp_evaluations
                WHERE employee_id = ? 
                AND YEAR(evaluated_at) = ?
                AND MONTH(evaluated_at) = ?
            ) AS combined_data";
            
$stmtEval = $conn->prepare($sqlEval);
$stmtEval->bind_param("siiiii", $employeeId, $selectedYear, $selectedMonth, $employeeId, $selectedYear, $selectedMonth);
$stmtEval->execute();
$evalResult = $stmtEval->get_result();

if ($evalResult->num_rows > 0) {
    $evaluation = $evalResult->fetch_assoc();
    $overallAverage = $evaluation['overall_avg'];
} else {
    $evaluation = [
        'avg_quality' => 0,
        'avg_communication_skills' => 0,
        'avg_teamwork' => 0,
        'avg_punctuality' => 0,
        'avg_initiative' => 0,
        'overall_avg' => 0,
        'total_evaluations' => 0
    ];
    $overallAverage = 0;
}

// Fetch monthly averages - THIS IS THE CRUCIAL FIX
$sqlMonthly = "SELECT 
                  DATE_FORMAT(evaluated_at, '%Y-%m') as month,
                  AVG((quality + communication_skills + teamwork + punctuality + initiative) / 5) as avg_score,
                  COUNT(*) as count
              FROM (
                  SELECT quality, communication_skills, teamwork, punctuality, initiative, evaluated_at
                  FROM evaluations
                  WHERE employee_id = ?
                  
                  UNION ALL
                  
                  SELECT quality, communication_skills, teamwork, punctuality, initiative, evaluated_at
                  FROM ptp_evaluations
                  WHERE employee_id = ?
              ) AS combined_data
              GROUP BY DATE_FORMAT(evaluated_at, '%Y-%m')
              ORDER BY month";
              
$stmtMonthly = $conn->prepare($sqlMonthly);
$stmtMonthly->bind_param("ss", $employeeId, $employeeId);
$stmtMonthly->execute();
$monthlyResult = $stmtMonthly->get_result();

$monthlyData = [];
while ($row = $monthlyResult->fetch_assoc()) {
    $month = $row['month'];
    $date = DateTime::createFromFormat('Y-m', $month);
    $monthlyData[$month] = [
        'label' => $date->format('M Y'),
        'score' => (float)$row['avg_score'],
        'count' => (int)$row['count']
    ];
}

// Close connections
$stmt->close();
$stmtYears->close();
$stmtEval->close();
$stmtMonthly->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Result | HR2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/styles.css" rel="stylesheet" />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #00b4d8;
            --secondary-color: #0077b6;
            --accent-color: #48cae4;
            --grid-color: rgba(73, 80, 87, 0.5);
            --dark-bg: rgba(16, 17, 18) !important;
            --card-bg: rgba(33, 37, 41) !important;
            --border-color: #2a2a2a;
            --text-primary: #f8f9fa;
            --text-secondary: #adb5bd;
            --text-muted: #6c757d;
            --success-color: #06d6a0;
            --warning-color: #ffd166;
            --danger-color: #ef476f;
        }
        
        body {
            background-color: var(--dark-bg);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .page-header h1 i {
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        
        .evaluation-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            color: var(--primary-color);
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .chart-container {
            background-color: #141414;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 20px 20px;
            pointer-events: none;
            border-radius: 6px;
        }
        
        .chart-title {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 1rem;
            color: var(--text-primary);
            text-align: center;
        }
        
        .chart-subtitle {
            font-size: 10px;
            color: white;
            text-align: center;
            margin-bottom: 1.25rem;
        }
        
        .chart-controls {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
            gap: 0.5rem;
        }
        
        .chart-control-btn {
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .chart-control-btn:hover {
            background-color: rgba(0, 180, 216, 0.1);
            color: var(--primary-color);
        }
        
        .chart-control-btn.active {
            background-color: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
        }
        
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
        }
        
        .data-table th {
            background-color: rgba(0, 0, 0, 0.3);
            color: var(--text-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
        }
        
        .data-table td {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            font-size: 0.9rem;
        }
        
        .data-table tr:nth-child(odd) td {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .data-table tr:hover td {
            background-color: rgba(0, 180, 216, 0.05);
        }
        
        .category-cell {
            display: flex;
            align-items: center;
        }
        
        .category-cell i {
            margin-right: 0.75rem;
            width: 16px;
            color: var(--primary-color);
        }
        
        .value-cell {
            text-align: center;
            font-weight: 600;
        }
        
        .value-cell .badge {
            padding: 0.35rem 0.65rem;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.85rem;
            background-color: rgba(0, 180, 216, 0.15);
            color: var(--primary-color);
        }
        
        .summary-row {
            background-color: rgba(0, 0, 0, 0.2) !important;
        }
        
        .summary-row td {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .chart-legend {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            font-size: 10px;
            color: white;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            margin-right: 0.5rem;
        }
        
        .chart-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        
        .chart-footer .evaluation-count {
            display: flex;
            align-items: center;
        }
        
        .chart-footer .evaluation-count i {
            margin-right: 0.5rem;
        }
        
        .data-point {
            position: relative;
            cursor: pointer;
        }
        
        .data-point:hover::after {
            content: attr(data-value);
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 10;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .chart-controls {
                flex-wrap: wrap;
            }
            
            .data-table th,
            .data-table td {
                padding: 0.5rem;
                font-size: 0.8rem;
            }
        }
        
        .btn-close-calendar {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 1.25rem;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .btn-close-calendar:hover {
            color: var(--text-primary);
        }
        
        /* Loading modal styling */
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(0, 180, 216, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Tooltip styling */
        .tooltip-inner {
            background-color: rgba(0, 0, 0, 0.9);
            color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            max-width: 250px;
        }
        
        .bs-tooltip-auto[x-placement^=top] .arrow::before, 
        .bs-tooltip-top .arrow::before {
            border-top-color: rgba(0, 0, 0, 0.9);
        }
    </style>
</head>
<body class="sb-nav-fixed bg-black">
   <?php include 'navbar.php'; ?>
    <div id="layoutSidenav">
    <?php include 'sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main class="bg-black">
                <div class="container-fluid position-relative px-4">
                    <div class="mb-4 text-light">
                        <h1>Evaluation Analytics</h1>
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
                    
                    <div class="evaluation-card">
                        <div class="card-header">
                            <h2 class="text-light"><i class="fas fa-chart-line me-2"></i>Performance Metrics</h2>
                            <div class="d-flex align-items-center">
                                <!-- Year Filter Dropdown -->
                                <select id="yearFilter" class="form-select form-select-sm bg-dark text-light border-dark me-2">
                                    <?php
                                    // Get current year and month
                                    $currentYear = date('Y');
                                    $currentMonth = date('n');
                                    
                                    // Determine if we're in Q4 (October-December)
                                    $isQ4 = ($currentMonth >= 10);
                                    
                                    // Set end year (current year + 1 if in Q4)
                                    $endYear = $isQ4 ? $currentYear + 1 : $currentYear;
                                    
                                    // Set start year (2020 or earliest evaluation year)
                                    $startYear = 2020;
                                    if (!empty($availableYears)) {
                                        $earliestYear = min($availableYears);
                                        $startYear = min($earliestYear, 2020);
                                    }
                                    
                                    // Generate year options
                                    for ($year = $startYear; $year <= $endYear; $year++) {
                                        $selected = ($year == $selectedYear) ? 'selected' : '';
                                        echo "<option value='$year' $selected>$year</option>";
                                    }
                                    ?>
                                </select>
                                
                                <!-- Month Filter Dropdown -->
                                <select id="monthFilter" class="form-select form-select-sm bg-dark text-light border-dark">
                                    <option value="01" <?php echo ($selectedMonth == '01') ? 'selected' : ''; ?>>January</option>
                                    <option value="02" <?php echo ($selectedMonth == '02') ? 'selected' : ''; ?>>February</option>
                                    <option value="03" <?php echo ($selectedMonth == '03') ? 'selected' : ''; ?>>March</option>
                                    <option value="04" <?php echo ($selectedMonth == '04') ? 'selected' : ''; ?>>April</option>
                                    <option value="05" <?php echo ($selectedMonth == '05') ? 'selected' : ''; ?>>May</option>
                                    <option value="06" <?php echo ($selectedMonth == '06') ? 'selected' : ''; ?>>June</option>
                                    <option value="07" <?php echo ($selectedMonth == '07') ? 'selected' : ''; ?>>July</option>
                                    <option value="08" <?php echo ($selectedMonth == '08') ? 'selected' : ''; ?>>August</option>
                                    <option value="09" <?php echo ($selectedMonth == '09') ? 'selected' : ''; ?>>September</option>
                                    <option value="10" <?php echo ($selectedMonth == '10') ? 'selected' : ''; ?>>October</option>
                                    <option value="11" <?php echo ($selectedMonth == '11') ? 'selected' : ''; ?>>November</option>
                                    <option value="12" <?php echo ($selectedMonth == '12') ? 'selected' : ''; ?>>December</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body text-light">
                            <div class="chart-title fs-4">Performance Evaluation Metrics</div>
                            <div class="chart-subtitle fs-5">
                                Analysis of <span class="text-info"><?php echo htmlspecialchars($employeeInfo['first_name'] . ' ' . $employeeInfo['last_name']); ?>'s</span> 
                                performance for <?php echo DateTime::createFromFormat('!m', $selectedMonth)->format('F') . ' ' . $selectedYear; ?>
                            </div>
                            
                            <div class="chart-container">
                                <canvas id="evaluationChart" height="300"></canvas>
                            </div>
                            
                            <div class="chart-footer">
                                <div class="timestamp text-white">
                                    <span class="me-2">Last update:</span> 
                                    <?php
                                        date_default_timezone_set('Asia/Manila');
                                        echo date('F j, Y h:i A');
                                    ?>
                                </div>
                            </div>
                            
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th width="50%">Performance Category</th>
                                        <th width="25%">Rating (1-6)</th>
                                        <th width="25%">Performance Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="category-cell">
                                                <i class="fas fa-medal me-2"></i>
                                                Quality of Work
                                            </div>
                                        </td>
                                        <td class="value-cell">
                                            <span class="badgeT"><?php echo htmlspecialchars(number_format($evaluation['avg_quality'], 2)); ?></span>
                                        </td>
                                        <td class="value-cell">
                                            <?php 
                                            $score = $evaluation['avg_quality'];
                                            if ($score >= 1 && $score < 2) {
                                                echo 'Underperforming';
                                            } elseif ($score >= 2 && $score < 3) {
                                                echo 'Development Needed';
                                            } elseif ($score >= 3 && $score < 4) {
                                                echo 'Meets Expectations';
                                            } elseif ($score >= 4 && $score < 5) {
                                                echo 'Exceeds Expectations';
                                            } elseif ($score >= 5 && $score < 6) {
                                                echo 'Strong Performance';
                                            } elseif ($score == 6) {
                                                echo 'Outstanding Performance';
                                            } else {
                                                echo 'Not Rated';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="category-cell">
                                                <i class="fas fa-comments me-2"></i>
                                                Communication Skills
                                            </div>
                                        </td>
                                        <td class="value-cell">
                                            <span class="badgeT"><?php echo htmlspecialchars(number_format($evaluation['avg_communication_skills'], 2)); ?></span>
                                        </td>
                                        <td class="value-cell">
                                            <?php 
                                            $score = $evaluation['avg_communication_skills'];
                                            if ($score >= 1 && $score < 2) {
                                                echo 'Underperforming';
                                            } elseif ($score >= 2 && $score < 3) {
                                                echo 'Development Needed';
                                            } elseif ($score >= 3 && $score < 4) {
                                                echo 'Meets Expectations';
                                            } elseif ($score >= 4 && $score < 5) {
                                                echo 'Exceeds Expectations';
                                            } elseif ($score >= 5 && $score < 6) {
                                                echo 'Strong Performance';
                                            } elseif ($score == 6) {
                                                echo 'Outstanding Performance';
                                            } else {
                                                echo 'Not Rated';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="category-cell">
                                                <i class="fas fa-users me-2"></i>
                                                Teamwork
                                            </div>
                                        </td>
                                        <td class="value-cell">
                                            <span class="badgeT"><?php echo htmlspecialchars(number_format($evaluation['avg_teamwork'], 2)); ?></span>
                                        </td>
                                        <td class="value-cell">
                                            <?php 
                                            $score = $evaluation['avg_teamwork'];
                                            if ($score >= 1 && $score < 2) {
                                                echo 'Underperforming';
                                            } elseif ($score >= 2 && $score < 3) {
                                                echo 'Development Needed';
                                            } elseif ($score >= 3 && $score < 4) {
                                                echo 'Meets Expectations';
                                            } elseif ($score >= 4 && $score < 5) {
                                                echo 'Exceeds Expectations';
                                            } elseif ($score >= 5 && $score < 6) {
                                                echo 'Strong Performance';
                                            } elseif ($score == 6) {
                                                echo 'Outstanding Performance';
                                            } else {
                                                echo 'Not Rated';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="category-cell">
                                                <i class="fas fa-clock me-2"></i>
                                                Punctuality
                                            </div>
                                        </td>
                                        <td class="value-cell">
                                            <span class="badgeT"><?php echo htmlspecialchars(number_format($evaluation['avg_punctuality'], 2)); ?></span>
                                        </td>
                                        <td class="value-cell">
                                            <?php 
                                            $score = $evaluation['avg_punctuality'];
                                            if ($score >= 1 && $score < 2) {
                                                echo 'Underperforming';
                                            } elseif ($score >= 2 && $score < 3) {
                                                echo 'Development Needed';
                                            } elseif ($score >= 3 && $score < 4) {
                                                echo 'Meets Expectations';
                                            } elseif ($score >= 4 && $score < 5) {
                                                echo 'Exceeds Expectations';
                                            } elseif ($score >= 5 && $score < 6) {
                                                echo 'Strong Performance';
                                            } elseif ($score == 6) {
                                                echo 'Outstanding Performance';
                                            } else {
                                                echo 'Not Rated';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="category-cell">
                                                <i class="fas fa-lightbulb me-2"></i>
                                                Initiative
                                            </div>
                                        </td>
                                        <td class="value-cell">
                                            <span class="badgeT"><?php echo htmlspecialchars(number_format($evaluation['avg_initiative'], 2)); ?></span>
                                        </td>
                                        <td class="value-cell">
                                            <?php 
                                            $score = $evaluation['avg_initiative'];
                                            if ($score >= 1 && $score < 2) {
                                                echo 'Underperforming';
                                            } elseif ($score >= 2 && $score < 3) {
                                                echo 'Development Needed';
                                            } elseif ($score >= 3 && $score < 4) {
                                                echo 'Meets Expectations';
                                            } elseif ($score >= 4 && $score < 5) {
                                                echo 'Exceeds Expectations';
                                            } elseif ($score >= 5 && $score < 6) {
                                                echo 'Strong Performance';
                                            } elseif ($score == 6) {
                                                echo 'Outstanding Performance';
                                            } else {
                                                echo 'Not Rated';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr class="summary-row">
                                        <td>
                                            <div class="category-cell">
                                                <i class="fas fa-star me-2"></i>
                                                Overall Average
                                            </div>
                                        </td>
                                        <td class="value-cell">
                                            <span class="badgeT" style="background-color: rgba(6, 214, 160, 0.15); color: var(--success-color);">
                                                <?php echo htmlspecialchars(number_format($overallAverage, 2)); ?>
                                            </span>
                                        </td>
                                        <td class="value-cell">
                                            <?php 
                                            $score = $overallAverage;
                                            if ($score >= 1 && $score < 2) {
                                                echo 'Underperforming';
                                            } elseif ($score >= 2 && $score < 3) {
                                                echo 'Development Needed';
                                            } elseif ($score >= 3 && $score < 4) {
                                                echo 'Meets Expectations';
                                            } elseif ($score >= 4 && $score < 5) {
                                                echo 'Exceeds Expectations';
                                            } elseif ($score >= 5 && $score < 6) {
                                                echo 'Strong Performance';
                                            } elseif ($score == 6) {
                                                echo 'Outstanding Performance';
                                            } else {
                                                echo 'Not Rated';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Monthly Performance History Section -->
                    <div class="evaluation-card mt-4">
                        <div class="card-header">
                            <h2 class="text-light"><i class="fas fa-chart-line me-2"></i>Monthly Performance History</h2>
                            <div class="d-flex align-items-center">
                                <select id="historyYearFilter" class="form-select form-select-sm bg-dark text-light border-dark">
                                    <option value="all">All Years</option>
                                    <?php 
                                    $currentYear = date('Y');
                                    $startYear = 2020;
                                    $endYear = $currentYear;
                                    $currentQuarter = ceil(date('n') / 3);
                                    if ($currentQuarter >= 4) $endYear++;
                                    
                                    for ($year = $startYear; $year <= $endYear; $year++): 
                                    ?>
                                        <option value="<?= $year ?>" <?= $year == $selectedYear ? 'selected' : '' ?>>
                                            <?= $year ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="card-body text-light">
                            <div class="chart-container" style="height: 400px; position: relative;">
                                <canvas id="monthlyPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>

                    
                    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content bg-dark text-muted">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                                    <button type="button" class="btn-close btn-close-light" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-sign-out-alt fa-3x text-warning mb-3"></i>
                                        <p>Are you sure you want to log out?</p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                                    <form action="../../employee/logout.php" method="POST">
                                        <button type="submit" class="btn btn-danger">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>  
                </div>

            </main>
            <?php include 'footer.php'; ?>
        </div>
    </div>
    
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                    <div class="loading-spinner"></div>
                    <div class="mt-3 text-light fw-bold">Processing data...</div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="../../js/employee.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to update year filter options with new years
    function updateYearFilterOptions() {
        const yearFilter = document.getElementById('yearFilter');
        const currentYear = new Date().getFullYear();
        const currentMonth = new Date().getMonth() + 1; // January is 0
        const isQ4 = (currentMonth >= 10); // October-December
        const endYear = isQ4 ? currentYear + 1 : currentYear;
        
        // Get the last year currently in the dropdown
        const lastOption = yearFilter.options[yearFilter.options.length - 1];
        const lastYear = parseInt(lastOption.value);
        
        // Add new years if needed
        if (endYear > lastYear) {
            for (let year = lastYear + 1; year <= endYear; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearFilter.appendChild(option);
            }
        }
    }

    // Set default to current year/month if no selection exists
    if (!<?php echo isset($_GET['year']) ? 'true' : 'false'; ?>) {
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        
        document.getElementById('yearFilter').value = currentYear;
        document.getElementById('monthFilter').value = currentMonth;
    }

    // Initialize the chart with the selected data
    const ctx = document.getElementById('evaluationChart').getContext('2d');
    
    // Chart data with PHP variables
    const chartData = {
        labels: [
            'Quality of Work', 
            'Communication Skills', 
            'Teamwork', 
            'Punctuality', 
            'Initiative'
        ],
        datasets: [{
            label: 'Performance Metrics',
            data: [
                <?php echo number_format($evaluation['avg_quality'], 2); ?>,
                <?php echo number_format($evaluation['avg_communication_skills'], 2); ?>,
                <?php echo number_format($evaluation['avg_teamwork'], 2); ?>,
                <?php echo number_format($evaluation['avg_punctuality'], 2); ?>,
                <?php echo number_format($evaluation['avg_initiative'], 2); ?>
            ],
            backgroundColor: 'rgba(0, 180, 216, 0.1)',
            borderColor: 'rgba(0, 180, 216, 1)',
            borderWidth: 2,
            pointBackgroundColor: '#fff',
            pointBorderColor: 'rgba(0, 180, 216, 1)',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
            tension: 0.3,
            fill: true
        }]
    };

    // Line chart configuration
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 6,
                grid: {
                    color: 'rgba(73, 80, 87, 0.2)',
                    lineWidth: 1,
                    drawBorder: true
                },
                ticks: {
                    color: 'rgba(173, 181, 189, 0.8)',
                    font: {
                        size: 11,
                    },
                    stepSize: 1,
                    padding: 8,
                    callback: function(value) {
                        if (value === 0) return '0 (No Data)';
                        if (value === 1) return '1 (Under)';
                        if (value === 2) return '2 (Dev)';
                        if (value === 3) return '3 (Meets)';
                        if (value === 4) return '4 (Exceeds)';
                        if (value === 5) return '5 (Strong)';
                        if (value === 6) return '6 (Top)';
                        return value;
                    }
                },
                title: {
                    display: true,
                    text: 'Rating (0-6 scale)',
                    color: 'rgba(173, 181, 189, 0.8)',
                    font: {
                        size: 12,
                        weight: '500'
                    },
                    padding: {top: 10, bottom: 10}
                }
            },
            x: {
                grid: {
                    color: 'rgba(73, 80, 87, 0.2)',
                    lineWidth: 1,
                    drawBorder: true
                },
                ticks: {
                    color: 'rgba(173, 181, 189, 0.8)',
                    font: {
                        size: 11,
                    },
                    padding: 8
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                titleFont: {
                    size: 13,
                },
                bodyFont: {
                    size: 12,
                },
                padding: 12,
                cornerRadius: 4,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return `Rating: ${context.raw} / 6.00 (${((context.raw/6)*100).toFixed(1)}%)`;
                    }
                }
            },
            annotation: {
                annotations: {
                    line1: {
                        type: 'line',
                        yMin: 3,
                        yMax: 3,
                        borderColor: 'rgba(255, 209, 102, 0.5)',
                        borderWidth: 1,
                        borderDash: [5, 5],
                        label: {
                            content: 'Average',
                            display: true,
                            position: 'end',
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            color: '#ffd166',
                            font: {
                                size: 10,
                            }
                        }
                    }
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeOutQuart'
        }
    };

    // Create the line chart
    const chart = new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: chartOptions
    });

    // Filter change handlers
    document.getElementById('yearFilter').addEventListener('change', applyFilters);
    document.getElementById('monthFilter').addEventListener('change', applyFilters);

    function applyFilters() {
        const year = document.getElementById('yearFilter').value;
        const month = document.getElementById('monthFilter').value;
        window.location.href = `?year=${year}&month=${month}`;
    }

    // Check for new years periodically (once a month)
    setInterval(updateYearFilterOptions, 30 * 24 * 60 * 60 * 1000); // 30 days
    

    // Monthly Performance Chart Implementation
    const initializeMonthlyChart = () => {
        const monthlyCtx = document.getElementById('monthlyPerformanceChart');
        if (!monthlyCtx) {
            console.error("Monthly performance chart canvas not found");
            return;
        }

        // Get year range (2020 to current year + 1 if Q4)
        const getYearRange = () => {
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1;
            const isQ4 = currentMonth >= 10;
            
            return {
                startYear: 2020,
                endYear: isQ4 ? currentYear + 1 : currentYear,
                currentYear: currentYear
            };
        };

        // Prepare data structure
        const { startYear, endYear, currentYear } = getYearRange();
        const allMonthlyData = {};
        
        // Initialize with all possible months
        for (let year = startYear; year <= endYear; year++) {
            allMonthlyData[year] = {};
            for (let month = 1; month <= 12; month++) {
                const monthKey = `${year}-${month.toString().padStart(2, '0')}`;
                allMonthlyData[year][monthKey] = 0; // Default to 0
            }
        }
        
        // Populate with actual data from PHP
        <?php 
        foreach ($monthlyData as $month => $data) {
            $date = DateTime::createFromFormat('Y-m', $month);
            $year = $date->format('Y');
            $monthNum = $date->format('m');
            $score = $data['score'] ?? 0;
            echo "if (allMonthlyData['$year']) allMonthlyData['$year']['$year-$monthNum'] = $score;";
        }
        ?>
        
        console.log("Monthly performance data:", allMonthlyData);

        // Prepare chart data based on filter
        const prepareChartData = (yearFilter) => {
            const { startYear, endYear } = getYearRange();
            const labels = [];
            const data = [];
            
            if (yearFilter === 'all') {
                // All years data
                for (let year = startYear; year <= endYear; year++) {
                    for (let month = 1; month <= 12; month++) {
                        const monthKey = `${year}-${month.toString().padStart(2, '0')}`;
                        const date = new Date(year, month - 1);
                        labels.push(date.toLocaleString('default', { 
                            month: 'short', 
                            year: yearFilter === 'all' ? '2-digit' : undefined 
                        }));
                        data.push(allMonthlyData[year]?.[monthKey] ?? 0);
                    }
                }
            } else {
                // Specific year data
                const yearData = allMonthlyData[yearFilter] || {};
                for (let month = 1; month <= 12; month++) {
                    const monthKey = `${yearFilter}-${month.toString().padStart(2, '0')}`;
                    const date = new Date(yearFilter, month - 1);
                    labels.push(date.toLocaleString('default', { month: 'short' }));
                    data.push(yearData[monthKey] ?? 0);
                }
            }
            
            return { labels, data };
        };

        // Initialize chart
        const initialData = prepareChartData(currentYear);
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: initialData.labels,
                datasets: [{
                    label: 'Monthly Performance Score',
                    data: initialData.data,
                    borderColor: '#3a86ff',
                    backgroundColor: 'rgba(58, 134, 255, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#3a86ff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 0,
                        max: 6,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                const ratings = [
                                    '0 (No Data)',
                                    '1 (Under)',
                                    '2 (Dev)',
                                    '3 (Meets)',
                                    '4 (Exceeds)',
                                    '5 (Strong)',
                                    '6 (Top)'
                                ];
                                return ratings[value] || value;
                            }
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                const score = context.parsed.y;
                                if (score === 0) return 'No data available';
                                
                                const performanceLevels = [
                                    '',
                                    'Underperforming',
                                    'Development Needed',
                                    'Meets Expectations',
                                    'Exceeds Expectations',
                                    'Strong Performance',
                                    'Outstanding Performance'
                                ];
                                
                                const level = performanceLevels[Math.floor(score)] || '';
                                return `Score: ${score.toFixed(2)}${level ? ` (${level})` : ''}`;
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            boxWidth: 12,
                            padding: 20,
                            usePointStyle: true,
                        }
                    }
                }
            }
        });

        // Filter change handler
        document.getElementById('historyYearFilter').addEventListener('change', function() {
            const yearFilter = this.value;
            const newData = prepareChartData(yearFilter);
            
            monthlyChart.data.labels = newData.labels;
            monthlyChart.data.datasets[0].data = newData.data;
            monthlyChart.update();
        });

        // Update year filter options periodically
        const updateYearFilterOptions = () => {
            const yearFilter = document.getElementById('historyYearFilter');
            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth() + 1;
            const isQ4 = currentMonth >= 10;
            const endYear = isQ4 ? currentYear + 1 : currentYear;
            
            const lastOption = yearFilter.options[yearFilter.options.length - 1];
            const lastYear = lastOption.value === 'all' ? 
                parseInt(yearFilter.options[yearFilter.options.length - 2].value) : 
                parseInt(lastOption.value);
                
            if (endYear > lastYear) {
                for (let year = lastYear + 1; year <= endYear; year++) {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    yearFilter.insertBefore(option, lastOption);
                }
            }
        };

        setInterval(updateYearFilterOptions, 30 * 24 * 60 * 60 * 1000); // Monthly check
    };

    // Initialize the chart
    initializeMonthlyChart();


    // Export data functionality
    document.getElementById('exportData')?.addEventListener('click', function() {
        // Show loading modal
        $('#loadingModal').modal('show');
        
        // Simulate processing delay
        setTimeout(function() {
            // Create CSV content
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Category,Rating,Percentile\n";
            csvContent += `Quality of Work,${<?php echo htmlspecialchars(number_format($evaluation['avg_quality'], 2)); ?>},${<?php echo htmlspecialchars(number_format(($evaluation['avg_quality'] / 6) * 100, 1)); ?>}\n`;
            csvContent += `Communication Skills,${<?php echo htmlspecialchars(number_format($evaluation['avg_communication_skills'], 2)); ?>},${<?php echo htmlspecialchars(number_format(($evaluation['avg_communication_skills'] / 6) * 100, 1)); ?>}\n`;
            csvContent += `Teamwork,${<?php echo htmlspecialchars(number_format($evaluation['avg_teamwork'], 2)); ?>},${<?php echo htmlspecialchars(number_format(($evaluation['avg_teamwork'] / 6) * 100, 1)); ?>}\n`;
            csvContent += `Punctuality,${<?php echo htmlspecialchars(number_format($evaluation['avg_punctuality'], 2)); ?>},${<?php echo htmlspecialchars(number_format(($evaluation['avg_punctuality'] / 6) * 100, 1)); ?>}\n`;
            csvContent += `Initiative,${<?php echo htmlspecialchars(number_format($evaluation['avg_initiative'], 2)); ?>},${<?php echo htmlspecialchars(number_format(($evaluation['avg_initiative'] / 6) * 100, 1)); ?>}\n`;
            csvContent += `Overall Average,${<?php echo htmlspecialchars(number_format($overallAverage, 2)); ?>},${<?php echo htmlspecialchars(number_format(($overallAverage / 6) * 100, 1)); ?>}\n`;
            
            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "evaluation_data_<?php echo date('Y-m-d'); ?>.csv");
            document.body.appendChild(link);
            
            // Trigger download
            link.click();
            
            // Hide loading modal
            $('#loadingModal').modal('hide');
        }, 1000);
    });
});
</script>
</body>
</html>

