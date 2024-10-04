<?php
session_start();
include '../db/db_conn.php';

// Ensure the user is logged in
if (!isset($_SESSION['a_id'])) {
    die("Error: You must be logged in.");
}

// Fetch employee ID from the session
$e_id = $_SESSION['e_id'];

// Check if there is a search term
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare SQL query with search criteria
$sql = "
    SELECT lr.*, e.firstname, e.lastname 
    FROM leave_requests lr
    JOIN employee_register e ON lr.e_id = e.e_id
    WHERE (e.firstname LIKE ? OR e.lastname LIKE ? OR e.e_id LIKE ? 
    OR lr.leave_id LIKE ? OR lr.leave_type LIKE ? OR lr.start_date LIKE ? 
    OR lr.end_date LIKE ? OR lr.status LIKE ? OR lr.created_at LIKE ?)
    ORDER BY lr.created_at ASC";

// Prepare statement
$stmt = $conn->prepare($sql);

// Create wildcard search pattern
$searchPattern = '%' . $searchTerm . '%';

// Bind search term to all the placeholders
$stmt->bind_param('sssssssss', $searchPattern, $searchPattern, $searchPattern, 
                  $searchPattern, $searchPattern, $searchPattern, 
                  $searchPattern, $searchPattern, $searchPattern);

// Execute the query
$stmt->execute();

// Fetch the result
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet" />
</head>
<body class="bg-dark">
    <div class="container">
        <h2 class="text-center mt-5 text-light">Leave History</h2>
        
        <!-- Search Form -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by Employee Name, Leave ID, etc." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary bg-light" type="submit">Search</button>
                </div>
            </div>
        </form>
    
    <div class="border-radius-lg overflow-hidden">
        <table class="table table-bordered border mt-3 text-center text-light .rounded">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Leave ID</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Date of Request</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['e_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($row['leave_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td class="bold-status <?php 
    if (htmlspecialchars($row['status']) === 'Approved') {
        echo 'text-success';
    } elseif (htmlspecialchars($row['status']) === 'Denied') {
        echo 'text-danger';
    } elseif (htmlspecialchars($row['status']) === 'Pending') {
        echo 'text-warning';
    } ?>">
    <?php echo htmlspecialchars($row['status']); ?>
</td>

                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No leave history found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
        <div class="text-center mb-5">
            <a href="../main/index.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>
