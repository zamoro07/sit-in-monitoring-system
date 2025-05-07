<?php
session_start();
require '../db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Update all unread notifications to read for admin
$query = "UPDATE notification SET IS_READ = 1 
          WHERE (USER_ID IS NULL OR USER_ID = 0) AND IS_READ = 0";
$result = $conn->query($query);

// Return response
header('Content-Type: application/json');
echo json_encode([
    'success' => $result ? true : false,
    'affected_rows' => $conn->affected_rows
]);

$conn->close();
?>
