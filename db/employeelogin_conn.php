<?php
session_start();

include '../db/db_conn.php';

// Check if form data is set
$inputEmail = isset($_POST['email']) ? trim($_POST['email']) : '';
$inputPassword = isset($_POST['password']) ? trim($_POST['password']) : '';

// Basic validation
if (empty($inputEmail) || empty($inputPassword)) {
    $error = urlencode("Email and password fields are required.");
    header("Location: ../e_portal/employee_login.php?error=$error");
    exit();
}

// Password validation: at least one uppercase letter, one lowercase letter, one number, and one special character
$passwordPattern = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).+$/";
if (!preg_match($passwordPattern, $inputPassword)) {
    $error = urlencode("Password must be at least 8 characters long and contain at least one number, special character, uppercase and lowercase letter.");
    header("Location: ../e_portal/employee_login.php?error=$error");
    exit();
}

// Prepare and execute SQL statement
$sql = "SELECT e_id, password FROM employee_register WHERE email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $inputEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employeeData = $result->fetch_assoc();
    
    // Verify the password
    if (password_verify($inputPassword, $employeeData['password'])) {
        $_SESSION['e_id'] = $employeeData['e_id']; 
        $stmt->close();
        $conn->close();
        header("Location: ../e_portal/employee_dashboard.php"); // Redirect to main dashboard
        exit();
    } else {
        $error = urlencode("Invalid email or password.");
        $stmt->close();
        $conn->close();
        header("Location: ../e_portal/employee_login.php?error=$error");
        exit();
    }
} else {
    $error = urlencode("Invalid email or password.");
    $stmt->close();
    $conn->close();
    header("Location: ../e_portal/employee_login.php?error=$error");
    exit();
}
?>
