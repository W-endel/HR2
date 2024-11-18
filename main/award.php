<?php
// Start session and check admin login
session_start();
if (!isset($_SESSION['a_id'])) {
    header("Location: ../main/adminlogin.php");
    exit();
}

// Include database connection
include '../db/db_conn.php';

// Fetch user info
$adminId = $_SESSION['a_id'];
$sql = "SELECT a_id, firstname, middlename, lastname, birthdate, email, role, position, department, phone_number, address, pfp FROM admin_register WHERE a_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$adminInfo = $result->fetch_assoc();

// Function to fetch top employees based on highest average score for a specific criterion
function getTopEmployeesByCriterion($conn, $criterion, $criterionLabel, $index) {
    // SQL query to fetch the highest average score for each employee
    $sql = "SELECT e.e_id, e.firstname, e.lastname, e.department, e.pfp, 
                   AVG(ae.$criterion) AS avg_score
            FROM employee_register e
            JOIN admin_evaluations ae ON e.e_id = ae.e_id
            GROUP BY e.e_id
            ORDER BY avg_score DESC
            LIMIT 1";  // Select the top employee with the highest average score

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    // Output the awardee's information for each criterion
    echo "<div class='category' id='category-$index' style='display: none;'>";
    echo "<h3 class='text-center mt-4'>$criterionLabel</h3>";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Ensure profile picture is base64-encoded for inline display
            $pfp = base64_encode(file_get_contents($row['pfp'])); // Load image from file system

            echo "<div class='card mb-5' style='max-width: 100%; margin-top: 50px; border: 2px solid #ddd; border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 20px; background-color: #f9f9f9;'>"; // Style for certificate look
            echo "<div class='row no-gutters' style='height: 100%;'>";
            echo "<div class='col-md-4' style='height: 100%;'>";
            echo "<img src='data:image/jpeg;base64,$pfp' class='card-img' alt='Profile Picture' style='height: 100%; object-fit: cover; border-radius: 15px;'>";
            echo "</div>";
            echo "<div class='col-md-8' style='height: 100%; display: flex; flex-direction: column; justify-content: center; padding-left: 20px;'>";
            echo "<div class='card-body' style='height: 100%;'>";
            echo "<h1 class='card-title' style='font-size: 40px; font-weight: bold; color: #333;'>" . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) . "</h1>";
            echo "<p class='card-text fs-5 text-dark'><strong>Department:</strong> " . htmlspecialchars($row['department']) . "</p>";
            echo "<p class='card-text fs-5 text-dark'><strong>$criterionLabel Score:</strong> " . number_format($row['avg_score'], 2) . "</p>";  // Display average score
            echo "<p class='card-text fs-5 text-dark'><strong>Employee ID:</strong> " . htmlspecialchars($row['e_id']) . "</p>";
            echo "</div>"; // End of card-body
            echo "</div>"; // End of col-md-8
            echo "</div>"; // End of row
            echo "</div>"; // End of card
        }
    } else {
        echo "<p class='text-center'>No outstanding employees found for $criterionLabel.</p>";
    }

    echo "</div>"; // End of category
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outstanding Employees</title>
    <link href="../css/styles.css" rel="stylesheet" />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .card {
            border: 2px solid #ddd; 
            border-radius: 15px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            padding: 20px; 
            background-color: #f9f9f9;
        }
        .card-img {
            border-radius: 15px;
        }
        .card-body {
            padding-left: 20px;
        }
        .card-title {
            font-size: 24px; 
            font-weight: bold; 
            color: #333;
        }
        .card-text {
            font-size: 18px;
        }
        .category {
            display: none;
        }
    </style>
</head>
<body class="sb-nav-fixed bg-black"> 
    <div class="container mt-5 text-light">
        <h2 class="text-center mb-5 text-light">Outstanding Employees by Evaluation Criteria</h2>

        <!-- Top Employees for Different Criteria -->
        <?php getTopEmployeesByCriterion($conn, 'quality', 'Quality of Work', 1); ?>
        <?php getTopEmployeesByCriterion($conn, 'communication_skills', 'Communication Skills', 2); ?>
        <?php getTopEmployeesByCriterion($conn, 'teamwork', 'Teamwork', 3); ?>
        <?php getTopEmployeesByCriterion($conn, 'punctuality', 'Punctuality', 4); ?>
        <?php getTopEmployeesByCriterion($conn, 'initiative', 'Initiative', 5); ?>

        <!-- Navigation buttons for manually controlling the categories -->
        <div class="text-center mt-4">
            <button class="btn btn-primary" onclick="showPreviousCategory()">Previous</button>
            <button class="btn btn-primary" onclick="showNextCategory()">Next</button>
        </div>
    </div>

    <script>
        let currentCategoryIndex = 1;
        const totalCategories = 5; // Total number of categories

        function showNextCategory() {
            // Hide the current category
            document.getElementById(`category-${currentCategoryIndex}`).style.display = 'none';

            // Update to the next category index, loop back to 1 if at the end
            currentCategoryIndex = (currentCategoryIndex % totalCategories) + 1;

            // Show the next category
            document.getElementById(`category-${currentCategoryIndex}`).style.display = 'block';
        }

        function showPreviousCategory() {
            // Hide the current category
            document.getElementById(`category-${currentCategoryIndex}`).style.display = 'none';

            // Update to the previous category index, loop back to totalCategories if at the start
            currentCategoryIndex = (currentCategoryIndex - 1) || totalCategories;

            // Show the previous category
            document.getElementById(`category-${currentCategoryIndex}`).style.display = 'block';
        }

        // Start the slideshow, show the first category immediately
        window.onload = function() {
            // Show the first category immediately
            document.getElementById(`category-1`).style.display = 'block';
            
            // Start the slideshow after showing the first category
            setInterval(showNextCategory, 3000); // Change every 3 seconds
        };
    </script>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/admin.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?> 
