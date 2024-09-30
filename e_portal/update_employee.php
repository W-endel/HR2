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
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Get form data with validation
$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$firstName = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
$lastName = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
$Email = isset($_POST['email']) ? trim($_POST['email']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';
$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';

// Basic validation
if (empty($id) || empty($firstName) || empty($lastName) || empty($Email) || empty($role) || empty($phone_number) || empty($address)) {
    echo json_encode(['error' => 'All fields are required.']);
    exit();
}

// Prepare and execute SQL statement for updating
$sql = "UPDATE employee_register SET firstname=?, lastname=?, email=?, role=?, phone_number=?, address=? WHERE id=?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ssssssi", $firstName, $lastName, $Email, $role, $phone_number, $address, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Employee information updated successfully!']);
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
