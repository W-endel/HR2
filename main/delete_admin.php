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
$adminId = isset($_POST['a_id']) ? trim($_POST['a_id']) : '';

// Basic validation
if (empty($adminId)) {
    echo json_encode(['error' => 'Admin ID is required.']);
    exit();
}

// Prepare and execute SQL statement for deleting
$sql = "DELETE FROM admin_register WHERE a_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $adminId);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Admin deleted successfully!']);
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
