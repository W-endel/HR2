<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../db/db_conn.php'; // Include your database connection file

// Check for a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Get input data and validate
    if (isset($data['date']) && isset($data['description'])) {
        $date = $data['date'];
        $description = $data['description'];

        // Check if the date already exists
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM non_working_days WHERE date = ?");
        $checkStmt->bind_param("s", $date);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'This date already exists.']);
            exit;
        }

        // Prepare and execute the SQL query
        $stmt = $conn->prepare("INSERT INTO non_working_days (date, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $date, $description);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Non-working day added.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error adding non-working day: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Date and description are required.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
