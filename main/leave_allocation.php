<?php
session_start();
include '../db/db_conn.php';

// Ensure the admin is logged in
if (!isset($_SESSION['a_id'])) {
    die("Error: You must be logged in as admin.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get leave allocations from the form
    $admin_leaves = $_POST['admin_leaves'];
    $employee_leaves = $_POST['employee_leaves'];

    // Insert or update leave allocations
    $insert_sql = "INSERT INTO leave_allocations (role, leave_days) VALUES (?, ?) 
                   ON DUPLICATE KEY UPDATE leave_days = VALUES(leave_days)";
    
    // Update for Admin
    $insert_stmt = $conn->prepare($insert_sql);
    if ($insert_stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $role = 'Admin';
    $insert_stmt->bind_param('si', $role, $admin_leaves);
    if (!$insert_stmt->execute()) {
        die("Error executing statement: " . $insert_stmt->error);
    }

    // Update for Employee
    $role = 'Employee';
    $insert_stmt->bind_param('si', $role, $employee_leaves);
    if (!$insert_stmt->execute()) {
        die("Error executing statement: " . $insert_stmt->error);
    }

    // Update available_leaves in employee_register based on leave_allocations
    $roles_sql = "SELECT * FROM leave_allocations";
    $roles_result = $conn->query($roles_sql);
    if (!$roles_result) {
        die("Error retrieving roles: " . $conn->error);
    }

    while ($role_row = $roles_result->fetch_assoc()) {
    $role = $role_row['role'];
    $leave_days = $role_row['leave_days'];

    // Update the available_leaves for employees in employee_register
    $update_sql = "UPDATE employee_register SET available_leaves = ? WHERE role = ?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt === false) {
        die("Error preparing update statement for employees: " . $conn->error);
    }
    $update_stmt->bind_param('is', $leave_days, $role);
    if (!$update_stmt->execute()) {
        die("Error executing update statement for employees: " . $update_stmt->error);
    }

    // Update the available_leaves for admins in admin_register
    $update_sql = "UPDATE admin_register SET available_leaves = ? WHERE role = ?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt === false) {
        die("Error preparing update statement for admins: " . $conn->error);
    }
    $update_stmt->bind_param('is', $leave_days, $role);
    if (!$update_stmt->execute()) {
        die("Error executing update statement for admins: " . $update_stmt->error);
    }
}


    echo "<div class='alert alert-success'>Leave allocations updated successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Leave Allocations</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body class="bg-dark">
    <div class="container">
        <h2 class="text-center mt-5 text-light">Set Leave Allocations</h2>
        
        <form method="POST" class="mt-4">
            <div class="form-group">
                <label class="text-light" for="admin_leaves">Leave Days for Admin:</label>
                <input type="number" name="admin_leaves" id="admin_leaves" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="text-light" for="employee_leaves">Leave Days for Employee:</label>
                <input type="number" name="employee_leaves" id="employee_leaves" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Set Allocations</button>
        </form>
        <div class="text-center mb-5">
            <a href="../main/index.php" class="btn btn-secondary mt-4">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
