<?php
session_start();

// Include database connection
include '../../db/db_conn.php';

// Redirect if not logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../../login.php");
    exit();
}

$employeeId = $_SESSION['employee_id'];

// Fetch employee information
$sql = "SELECT employee_id, first_name, middle_name, last_name, birthdate, email, role, position, department, phone_number, address, pfp 
        FROM employee_register 
        WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$employeeInfo = $result->fetch_assoc();

// Fetch evaluation data
$sql = "SELECT 
            AVG(quality) AS avg_quality, 
            AVG(communication_skills) AS avg_communication_skills, 
            AVG(teamwork) AS avg_teamwork, 
            AVG(punctuality) AS avg_punctuality, 
            AVG(initiative) AS avg_initiative,
            COUNT(*) AS total_evaluations 
        FROM evaluations 
        WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $evaluation = $result->fetch_assoc();
} else {
    echo "No evaluations found.";
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Result | HR2</title>
    <link href="../../css/styles.css" rel="stylesheet" />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="sb-nav-fixed bg-black">
   <?php include 'navbar.php'; ?>
    <div id="layoutSidenav">
    <?php include 'sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main class="bg-black">
                <div class="container-fluid position-relative px-4">
                    <h1 class="big mb-4 text-light">Evaluation Rating</h1>
                    <div class="container" id="calendarContainer" 
                    style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1050; 
                        width: 80%; height: 80%; display: none;">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="calendar" class="p-2"></div>
                            </div>
                        </div>
                    </div>   
                    <div class="card bg-black text-light py-4">
                        
                        <div class="bg-dark bordered">
                            <canvas id="evaluationChart" width="700" height="400"></canvas>
                        </div>

                        <table class="table table-bordered mt-3 text-light table-dark">
                            <thead>
                                <tr class="text-center">
                                    <th>Category</th>
                                    <th>Average Rating</th>
                                </tr>
                            </thead>
                            <tbody class="text-start">
                                <tr>
                                    <td>Quality of Work</td>
                                    <td><?php echo htmlspecialchars(number_format($evaluation['avg_quality'], 2)); ?></td>
                                </tr>
                                <tr>
                                    <td>Communication Skills</td>
                                    <td><?php echo htmlspecialchars(number_format($evaluation['avg_communication_skills'], 2)); ?></td>
                                </tr>
                                <tr>
                                    <td>Teamwork</td>
                                    <td><?php echo htmlspecialchars(number_format($evaluation['avg_teamwork'], 2)); ?></td>
                                </tr>
                                <tr>
                                    <td>Punctuality</td>
                                    <td><?php echo htmlspecialchars(number_format($evaluation['avg_punctuality'], 2)); ?></td>
                                </tr>
                                <tr>
                                    <td>Initiative</td>
                                    <td><?php echo htmlspecialchars(number_format($evaluation['avg_initiative'], 2)); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content bg-dark text-light">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to log out?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn border-secondary text-light" data-bs-dismiss="modal">Cancel</button>
                                    <form action="../../employee/logout.php" method="POST">
                                        <button type="submit" class="btn btn-danger">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>  
                </div>
            </main>
            <?php include 'footer.php'; ?>
        </div>
    </div>
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-transparent border-0">
                    <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                        <!-- Bouncing coin spinner -->
                        <div class="coin-spinner"></div>
                        <div class="mt-3 text-light fw-bold">Please wait...</div>
                    </div>
                </div>
            </div>
        </div>
    <script>

    //EVALUATION
    const ctx = document.getElementById('evaluationChart').getContext('2d');
    const chartData = {
        labels: [
            'Quality of Work', 
            'Communication Skills', 
            'Teamwork', 
            'Punctuality', 
            'Initiative'
        ],
        datasets: [{
            label: 'Average Ratings',
            data: [
                <?php echo htmlspecialchars(number_format($evaluation['avg_quality'], 2)); ?>,
                <?php echo htmlspecialchars(number_format($evaluation['avg_communication_skills'], 2)); ?>,
                <?php echo htmlspecialchars(number_format($evaluation['avg_teamwork'], 2)); ?>,
                <?php echo htmlspecialchars(number_format($evaluation['avg_punctuality'], 2)); ?>,
                <?php echo htmlspecialchars(number_format($evaluation['avg_initiative'], 2)); ?>
            ],
            backgroundColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 2)',
                'rgba(75, 192, 192, 2)',
                'rgba(75, 192, 192, 2)',
                'rgba(75, 192, 192, 2)',
                'rgba(75, 192, 192, 2)'
            ],
            borderWidth: 1
        }]
    };

    const myChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 6,
                    grid: {
                        color: '#495057', // Customize Y-axis grid line color
                        lineWidth: 1 // Customize grid line thickness
                    },
                    ticks: {
                        color: '#6c757d', // Customize Y-axis label color
                        font: {
                            size: 14 // Customize Y-axis label font size
                        }
                    }
                },
                x: {
                    grid: {
                        color: '#495057', // Customize X-axis grid line color
                        lineWidth: 1 // Customize grid line thickness
                    },
                    ticks: {
                        color: '#f8f9fa', // Customize X-axis label color
                        font: {
                            size: 14 // Customize X-axis label font size
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#f8f9fa', // Customize legend label color
                        font: {
                            size: 14 // Customize legend label font size
                        }
                    }
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 10,
                    bottom: 10
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
        //EVALUATION END
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../../js/employee.js"></script>
</body>
</html>
