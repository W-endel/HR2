<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";        
$password = "";            
$dbname = "hr2";           

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch employee records where role is 'employee' and department is 'finance'
$sql = "SELECT firstname, lastname, department, role, position FROM registeradmin_db WHERE role = 'employee' AND department = 'IT Department'";
$result = $conn->query($sql);

// Check if any records are found
if ($result->num_rows > 0) {
    // Output employee data
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
} else {
    echo "No employee records found for Finance Department.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Evaluation Table</title>
    <link rel="stylesheet" href="../main/eww.css">
</head>
<body>
    <h2>Finance Department Employee Evaluation</h2>
    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Position</th>
                <th>Department</th>
                <th>Role</th>
                <th>Evaluation</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($employees)): ?>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($employee['position']); ?></td>
                        <td><?php echo htmlspecialchars($employee['department']); ?></td>
                        <td><?php echo htmlspecialchars($employee['role']); ?></td>
                        <td><button class="eval-btn" onclick="evaluate('<?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?>')">Evaluate</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No employees found for evaluation in Finance Department.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script src="../main/eval_btn.js"></script>
</body>
</html>

