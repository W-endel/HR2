<?php
session_start();

include '../db/db_conn.php';

// Get employee ID from request
$id = isset($_POST['id']) ? trim($_POST['id']) : '';

// Basic validation
if (empty($id)) {
    echo json_encode(['error' => 'Employee ID is required.']);
    exit();
}

// Prepare and execute SQL statement for deleting
$sql = "DELETE FROM employee_register WHERE id=?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Employee deleted successfully!']);
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
