<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../db/db_conn.php';

if (!$conn) {
    die(json_encode(['error' => 'Database connection failed']));
}

try {
    // Fetch all attendance data
    $sql = "
        SELECT 
            attendance_date AS date,
            employee_name AS name,
            status
        FROM attendance_log
        ORDER BY attendance_date ASC
    ";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $attendanceData = [];
    while ($row = $result->fetch_assoc()) {
        $attendanceData[] = [
            'date' => $row['date'],
            'name' => $row['name'],
            'status' => $row['status']
        ];
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($attendanceData);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>