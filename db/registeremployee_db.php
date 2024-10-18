<?php
session_start();

include '../db/db_conn.php';
include '../phpqrcode/qrlib.php';  // Include the QR code library

// Get form data with validation
$firstName = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
$lastName = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
$Email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';
$position = isset($_POST['position']) ? trim($_POST['position']) : '';
$department = isset($_POST['department']) ? trim($_POST['department']) : '';

// Basic validation
if (empty($firstName) || empty($lastName) || empty($Email) || empty($password) || empty($confirm_password) || empty($role) || empty($position) || empty($department)) {
    echo json_encode(['error' => 'All fields are required.']);
    exit();
}

// Check if passwords match
if ($password !== $confirm_password) {
    echo json_encode(['error' => 'Passwords do not match.']);
    exit();
}

// Check if the email already exists
$sql = "SELECT COUNT(*) FROM employee_register WHERE email = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("s", $Email);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    echo json_encode(['error' => 'This email is already registered.']);
    exit();
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare and execute SQL statement to insert employee data
$sql = "INSERT INTO employee_register (firstname, lastname, email, password, role, position, department) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("sssssss", $firstName, $lastName, $Email, $hashedPassword, $role, $position, $department);

if ($stmt->execute()) {
    // Get the last inserted employee ID for QR code generation
    $employeeId = $stmt->insert_id;

    // Define QR code content (using employee ID or email)
    $codeContents = 'Employee ID: ' . $employeeId . ' | Email: ' . $Email;

    // Define the directory for QR codes
    $tempDir = "C:/xampp/htdocs/HR2/QR/";

    // Create the directory if it doesn't exist
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Define the file name and path for the QR code
    $fileName = 'employee_' . $employeeId . '.png';
    $pngAbsoluteFilePath = $tempDir . $fileName;

    // Generate the QR code
    QRcode::png($codeContents, $pngAbsoluteFilePath);

    // Update the employee's record with the QR code path
    $qrCodePath = 'QR/' . $fileName;
    $updateSql = "UPDATE employee_register SET qr_code_path = ? WHERE e_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $qrCodePath, $employeeId);
    $updateStmt->execute();
    $updateStmt->close();

    echo json_encode(['success' => 'Registration successful! QR code generated.']);
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
