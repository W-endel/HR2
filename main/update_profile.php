<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";        
$password = "";            
$dbname = "hr2";           

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user ID from session
$userId = $_SESSION['user_id'];

// Prepare and bind
$stmt = $conn->prepare("UPDATE admin_register SET firstname = ?, middlename = ?, lastname = ?, birthdate = ?, email = ?, phone_number = ?, address = ? WHERE id = ?");
$stmt->bind_param("sssssssi", $firstname, $middlename, $lastname, $birthdate, $email, $phone_number, $address, $userId);

// Get form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $middlename =$_POST['middlename'];
    $lastname = $_POST['lastname'];
    $birthdate = $_POST['birthdate'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $profilePicture = $_FILES['profile_picture'];
        $targetDir = "../uploads/"; // Ensure this directory exists and is writable
        $targetFile = $targetDir . basename($profilePicture['name']);
        
        // Move the uploaded file
        if (move_uploaded_file($profilePicture['tmp_name'], $targetFile)) {
            // Prepare to update the profile picture path in the database
            $stmtPic = $conn->prepare("UPDATE registeradmin_db SET pfp = ? WHERE id = ?");
            $stmtPic->bind_param("si", $targetFile, $userId);
            $stmtPic->execute();
            $stmtPic->close();
        } else {
            echo "Error uploading profile picture.";
        }
    }

    // Execute the statement to update user information
    if ($stmt->execute()) {
        // Set success message
        $_SESSION['update_success'] = "Your information has been updated successfully.";
        header("Location: ../main/profile.php");
        exit();
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>
