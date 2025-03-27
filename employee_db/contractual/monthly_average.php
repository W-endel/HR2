<?php
require_once '../../db/db_conn.php';

$employeeId = $_GET['employee_id'] ?? null;
if (!$employeeId) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

try {
    // Get current year
    $currentYear = date('Y');
    
    // Query to get monthly averages
    $sql = "SELECT 
                DATE_FORMAT(evaluated_at, '%Y-%m') as month,
                AVG((quality + communication_skills + teamwork + punctuality + initiative) / 5) as avg_score
            FROM evaluations
            WHERE employee_id = ?
            AND YEAR(evaluated_at) = ?
            GROUP BY DATE_FORMAT(evaluated_at, '%Y-%m')
            ORDER BY month ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $employeeId, $currentYear);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $monthlyData = [];
    while ($row = $result->fetch_assoc()) {
        $monthlyData[$row['month']] = (float)$row['avg_score'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($monthlyData);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>