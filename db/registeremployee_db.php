<?php
session_start();

include '../db/db_conn.php';
include '../phpqrcode/qrlib.php';  // Include the QR code library

// Get form inputs
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$role = "Employee"; // Static role value as requested
$department = $_POST['department'];
$position = $_POST['position'];

// Password validation
if ($password !== $confirm_password) {
    echo "<script>alert('Passwords do not match. Please try again.'); window.history.back();</script>";
    exit();
}

// Hash the password before storing it
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if the email already exists in the database
$email_check_query = "SELECT * FROM employee_register WHERE email = ?";
$stmt = $conn->prepare($email_check_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<script>alert('Email already exists. Please use a different email.'); window.history.back();</script>";
    exit();
}

// Insert data into the employee_register table
$insert_query = "INSERT INTO employee_register (firstname, lastname, email, password, role, department, position) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("sssssss", $firstname, $lastname, $email, $hashed_password, $role, $department, $position);

if ($stmt->execute()) {
    // Get the last inserted employee ID for QR code generation
    $employeeId = $stmt->insert_id;

    // Define QR code content (using employee ID or email)
    $codeContents = 'Employee ID: ' . $employeeId . ' | Email: ' . $email;

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

    echo "<script>alert('Employee account created successfully!'); window.location.href='../admin/employee.php';</script>";
} else {
    echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
}

// Close the prepared statement and connection
$stmt->close();
$conn->close();
?>
