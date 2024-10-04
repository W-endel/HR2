<?php
session_start();
include '../db/db_conn.php';

// Ensure the admin is logged in
if (!isset($_SESSION['a_id'])) {
    die("Error: You must be logged in as admin.");
}

// Fetch all leave requests
$sql = "SELECT lr.leave_id, e.e_id, e.firstname, e.lastname, lr.start_date, lr.end_date, lr.leave_type, lr.reason, lr.status
        FROM leave_requests lr
        JOIN employee_register e ON lr.e_id = e.e_id
        WHERE lr.status = 'Pending'";


$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
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
            <?php endif; ?>
        <?php endif; ?>

        <h2 class="text-center mt-5 text-light">All Leave Requests</h2>
        <table class="table table-bordered mt-3 text-center text-light">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Leave ID</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Type of Leave</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['e_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($row['leave_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td class="<?php echo htmlspecialchars($row['status']) === 'Approved' ? 'text-success font-weight-bold' : (htmlspecialchars($row['status']) === 'Denied' ? 'text-danger font-weight-bold' : 
                                             (htmlspecialchars($row['status']) === 'Pending' ? 'text-warning font-weight-bold' : ''));?>">
                                   <?php echo htmlspecialchars($row['status']); ?>
                        </td>
                        <td>
                           <div class="row no-gutters">
                                <div class="col">
                                    <button class="btn btn-success btn-block" onclick="confirmAction('approve', <?php echo $row['leave_id']; ?>)">Approve</button>
                                </div>
                                <div class="col-auto">
                                <!-- Add a spacer using an empty div with a specified width -->
                                <div style="width: 10px;"></div>
                                </div>
                                <div class="col">
                                    <button class="btn btn-danger btn-block" onclick="confirmAction('deny', <?php echo $row['leave_id']; ?>)">Deny</button>
                                </div>
                           </div>
                        </td>

                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No leave requests found.</td>
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
            window.location.href = `../main/updateleave_status.php?leave_id=${requestId}&status=${action}`;
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
