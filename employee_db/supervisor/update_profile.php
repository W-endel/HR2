<?php
session_start();

include '../../db/db_conn.php';

// Fetch user ID from session
$employeeId = $_SESSION['e_id'];

// Prepare and bind
$stmt = $conn->prepare("UPDATE employee_register SET firstname = ?, middlename = ?, lastname = ?, birthdate = ?, email = ?, phone_number = ?, address = ? WHERE e_id = ?");
$stmt->bind_param("sssssssi", $firstname, $middlename, $lastname, $birthdate, $email, $phone_number, $address, $employeeId);

// Get form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $middlename =$_POST['middlename'];
    $lastname = $_POST['lastname'];
    $birthdate = $_POST['birthdate'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];

    // Execute the statement to update user information
    if ($stmt->execute()) {
        // Set success message
        $_SESSION['update_success'] = "Your information has been updated successfully.";
        header("Location: ../../employee/supervisor/profile.php");
        exit();
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>
