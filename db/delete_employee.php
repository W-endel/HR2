<?php
session_start();

include '../db/db_conn.php';

// Get employee ID from request
$employeeId = isset($_POST['e_id']) ? trim($_POST['e_id']) : '';

// Basic validation
if (empty($employeeId)) {
    echo json_encode(['error' => 'Employee ID is required.']);
    exit();
}

// Prepare and execute SQL statement for deleting related records from user_activity
$sql = "DELETE FROM user_activity WHERE user_id=?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed for user_activity delete: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $employeeId);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Error deleting user activity: ' . $stmt->error]);
    exit();
}

// Prepare and execute SQL statement for deleting the employee record
$sql = "DELETE FROM employee_register WHERE e_id=?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed for employee delete: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $employeeId);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Employee and related data deleted successfully!']);
} else {
    echo json_encode(['error' => 'Error deleting employee: ' . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
