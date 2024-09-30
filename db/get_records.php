<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hr2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// SQL query to get today's records
$sql = "SELECT * FROM timesheet_db WHERE DATE(timestamp) = CURDATE() ORDER BY timestamp DESC";
$result = $conn->query($sql);

$records = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

// Return records as JSON
echo json_encode(['success' => true, 'records' => $records]);

// Close connection
$conn->close();
?>
