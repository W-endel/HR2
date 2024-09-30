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
$sql = "SELECT a_id, password FROM admin_register WHERE email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $inputEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $adminId = $result->fetch_assoc();
    
    // Verify the password
    if (password_verify($inputPassword, $adminId['password'])) {
        // Start session and store admin ID
        $_SESSION['a_id'] = $adminId['a_id']; // Set admin ID here
        $stmt->close();
        $conn->close();
        header("Location: ../main/index.php");
        exit();
    } else {
        $error = urlencode("Invalid username or password!");
        $stmt->close();
        $conn->close();
        header("Location: ../main/adminlogin.php?error=$error");
        exit();
    }
} else {
    $error = urlencode("Invalid username or password!");
    $stmt->close();
    $conn->close();
    header("Location: ../main/adminlogin.php?error=$error");
    exit();
}
?>
