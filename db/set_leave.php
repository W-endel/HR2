<?php
session_start();
include '../db/db_conn.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the admin is logged in
if (!isset($_SESSION['a_id'])) {
    die("Error: You must be logged in as admin.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get leave allocations from the form
    $employee_leaves = $_POST['employee_leaves'];
    $employeeId = $_POST['employee_id']; // Get specific employee ID

    // Validate leave input
    if (!is_numeric($employee_leaves) || $employee_leaves <= 0 || $employee_leaves > 20) {
        die("Error: Leave days must be a number between 1 and 20.");
    }

    // Validate employee selection
    if (empty($employeeId) || $employeeId == '') {
        die("Error: You must select a valid employee.");
    }

    // Function to get current leave balance for a specific employee
    function get_current_leave_balance($conn, $employeeId) {
        $sql = "SELECT available_leaves FROM employee_register WHERE e_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param('i', $employeeId);
        $stmt->execute();
        $stmt->bind_result($available_leaves);
        $stmt->fetch();
        $stmt->close();
        return $available_leaves;
    }

    // Get the admin's ID from session
    $admin_id = $_SESSION['a_id'];

    // Get the admin's name
    $admin_query = "SELECT firstname, lastname FROM admin_register WHERE a_id = ?";
    $admin_stmt = $conn->prepare($admin_query);
    $admin_stmt->bind_param("i", $admin_id);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();
    $admin = $admin_result->fetch_assoc();
    $admin_name = $admin['firstname'] . ' ' . $admin['lastname'];

    // Capture admin's IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Prepare activity log details
    $action_type = "Leave Allocation Updated";
    $affected_feature = "Leave Information";
    $details = '';

    if ($employeeId == 'all') {
        // Update for all employees
        $update_sql = "UPDATE employee_register SET available_leaves = available_leaves + ? WHERE role = 'Employee'";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt === false) {
            die("Error preparing update statement: " . $conn->error);
        }
        $update_stmt->bind_param('i', $employee_leaves);
        if (!$update_stmt->execute()) {
            die("Error executing update statement: " . $update_stmt->error);
        }
        $details = "Leave allocations updated for all employees. Leave added: $employee_leaves days.";
    } else {
        // Update for a specific employee
        $current_leaves = get_current_leave_balance($conn, $employeeId);
        $new_leave_total = $current_leaves + $employee_leaves;

        $update_sql = "UPDATE employee_register SET available_leaves = ? WHERE e_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt === false) {
            die("Error preparing update statement: " . $conn->error);
        }
        $update_stmt->bind_param('ii', $new_leave_total, $employeeId);
        if (!$update_stmt->execute()) {
            die("Error executing update statement: " . $update_stmt->error);
        }
        $details = "Employee ID: $employeeId leave updated. Leave added: $employee_leaves days. Total available leaves: $new_leave_total days.";
    }

    // Insert the log entry into activity_logs table
    $log_query = "INSERT INTO activity_logs (admin_id, admin_name, action_type, affected_feature, details, ip_address) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bind_param("isssss", $admin_id, $admin_name, $action_type, $affected_feature, $details, $ip_address);
    $log_stmt->execute();

    // Respond to the user with success message
    echo "<div class='alert alert-success'>$details</div>";
}
?>
