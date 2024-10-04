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
    $supervisor_leaves = $_POST['supervisor_leaves'];
    $employee_leaves = $_POST['employee_leaves'];

    // Insert or update leave allocations
    $insert_sql = "INSERT INTO leave_allocations (position, leave_days) VALUES (?, ?) 
                   ON DUPLICATE KEY UPDATE leave_days = VALUES(leave_days)";
    
    // Update for Supervisor
    $insert_stmt = $conn->prepare($insert_sql);
    if ($insert_stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $position = 'Supervisor';
    $insert_stmt->bind_param('si', $position, $supervisor_leaves);
    if (!$insert_stmt->execute()) {
        die("Error executing statement: " . $insert_stmt->error);
    }

    // Update for Employee
    $position = 'Employee';
    $insert_stmt->bind_param('si', $position, $employee_leaves);
    if (!$insert_stmt->execute()) {
        die("Error executing statement: " . $insert_stmt->error);
    }

    // Update available_leaves in employee_register based on leave_allocations
    $positions_sql = "SELECT * FROM leave_allocations";
    $positions_result = $conn->query($positions_sql);
    if (!$positions_result) {
        die("Error retrieving positions: " . $conn->error);
    }

    while ($position_row = $positions_result->fetch_assoc()) {
        $position = $position_row['position'];
        $leave_days = $position_row['leave_days'];

        // Update the available_leaves for employees in this position
        $update_sql = "UPDATE employee_register SET available_leaves = ? WHERE position = ?";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt === false) {
            die("Error preparing update statement: " . $conn->error);
        }
        $update_stmt->bind_param('is', $leave_days, $position);
        if (!$update_stmt->execute()) {
            die("Error executing update statement: " . $update_stmt->error);
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
                <label class="text-light" for="supervisor_leaves">Leave Days for Supervisor:</label>
                <input type="number" name="supervisor_leaves" id="supervisor_leaves" class="form-control" required>
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
