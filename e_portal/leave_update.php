<?php
session_start();
include '../db/db_conn.php';

// Check if the user is logged in and get the employee's ID from the session
if (!isset($_SESSION['e_id'])) {
    die("Error: User is not logged in.");
}

$employeeId = $_SESSION['e_id'];

// Fetch the employee information from the database
$sql = "SELECT firstname, lastname, role, department FROM employee_register WHERE e_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    die("Error: Employee information not found.");
}

// Close the database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-dark text-warning">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 border border-light rounded p-4 mt-5">
                <form id="leave-request-form" action="../db/leave_conn.php" method="POST">
                    <h2 class="text-center text-light">Leave Request Form</h2>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="first_name" class="text-light">First Name:</label>
                                <input type="text" class="form-control text-dark" id="first_name" name="first_name" value="<?php echo htmlspecialchars($employee['firstname']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="text-light">Last Name:</label>
                                <input type="text" class="form-control text-dark" id="last_name" name="last_name" value="<?php echo htmlspecialchars($employee['lastname']); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="role" class="text-light">Role:</label>
                                <input type="text" class="form-control text-dark" id="role" name="role" value="<?php echo htmlspecialchars($employee['role']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="department" class="text-light">Department:</label>
                                <input type="text" class="form-control text-dark" id="department" name="department" value="<?php echo htmlspecialchars($employee['department']); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="start_date" class="text-light">Start Date:</label>
                                <input type="date" class="form-control text-dark" id="start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="text-light">End Date:</label>
                                <input type="date" class="form-control text-dark" id="end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="leave_type" class="text-light">Type of Leave:</label>
                        <select class="form-control text-dark" id="leave_type" name="leave_type" required>
                            <option value="">Select a leave type</option>
                            <option value="Annual Leave">Annual Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Family Leave">Family Leave</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason" class="text-light">Reason for Leave:</label>
                        <textarea class="form-control text-dark" id="reason" name="reason" placeholder="Enter the reason for your leave" required></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-dark border border-light">Submit Leave</button>
                    </div>
                    <div class="text-center mt-3">
                        <a class="btn btn-dark border border-light" href="../e_portal/leave_balance.php">Check Remaining Leave</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
</body>
</html>
