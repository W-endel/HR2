<?php
session_start();
include '../../db/db_conn.php'; // Include your database connection

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $employeeId = $data['employee_id']; // The employee being reacted to
    $adminId = $_SESSION['employee_id']; // The logged-in employee's ID
    $reaction = $data['reaction'];

    // Validate input
    if (empty($reaction) || empty($employeeId)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit();
    }

    // Validate reaction (only allow specific reactions)
    $allowedReactions = [" Like", "❤️ Heart", "😎 Awesome", "😮 Wow"];
    if (!in_array($reaction, $allowedReactions)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid reaction']);
        exit();
    }

    // Check if the admin has already reacted to this employee
    $checkSql = "SELECT id FROM employee_reactions WHERE employee_id = ? AND admin_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $employeeId, $adminId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Admin has already reacted, update the existing reaction
        $updateSql = "UPDATE employee_reactions SET reaction = ? WHERE employee_id = ? AND admin_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssi", $reaction, $employeeId, $adminId);

        if ($updateStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Reaction updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update reaction']);
        }

        $updateStmt->close();
    } else {
        // Admin has not reacted yet, insert a new reaction
        $insertSql = "INSERT INTO employee_reactions (employee_id, admin_id, reaction) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sis", $employeeId, $adminId, $reaction);

        if ($insertStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Reaction saved successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save reaction']);
        }

        $insertStmt->close();
    }

    $checkStmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
