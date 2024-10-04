<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-dark">

<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'hr2');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch employee data for each department
function getDepartmentData($conn, $department) {
    // Get total employees in the department
    $employeeQuery = "SELECT COUNT(*) as total FROM employee_register WHERE department = '$department'";
    $employeeResult = $conn->query($employeeQuery);
    $totalEmployees = $employeeResult->fetch_assoc()['total'];

    // Get evaluated (assuming evaluation status is 'evaluated')
    $evaluatedQuery = "SELECT COUNT(*) as evaluated FROM admin_evaluations WHERE department = '$department'";
    $evaluatedResult = $conn->query($evaluatedQuery);
    $evaluated = $evaluatedResult->fetch_assoc()['evaluated'];
    $pendingEmployees = $totalEmployees - $evaluated;

    return array('total' => $totalEmployees, 'evaluated' => $evaluated, 'pending' => $pendingEmployees);
}

// Fetch data for different departments
$financeData = getDepartmentData($conn, 'Finance Department');
$hrData = getDepartmentData($conn, 'Human Resource Department');
$operationsData = getDepartmentData($conn, 'Operations Department');
$riskData = getDepartmentData($conn, 'Risk Department');
$marketingData = getDepartmentData($conn, 'Marketing Department');
$itData = getDepartmentData($conn, 'IT Department');
?>

