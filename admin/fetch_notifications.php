<?php
session_start();
require '../db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Default limit for notifications to fetch
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Fetch notifications for admin (all notifications that don't have a specific user_id)
$query = "SELECT * FROM notification 
          WHERE USER_ID IS NULL OR USER_ID = 0
          ORDER BY CREATED_AT DESC 
          LIMIT ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Count unread notifications
$unreadQuery = "SELECT COUNT(*) as count FROM notification 
                WHERE (USER_ID IS NULL OR USER_ID = 0) AND IS_READ = 0";
$unreadResult = $conn->query($unreadQuery);
$unreadRow = $unreadResult->fetch_assoc();
$unreadCount = $unreadRow['count'];

// Return the data
header('Content-Type: application/json');
echo json_encode([
    'notifications' => $notifications,
    'unread_count' => (int)$unreadCount
]);

$stmt->close();
$conn->close();
?>
