<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../db/db_conn.php';

if (!$conn) {
    die(json_encode(['error' => 'Database connection failed']));
}

try {
    // Fetch evaluation data grouped by year and month
    $sql = "
        SELECT 
            YEAR(evaluated_at) AS year,
            MONTH(evaluated_at) AS month,
            AVG(quality) AS quality,
            AVG(communication_skills) AS communication_skills,
            AVG(punctuality) AS punctuality,
            AVG(initiative) AS initiative,
            AVG(teamwork) AS teamwork
        FROM evaluations
        GROUP BY YEAR(evaluated_at), MONTH(evaluated_at)
        UNION ALL
        SELECT 
            YEAR(evaluated_at) AS year,
            MONTH(evaluated_at) AS month,
            AVG(quality) AS quality,
            AVG(communication_skills) AS communication_skills,
            AVG(punctuality) AS punctuality,
            AVG(initiative) AS initiative,
            AVG(teamwork) AS teamwork
        FROM ptp_evaluations
        GROUP BY YEAR(evaluated_at), MONTH(evaluated_at)
    ";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $monthlyAverages = [];
    while ($row = $result->fetch_assoc()) {
        $year = $row['year'];
        $month = $row['month'];
        $monthName = date('F Y', mktime(0, 0, 0, $month, 1, $year)); // Format as "Month YYYY"

        if (!isset($monthlyAverages[$monthName])) {
            $monthlyAverages[$monthName] = [
                'quality' => 0,
                'communication_skills' => 0,
                'punctuality' => 0,
                'initiative' => 0,
                'teamwork' => 0,
                'count' => 0,
            ];
        }
        $monthlyAverages[$monthName]['quality'] += $row['quality'];
        $monthlyAverages[$monthName]['communication_skills'] += $row['communication_skills'];
        $monthlyAverages[$monthName]['punctuality'] += $row['punctuality'];
        $monthlyAverages[$monthName]['initiative'] += $row['initiative'];
        $monthlyAverages[$monthName]['teamwork'] += $row['teamwork'];
        $monthlyAverages[$monthName]['count'] += 1;
    }

    // Calculate the final averages for each month
    foreach ($monthlyAverages as $monthName => $data) {
        $count = $data['count'];
        if ($count > 0) { // Ensure we don't divide by zero
            $monthlyAverages[$monthName]['quality'] /= $count;
            $monthlyAverages[$monthName]['communication_skills'] /= $count;
            $monthlyAverages[$monthName]['punctuality'] /= $count;
            $monthlyAverages[$monthName]['initiative'] /= $count;
            $monthlyAverages[$monthName]['teamwork'] /= $count;
        } else {
            // If no data, set averages to 0
            $monthlyAverages[$monthName]['quality'] = 0;
            $monthlyAverages[$monthName]['communication_skills'] = 0;
            $monthlyAverages[$monthName]['punctuality'] = 0;
            $monthlyAverages[$monthName]['initiative'] = 0;
            $monthlyAverages[$monthName]['teamwork'] = 0;
        }
        unset($monthlyAverages[$monthName]['count']); // Remove the count field
    }

    // Sort the months chronologically
    uksort($monthlyAverages, function ($a, $b) {
        return strtotime($a) - strtotime($b);
    });

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($monthlyAverages);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>