<main>
    <div class="container-fluid px-3">
        <h1 class="mt-5 text-center text-light">Admin Evaluation Dashboard</h1>
        <div class="row justify-content-center">
            <div class="col-xl-4 col-md-6 mt-5">
                <div class="card mb-4">
                    <div class="card-body bg-primary text-center">
                        <a href="../main/finance.php" class="btn card-button text-dark font-weight-bold bg-light border border-dark w-100">Finance Department</a>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between bg-dark border border-light department-toggle" data-target="#financeInfo">
                        <div class="small text-warning">Click to View Details</div>
                        <div class="small text-warning">
                            <i class="fas fa-angle-down"></i>
                        </div>
                    </div>
                    <div id="financeInfo" class="collapse bg-light text-dark">
                        <div class="card-body bg-dark">
                        <h5 class="text-center mb-4 text-light">Finance Evaluation Status</h5>
                            <div class="text-center mb-3">
                                <span class="badge badge-primary mx-1">Total Employees: <?php echo $financeData['total']; ?></span>
                                <span class="badge badge-success mx-1">Evaluated: <?php echo $financeData['evaluated']; ?></span>
                                <span class="badge badge-warning mx-1">Pending Evaluation: <?php echo $financeData['pending']; ?></span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success font-weight-bold" role="progressbar" 
                                     style="width: <?php echo ($financeData['evaluated'] / $financeData['total']) * 100; ?>%;" 
                                    aria-valuenow="<?php echo $financeData['evaluated']; ?>" 
                                    aria-valuemin="0" 
                                    aria-valuemax="<?php echo $financeData['total']; ?>">
                                     Evaluated (<?php echo $financeData['evaluated']; ?>)
                                </div>
                                <div class="progress-bar bg-warning text-dark font-weight-bold" role="progressbar" 
                                    style="width: <?php echo ($financeData['pending'] / $financeData['total']) * 100; ?>%;" 
                                    aria-valuenow="<?php echo $financeData['pending']; ?>" 
                                    aria-valuemin="0" 
                                    aria-valuemax="<?php echo $financeData['total']; ?>">
                                    Pending (<?php echo $financeData['pending']; ?>)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mt-5">
                <div class="card mb-4">
                    <div class="card-body bg-primary text-center">
                        <a href="../main/hr.php" class="btn card-button text-dark font-weight-bold bg-light border border-dark w-100">Human Resource Department</a>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between bg-dark border border-light department-toggle" data-target="#hrInfo">
                        <div class="small text-warning">Click to View Details</div>
                        <div class="small text-warning">
                            <i class="fas fa-angle-down"></i>
                        </div>
                    </div>
                    <div id="hrInfo" class="collapse bg-light text-dark">
                        <div class="card-body">
                            <p>Total Employees: <?php echo $hrData['total']; ?></p>
                            <p>Evaluated: <?php echo $hrData['evaluated']; ?></p>
                            <p>Pending Evaluations: <?php echo $hrData['pending']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mt-5">
                <div class="card mb-4">
                    <div class="card-body bg-primary text-center">
                        <a href="../main/operations.php" class="btn card-button text-dark font-weight-bold bg-light border border-dark w-100">Operations Department</a>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between bg-dark border border-light department-toggle" data-target="#operationsInfo">
                        <div class="small text-warning">Click to View Details</div>
                        <div class="small text-warning">
                            <i class="fas fa-angle-down"></i>
                        </div>
                    </div>
                    <div id="operationsInfo" class="collapse bg-light text-dark">
                        <div class="card-body">
                            <p>Total Employees: <?php echo $operationsData['total']; ?></p>
                            <p>Evaluated: <?php echo $operationsData['evaluated']; ?></p>
                            <p>Pending Evaluations: <?php echo $operationsData['pending']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mt-5">
                <div class="card mb-4">
                    <div class="card-body bg-primary text-center">
                        <a href="../main/risk.php" class="btn card-button text-dark font-weight-bold bg-light border border-dark w-100">Risk Department</a>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between bg-dark border border-light department-toggle" data-target="#riskInfo">
                        <div class="small text-warning">Click to View Details</div>
                        <div class="small text-warning">
                            <i class="fas fa-angle-down"></i>
                        </div>
                    </div>
                    <div id="riskInfo" class="collapse bg-light text-dark">
                        <div class="card-body">
                            <p>Total Employees: <?php echo $riskData['total']; ?></p>
                            <p>Evaluated: <?php echo $riskData['evaluated']; ?></p>
                            <p>Pending Evaluations: <?php echo $riskData['pending']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mt-5">
                <div class="card mb-4">
                    <div class="card-body bg-primary text-center">
                        <a href="../main/marketing.php" class="btn card-button text-dark font-weight-bold bg-light border border-dark w-100">Marketing Department</a>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between bg-dark border border-light department-toggle" data-target="#marketingInfo">
                        <div class="small text-warning">Click to View Details</div>
                        <div class="small text-warning">
                            <i class="fas fa-angle-down"></i>
                        </div>
                    </div>
                    <div id="marketingInfo" class="collapse bg-light text-dark">
                        <div class="card-body">
                            <p>Total Employees: <?php echo $marketingData['total']; ?></p>
                            <p>Evaluated: <?php echo $marketingData['evaluated']; ?></p>
                            <p>Pending Evaluations: <?php echo $marketingData['pending']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mt-5">
                <div class="card mb-4">
                    <div class="card-body bg-primary text-center">
                        <a href="../main/it.php" class="btn card-button text-dark font-weight-bold bg-light border border-dark w-100">IT Department</a>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between bg-dark border border-light department-toggle" data-target="#itInfo">
                        <div class="small text-warning">Click to View Details</div>
                        <div class="small text-warning">
                            <i class="fas fa-angle-down"></i>
                        </div>
                    </div>
                    <div id="itInfo" class="collapse bg-light text-dark">
                        <div class="card-body">
                            <p>Total Employees: <?php echo $itData['total']; ?></p>
                            <p>Evaluated: <?php echo $itData['evaluated']; ?></p>
                            <p>Pending Evaluations: <?php echo $itData['pending']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // Add event listener to all elements with class "department-toggle"
    document.querySelectorAll('.department-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const target = this.getAttribute('data-target');
            const icon = this.querySelector('i');

            // Toggle the collapse
            $(target).collapse('toggle');

            // Toggle the icon classes between angle-down and angle-up
            icon.classList.toggle('fa-angle-down');
            icon.classList.toggle('fa-angle-up');
        });
    });
</script>
</body>
</html>
