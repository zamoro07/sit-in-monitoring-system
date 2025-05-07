<?php
session_start();
require '../db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Check if notification_id is provided
if (!isset($_POST['notification_id']) || empty($_POST['notification_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Notification ID is required']);
    exit;
}

$notificationId = (int)$_POST['notification_id'];

// Update notification status to read
$query = "UPDATE notification SET IS_READ = 1 WHERE NOTIF_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $notificationId);
$success = $stmt->execute();

// Return response
header('Content-Type: application/json');
echo json_encode([
    'success' => $success,
    'notification_id' => $notificationId
]);

$stmt->close();
$conn->close();
?>
