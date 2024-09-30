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
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$inputEmail = $_POST['email'];
$inputPassword = $_POST['password'];

// Prepare and execute SQL statement
$sql = "SELECT e_id, password FROM employee_register WHERE email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Improved error handling
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $inputEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employeeId = $result->fetch_assoc();
    
    // Verify the password
    if (password_verify($inputPassword, $employeeId['password'])) {
        // Start session and store user ID
        $_SESSION['e_id'] = $employeeId['e_id'];
        $stmt->close();
        $conn->close();
        header("Location: ../e_portal/employee_dashboard.php");
        exit();
    } else {
        // Redirect to login page with URL-encoded error message
        $error = urlencode("Invalid username or password!.");
        $stmt->close();
        $conn->close();
        header("Location: ../e_portal/employee_login.php?error=$error");
        exit();
    }
} else {
    // Redirect to login page with URL-encoded error message
    $error = urlencode("Invalid username or password!.");
    $stmt->close();
    $conn->close();
    header("Location: ../e_portal/employee_login.php?error=$error");
    exit();
}
?>
