<?php
include '../../db/db_conn.php';

// Fetch aggregated data for all months
$sql = "
    SELECT 
        a.employee_id, 
        IFNULL(l.total_leave_days, 0) AS total_leave_days, 
        IFNULL(at.attendance_rate, 0) AS attendance_rate, 
        IFNULL(e.performance_score, 0) AS performance_score
    FROM 
        (SELECT DISTINCT employee_id FROM attendance_log) a
    LEFT JOIN 
        (SELECT employee_id, SUM(DATEDIFF(end_date, start_date) + 1) AS total_leave_days 
         FROM leave_requests 
         WHERE status = 'approved' 
         GROUP BY employee_id) l 
        ON a.employee_id = l.employee_id
    LEFT JOIN 
        (SELECT employee_id, COUNT(CASE WHEN status = 'Present' THEN 1 END) * 1.0 / COUNT(*) AS attendance_rate 
         FROM attendance_log 
         GROUP BY employee_id) at 
        ON a.employee_id = at.employee_id
    LEFT JOIN 
        (SELECT employee_id, (quality + communication_skills + punctuality + initiative + teamwork) / 5.0 AS performance_score 
         FROM admin_evaluations 
         WHERE (employee_id, evaluated_at) IN 
            (SELECT employee_id, MAX(evaluated_at) FROM admin_evaluations 
             GROUP BY employee_id)
        ) e 
        ON a.employee_id = e.employee_id;
";

// Execute the query
$result = $conn->query($sql);

if (!$result) {
    error_log("Database query failed: " . $conn->error);
    echo json_encode(['error' => 'Database query failed', 'message' => $conn->error]);
    $conn->close();
    exit();
}

// Fetch and process the result
$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'employee_id' => $row['employee_id'],
            'total_leave_days' => (int)$row['total_leave_days'],  // Ensure numbers are properly cast
            'performance_score' => (float)$row['performance_score'],
            'attendance_rate' => (float)$row['attendance_rate']
        ];
    }
} else {
    error_log("No data found in the database.");
    echo json_encode(['error' => 'No data found', 'message' => 'No data available for employees']);
    $conn->close();
    exit();
}

// Debugging: Output the data to check its structure (can be removed in production)
error_log(print_r($data, true));

// Set the response header to JSON
header('Content-Type: application/json');
echo json_encode($data);

// Close the connection
$conn->close();
?>
