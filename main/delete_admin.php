<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";        
$password = "";            
$dbname = "hr2";           

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error] );
    exit();
}

// Get employee ID from request
$id = isset($_POST['id']) ? trim($_POST['id']) : '';

// Basic validation
if (empty($id)) {
    echo json_encode(['error' => 'Admin ID is required.']);
    exit();
}

// Prepare and execute SQL statement for deleting
$sql = "DELETE FROM registeradmin_db WHERE id=?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Admin deleted successfully!']);
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
