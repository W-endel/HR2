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
        $_SESSION['status_message'] = "User not logged in.";
        header("Location: ../e_portal/leave_request.php"); // Redirect back to form
        exit();
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
        $_SESSION['status_message'] = "Please fill in all required fields.";
        header("Location: ../e_portal/leave_request.php"); // Redirect back to form
        exit();
    }

    // Check if the end date is after the start date
    if (strtotime($endDate) < strtotime($startDate)) {
        $_SESSION['status_message'] = "End date cannot be earlier than start date.";
        header("Location: ../e_portal/leave_request.php"); // Redirect back to form
        exit();
    }

    // Prepare the SQL query to get the available leaves
    $sql = "SELECT available_leaves FROM employee_register WHERE e_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        $availableLeaves = $employee['available_leaves'];

        // Calculate the number of leave days requested
        $startDateObj = new DateTime($startDate);
        $endDateObj = new DateTime($endDate);
        $leaveDaysRequested = $endDateObj->diff($startDateObj)->days + 1; // Include start date

        // Check if the requested leave days exceed available leaves
        if ($leaveDaysRequested > $availableLeaves) {
            $_SESSION['status_message'] = ['type' => 'warning', 'message' => "You do not have enough leave days available. Available leaves: $availableLeaves"];
            header("Location: ../e_portal/leave_request.php"); // Redirect back to form
            exit();
        }
    } else {
        $_SESSION['status_message'] = "Error: Employee not found.";
        header("Location: ../e_portal/leave_request.php"); // Redirect back to form
        exit();
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
            $_SESSION['status_message'] = ['type' => 'success', 'message' => 'Leave request submitted successfully.'];
        } else {
            $_SESSION['status_message'] = ['type' => 'warning', 'message' => 'Error: Failed to submit leave request.'];
        }

        // Close the statement
        $stmt->close();
    } else {
        $_SESSION['status_message'] = ['type' => 'warning', 'message' => 'Error: Failed to prepare SQL query.'] . $conn->error;
    }

    // Close the database connection
    $conn->close();

    // Redirect back to the leave request form
    header("Location: ../e_portal/leave_request.php");
    exit();
} else {
    $_SESSION['status_message'] = "Invalid request method.";
    header("Location: ../e_portal/leave_request.php"); // Redirect back to form
    exit();
}
?>
