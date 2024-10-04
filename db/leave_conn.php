<?php
session_start();
include 'db_conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Check if the employee is logged in
    if (!isset($_SESSION['e_id'])) {
        die("Error: User not logged in.");
    }

    // Get the employee ID from the session
    $employeeId = $_SESSION['e_id'];

    // Collect form data
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $leaveType = $_POST['leave_type'];
    $reason = $_POST['reason'];

    // Validate form data (check for empty fields)
    if (empty($startDate) || empty($endDate) || empty($leaveType) || empty($reason)) {
        die("Error: Please fill in all required fields.");
    }

    // Check if the end date is after the start date
    if (strtotime($endDate) < strtotime($startDate)) {
        die("Error: End date cannot be earlier than start date.");
    }

    // Prepare the SQL query to insert the leave request
    $sql = "INSERT INTO leave_requests (e_id, start_date, end_date, leave_type, reason, status)
            VALUES (?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters and execute the query
        $stmt->bind_param('issss', $employeeId, $startDate, $endDate, $leaveType, $reason);
        $stmt->execute();

        // Check if the insertion was successful
        if ($stmt->affected_rows > 0) {
            echo "Leave request submitted successfully.";
        } else {
            echo "Error: Failed to submit leave request.";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error: Failed to prepare SQL query: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
} else {
    die("Error: Invalid request method.");
}
?>
