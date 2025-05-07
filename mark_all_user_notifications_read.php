<?php
session_start();
require 'db.php';

// Return error if no user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

// Update all unread notifications to read for this user
$query = "UPDATE notification SET IS_READ = 1 WHERE USER_ID = ? AND IS_READ = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$success = $stmt->execute();

// Return success status
echo json_encode(['success' => $success]);
?>
