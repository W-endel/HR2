<?php
session_start();
include '../../db/db_conn.php'; // Include your database connection

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $employeeId = $data['employee_id'];

    // Validate input
    if (empty($employeeId)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit();
    }

    // Fetch comments from the database
    $sql = "SELECT comment, username, created_at FROM employee_comments WHERE employee_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = [];

    while ($row = $result->fetch_assoc()) {
        // Convert the MySQL timestamp to a JavaScript-friendly format (ISO 8601)
        $row['created_at'] = date('c', strtotime($row['created_at'])); // Format: 2023-10-25T12:34:56+00:00
        $comments[] = $row;
    }

    // Fetch the total number of comments
    $countSql = "SELECT COUNT(*) AS total_comments FROM employee_comments WHERE employee_id = ?";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("s", $employeeId);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $countRow = $countResult->fetch_assoc();
    $totalComments = $countRow['total_comments'];

    if ($comments) {
        echo json_encode(['status' => 'success', 'comments' => $comments, 'total_comments' => $totalComments]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No comments found']);
    }

    $stmt->close();
    $countStmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>