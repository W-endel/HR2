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

// Fetch employee records where role is 'employee' and department is 'Marketing Department'
$sql = "SELECT id, firstname, lastname, department, role, position FROM registeradmin_db WHERE role = 'employee' AND department = 'Marketing Department'";
$result = $conn->query($sql);

// Check if any records are found
if ($result->num_rows > 0) {
    // Output employee data
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
} else {
    echo "No employee records found for Marketing Department.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketing Department Employee Evaluation Table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: black;
            padding: 20px;
        }
        h2 {
            margin-bottom: 20px;
        }
        .eval-btn {
            background-color: #007bff;
            color: white;
        }
        .eval-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container text-center text-light">
        <h2>Marketing Department Employee Evaluation</h2>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr class="text-center">
                    <th>Employee ID</th>
                    <th>Full Name</th>
                    <th>Position</th>
                    <th>Role</th>
                    <th>Evaluation</th>
                </tr>
            </thead>
            <tbody class="text-center text-light">
                <?php if (!empty($employees)): ?>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($employee['id']); ?></td>
                            <td><?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($employee['position']); ?></td>
                            <td><?php echo htmlspecialchars($employee['role']); ?></td>
                            <td>
                                <button class="btn eval-btn" onclick="evaluate('<?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?>')">Evaluate</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No employees found for evaluation in Marketing Department.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="../js/eval_btn.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
