<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Check if notification ID was provided
if (!isset($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'error' => 'No notification ID provided']);
    exit;
}

$notificationId = $_POST['notification_id'];

// If admin is logged in, they can mark any notification as read
// If user is logged in, they can only mark their own notifications as read
$query = "UPDATE notification SET IS_READ = 1 WHERE NOTIF_ID = ?";
if (isset($_SESSION['user_id']) && !isset($_SESSION['admin'])) {
    $query .= " AND USER_ID = ?";
}

$stmt = $conn->prepare($query);
if (isset($_SESSION['user_id']) && !isset($_SESSION['admin'])) {
    $stmt->bind_param("ii", $notificationId, $_SESSION['user_id']);
} else {
    $stmt->bind_param("i", $notificationId);
}

$success = $stmt->execute();

// Return success status
echo json_encode(['success' => $success]);
?>
