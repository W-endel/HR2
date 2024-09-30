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
$sql = "SELECT id, password FROM employee_register WHERE email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Improved error handling
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $inputEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verify the password
    if (password_verify($inputPassword, $user['password'])) {
        // Start session and store user ID
        $_SESSION['user_id'] = $user['id'];
        $stmt->close();
        $conn->close();
        header("Location: ../main/employee_dashboard.php");
        exit();
    } else {
        // Redirect to login page with URL-encoded error message
        $error = urlencode("Invalid username or password!.");
        $stmt->close();
        $conn->close();
        header("Location: ../main/employeelogin.php?error=$error");
        exit();
    }
} else {
    // Redirect to login page with URL-encoded error message
    $error = urlencode("Invalid username or password!.");
    $stmt->close();
    $conn->close();
    header("Location: ../main/login.php?error=$error");
    exit();
}
?>
