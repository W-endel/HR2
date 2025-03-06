<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../db/db_conn.php';

// Check if the connection was successful
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Fetch all approved leaves with employee details
$sql = "
    SELECT 
        lr.e_id, 
        er.firstname, 
        er.lastname, 
        lr.start_date, 
        lr.end_date, 
        lr.leave_type 
    FROM 
        leave_requests lr
    JOIN 
        employee_register er 
    ON 
        lr.e_id = er.e_id 
    WHERE 
        lr.status = 'Approved'
";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['firstname'] . ' ' . $row['lastname'],
        'start' => $row['start_date'],
        'end' => date('Y-m-d', strtotime($row['end_date'] . ' +1 day')), // Add 1 day to include the end date
        'backgroundColor' => '#ffc107', // Customize the event color
        'borderColor' => '#ffc107',
        'textColor' => '#000',
        'extendedProps' => [
            'e_id' => $row['e_id'],
            'leave_type' => $row['leave_type']
        ]
    ];
}

// If no events were found, return a response with an empty array
if (empty($events)) {
    echo json_encode(['message' => 'No events found']);
} else {
    echo json_encode($events);
}

$conn->close();
?>