<?php
session_start();

if (!isset($_SESSION['a_id'])) {
    header("Location: ../main/adminlogin.php");
    exit();
}

include '../db/db_conn.php';

// Fetch all leave requests (always fetch this for displaying the table)
$sql = "SELECT lr.leave_id, e.e_id, e.firstname, e.lastname, e.available_leaves, lr.start_date, lr.end_date, lr.leave_type, lr.status
        FROM leave_requests lr
        JOIN employee_register e ON lr.e_id = e.e_id
        WHERE lr.status = 'Pending'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Fetch holidays from the database
$holidays = [];
$holiday_sql = "SELECT date FROM non_working_days";
$holiday_stmt = $conn->prepare($holiday_sql);
$holiday_stmt->execute();
$holiday_result = $holiday_stmt->get_result();
while ($holiday_row = $holiday_result->fetch_assoc()) {
    $holidays[] = $holiday_row['date']; // Store holidays in an array
}

// Handle approve/deny actions
if (isset($_GET['leave_id']) && isset($_GET['status'])) {
    $leave_id = $_GET['leave_id'];
    $status = $_GET['status'];

    // Fetch the specific leave request
    $sql = "SELECT lr.leave_id, e.e_id, e.firstname, e.lastname, e.available_leaves, lr.start_date, lr.end_date, lr.leave_type, lr.status
            FROM leave_requests lr
            JOIN employee_register e ON lr.e_id = e.e_id
            WHERE lr.leave_id = ?";
    $action_stmt = $conn->prepare($sql);
    $action_stmt->bind_param("i", $leave_id);
    $action_stmt->execute();
    $action_result = $action_stmt->get_result();

    if ($action_result->num_rows > 0) {
        $row = $action_result->fetch_assoc();
        $available_leaves = $row['available_leaves'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];

        // Calculate total leave days excluding Sundays and holidays
        $leave_days = 0;
        $current_date = strtotime($start_date);

        while ($current_date <= strtotime($end_date)) {
            $current_date_str = date('Y-m-d', $current_date);
            // Check if the current day is not a Sunday (0 = Sunday) and not a holiday
            if (date('N', $current_date) != 7 && !in_array($current_date_str, $holidays)) {
                $leave_days++; // Count this day as a leave day
            }
            $current_date = strtotime("+1 day", $current_date); // Move to the next day
        }

        if ($status === 'approve') {
            // Check if the employee has enough leave balance
            if ($leave_days > $available_leaves) {
                // Not enough leave balance
                header("Location: leave_status.php?status=insufficient_balance");
                exit();
            } else {
                // Update leave request status and subtract days from available leave balance
                $new_balance = $available_leaves - $leave_days;

                // Update the leave request status to 'Approved' and decrease available leaves in employee_register
                $update_sql = "UPDATE leave_requests lr 
                               JOIN employee_register e ON lr.e_id = e.e_id
                               SET lr.status = 'Approved', e.available_leaves = ?
                               WHERE lr.leave_id = ?";
                               
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $new_balance, $leave_id);

                if ($update_stmt->execute()) {
                    header("Location: leave_status.php?status=success");
                } else {
                    error_log("Error updating leave balance: " . $conn->error); // Log the error
                    header("Location: leave_status.php?status=error");
                }
            }
        } elseif ($status === 'deny') {
            // Deny the leave request
            $deny_sql = "UPDATE leave_requests SET status = 'Denied' WHERE leave_id = ?";
            $deny_stmt = $conn->prepare($deny_sql);
            $deny_stmt->bind_param("i", $leave_id);

            if ($deny_stmt->execute()) {
                header("Location: leave_status.php?status=success");
            } else {
                header("Location: leave_status.php?status=error");
            }
        }
    } else {
        // Leave request not found
        header("Location: leave_status.php?status=not_exist");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</head>
<body class="bg-dark">
    <div class="container">
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'success'): ?>
                <div class="alert alert-success" role="alert">
                    Leave request status updated successfully.
                </div>
            <?php elseif ($_GET['status'] === 'error'): ?>
                <div class="alert alert-danger" role="alert">
                    Error updating leave request status. Please try again.
                </div>
            <?php elseif ($_GET['status'] === 'not_exist'): ?>
                <div class="alert alert-warning" role="alert">
                    The leave request ID does not exist or could not be found.
                </div>
            <?php elseif ($_GET['status'] === 'insufficient_balance'): ?>
                <div class="alert alert-warning" role="alert">
                    Insufficient leave balance. The request cannot be approved.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <h2 class="text-center mt-5 text-light">All Leave Requests</h2>
        <table class="table table-bordered mt-3 text-center text-light">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Leave ID</th>
                    <th>Leave Balance</th>
                    <th>Duration of Leave</th>
                    <th>Deduction Leave</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                            // Calculate total leave days excluding Sundays and holidays
                            $leave_days = 0;
                            $current_date = strtotime($row['start_date']);
                            $end_date = strtotime($row['end_date']);
                        
                            while ($current_date <= $end_date) {
                            $current_date_str = date('Y-m-d', $current_date);
                            // Check if the current day is not a Sunday (0 = Sunday) and not a holiday
                            if (date('N', $current_date) != 7 && !in_array($current_date_str, $holidays)) {
                                $leave_days++; // Count this day as a leave day
                            }
                            $current_date = strtotime("+1 day", $current_date); // Move to the next day
                            }
                        ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['e_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($row['leave_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['available_leaves']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date'] . ' / ' . $row['end_date']); ?></td>
                        <td><?php echo htmlspecialchars($leave_days); ?></td>
                        <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                        <td class="<?php echo htmlspecialchars($row['status']) === 'Approved' ? 'text-success font-weight-bold' : (htmlspecialchars($row['status']) === 'Denied' ? 'text-danger font-weight-bold' : 
                                             (htmlspecialchars($row['status']) === 'Pending' ? 'text-warning font-weight-bold' : ''));?>">
                                   <?php echo htmlspecialchars($row['status']); ?>
                        </td>
                        <td>
                                <div class="col row no-gutters">
                                    <button class="btn btn-success btn-block" onclick="confirmAction('approve', <?php echo $row['leave_id']; ?>)">Approve</button>
                                </div>
                                <div class="col row no-gutters">
                                    <button class="btn btn-danger btn-block" onclick="confirmAction('deny', <?php echo $row['leave_id']; ?>)">Deny</button>
                                </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No leave requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="text-center mb-5">
            <a href="../main/index.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>

    <script>
        function confirmAction(action, requestId) {
            let confirmation = confirm(`Are you sure you want to ${action} this leave request?`);
            if (confirmation) {
                window.location.href = `leave_status.php?leave_id=${requestId}&status=${action}`;
            }
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>
