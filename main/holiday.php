<?php
// Connect to your database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hr2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query the non_working_days table
$sql = "SELECT date, description FROM non_working_days";
$result = $conn->query($sql);

// Prepare data for JSON
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['description'],
        'start' => $row['date'],
        'color' => 'red'  // or any color you prefer
    ];
}

// Return JSON response
echo json_encode($events);
$conn->close();
?>
