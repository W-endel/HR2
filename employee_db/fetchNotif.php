<?php
session_start();
include '../../db/db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$employeeId = $_SESSION['employee_id'];

// Fetch notifications
$notificationQuery = "SELECT * FROM notifications 
                     WHERE employee_id = ? 
                     ORDER BY created_at DESC 
                     LIMIT 10";
$stmt = $conn->prepare($notificationQuery);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// Count unread notifications
$unreadQuery = "SELECT COUNT(*) as unread_count FROM notifications 
               WHERE employee_id = ? AND status = 'unread'";
$unreadStmt = $conn->prepare($unreadQuery);
$unreadStmt->bind_param("i", $employeeId);
$unreadStmt->execute();
$unreadResult = $unreadStmt->get_result()->fetch_assoc();

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unreadCount' => $unreadResult['unread_count']
]);