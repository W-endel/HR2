<?php
$host = 'localhost'; // Change this if your database is hosted elsewhere
$dbname = 'hr2';
$user = 'root'; // Your MySQL username
$pass = ''; // Your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sales = $_POST["sales"];
    $customer_satisfaction = $_POST["customer_satisfaction"];
    $attendance = $_POST["attendance"];
    $project_completion = $_POST["project_completion"];
    $team_collaboration = $_POST["team_collaboration"];
    $learning_development = $_POST["learning_development"];

    // Rule-based AI system class here (same as your provided code)

    $ai = new PerformanceAI();
    $result = $ai->evaluatePerformance([
        'sales' => $sales,
        'customer_satisfaction' => $customer_satisfaction,
        'attendance' => $attendance,
        'project_completion' => $project_completion,
        'team_collaboration' => $team_collaboration,
        'learning_development' => $learning_development
    ]);

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO performance_db (sales, customer_satisfaction, attendance, project_completion, team_collaboration, learning_development, total_score, overall_rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $sales,
        $customer_satisfaction,
        $attendance,
        $project_completion,
        $team_collaboration,
        $learning_development,
        $result['total_score'],
        $result['overall_rating']
    ]);

    // Display result
    echo "<div class='mt-4 card result-card'>";
    echo "<div class='card-body'>";
    echo "<h4 class='card-title'>Performance Evaluation Result</h4>";
    echo "<div class='row'>";
    foreach ($result['scores'] as $metric => $data) {
        echo "<div class='col-md-4 mb-3'>";
        echo "<h5>" . ucwords(str_replace('_', ' ', $metric)) . "</h5>";
        echo "<p>Raw Score: {$data['raw']}</p>";
        echo "<p>Evaluated Score: {$data['score']}</p>";
        echo "<p>Weighted Score: " . number_format($data['weighted'], 2) . "</p>";
        echo "</div>";
    }
    echo "</div>";
    echo "<hr>";
    echo "<h5>Total Score: " . number_format($result['total_score'], 2) . "</h5>";
    echo "<h4>Overall Performance: <strong>{$result['overall_rating']}</strong></h4>";
    echo "</div>";
    echo "</div>";
}
?>
