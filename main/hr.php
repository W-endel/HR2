<?php
session_start();

// Include the database connection
include '../db/db_conn.php'; 

// Check if admin ID is set in the session
if (!isset($_SESSION['a_id'])) {
    die("Admin ID is not set. Please log in.");
}

// Fetch employee records where role is 'employee' and department is 'finance'
$sql = "SELECT e_id, firstname, lastname, department, role, position FROM employee_register WHERE role = 'employee' AND department = 'Human Resources Department'";
$result = $conn->query($sql);

// Fetch evaluations for this admin
$adminId = $_SESSION['a_id'];
$evaluatedEmployees = [];
$evalSql = "SELECT e_id FROM admin_evaluations WHERE a_id = $adminId";
$evalResult = $conn->query($evalSql);
if ($evalResult->num_rows > 0) {
    while ($row = $evalResult->fetch_assoc()) {
        $evaluatedEmployees[] = $row['e_id'];
    }
}

// Check if any records are found
$employees = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
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
    <link rel="stylesheet" href="../css/finance.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
<body class="bg-secondary">
    <h2 class="text-light">Human Resources Department Employee Evaluation</h2>
        <div class="container">
        <table class="table table-striped table-hover text-dark">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Position</th>
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
                            <td><?php echo htmlspecialchars($employee['role']); ?></td>
                            <td>
                                <button class="eval-btn" 
                                    onclick="evaluateEmployee(<?php echo $employee['e_id']; ?>, '<?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']); ?>', '<?php echo htmlspecialchars($employee['position']); ?>')"
                                    <?php echo in_array($employee['e_id'], $evaluatedEmployees) ? 'disabled' : ''; ?>>
                                    <?php echo in_array($employee['e_id'], $evaluatedEmployees) ? 'Evaluated' : 'Evaluate'; ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No employees found for evaluation in Operations Department.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Evaluation Modal -->
    <div class="modal" id="evaluationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 id="employeeDetails"></h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="a_id" value="<?php echo $_SESSION['a_id']; ?>">
                    <div id="questions"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitEvaluation()">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentEmployeeId;
        let currentEmployeeName;  
        let currentEmployeePosition; 

        function evaluateEmployee(employeeId, employeeName, employeePosition) {
            currentEmployeeId = employeeId; 
            currentEmployeeName = employeeName; 
            currentEmployeePosition = employeePosition; 

            const employeeDetails = `<strong>Name: ${employeeName} <br> Position: ${employeePosition}</strong>`;
            document.getElementById('employeeDetails').innerHTML = employeeDetails;

            const categories = {
                "Quality of Work": [
                    "How do you rate the employee's attention to detail?",
                    "How would you evaluate the accuracy of the employee's work?",
                    "Does the employee consistently meet job requirements?"
                ],
                "Communication Skills": [
                    "Does the employee communicate clearly?",
                    "Is the employee responsive to feedback?",
                    "How effectively does the employee listen to others?"
                ],
                "Teamwork": [
                    "Does the employee collaborate well with others?",
                    "How well does the employee contribute to team success?",
                    "Does the employee support team members when needed?"
                ],
                "Punctuality": [
                    "Is the employee consistent in meeting deadlines?",
                    "How often does the employee arrive on time?",
                    "Does the employee respect others' time?"
                ],
                "Initiative": [
                    "Does the employee take initiative without being asked?",
                    "How frequently does the employee suggest improvements?",
                    "Does the employee show a proactive attitude?"
                ]
            };

            const questionsDiv = document.getElementById('questions');
            questionsDiv.innerHTML = ''; 

            // Start the table structure
            let tableHtml = `
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Question</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>`;

            // Loop through categories and questions to add them into the table
            for (const [category, questions] of Object.entries(categories)) {
                questions.forEach((question, index) => {
                    const questionName = `${category.replace(/\s/g, '')}q${index}`; // Unique name per question
                    tableHtml += `
                    <tr>
                        <td>${index === 0 ? category : ''}</td>
                        <td>${question}</td>
                        <td>
                            <div class="star-rating">
                                ${[5, 4, 3, 2, 1].map(value => `
                                    <input type="radio" name="${questionName}" value="${value}" id="${questionName}star${value}">
                                    <label for="${questionName}star${value}">&#9733;</label>
                                `).join('')}
                            </div>
                        </td>
                    </tr>`;
                });
            }

            // Close the table structure
            tableHtml += `
                </tbody>
            </table>`;

            questionsDiv.innerHTML = tableHtml;

            $('#evaluationModal').modal('show'); 
        }

        function calculateAverage(category, evaluations) {
            const categoryEvaluations = evaluations.filter(evaluation => evaluation.question.startsWith(category.replace(/\s/g, '')));

            if (categoryEvaluations.length === 0) {
                return 0; 
            }

            const total = categoryEvaluations.reduce((sum, evaluation) => sum + parseInt(evaluation.rating), 0);
            return total / categoryEvaluations.length;
        }

        function submitEvaluation() {
            const evaluations = [];
            const questionsDiv = document.getElementById('questions');

            questionsDiv.querySelectorAll('input[type="radio"]:checked').forEach(input => {
                evaluations.push({
                    question: input.name,  
                    rating: input.value    
                });
            });

            const totalQuestions = questionsDiv.querySelectorAll('.star-rating').length;

            if (evaluations.length !== totalQuestions) {
                alert('Please complete the evaluation before submitting.');
                return;
            }

            const categoryAverages = {
                QualityOfWork: calculateAverage('Quality of Work', evaluations),
                CommunicationSkills: calculateAverage('Communication Skills', evaluations),
                Teamwork: calculateAverage('Teamwork', evaluations),
                Punctuality: calculateAverage('Punctuality', evaluations),
                Initiative: calculateAverage('Initiative', evaluations)
            };

            console.log('Category Averages:', categoryAverages);

            // Get admin ID from the hidden input field
            const adminId = document.getElementById('a_id').value;

            $.ajax({
                type: 'POST',
                url: '../db/submit_hr.php',
                data: {
                    employeeId: currentEmployeeId,
                    employeeName: currentEmployeeName,
                    employeePosition: currentEmployeePosition,
                    categoryAverages: categoryAverages,
                    adminId: adminId
                },
                success: function (response) {
                    console.log(response); 
                    if (response === 'You have already evaluated this employee.') {
                        alert(response); 
                    } else {
                        $('#evaluationModal').modal('hide');
                        alert('Evaluation submitted successfully!');
                    }
                },
                error: function (err) {
                    console.error(err);
                    alert('An error occurred while submitting the evaluation.');
                }
            });
        }
    </script>
</body>

</html>