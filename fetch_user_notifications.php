<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Return error if no user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in', 'notifications' => [], 'unread_count' => 0]);
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch notifications where user_id matches the logged-in user
$query = "SELECT n.*, r.ID as RES_ID, a.ID as ANN_ID, a.CONTENT as ANNOUNCEMENT_CONTENT
          FROM notification n
          LEFT JOIN reservation r ON n.RESERVATION_ID = r.ID
          LEFT JOIN announcement a ON n.ANNOUNCEMENT_ID = a.ID
          WHERE n.USER_ID = ?
          ORDER BY n.CREATED_AT DESC
          LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    // Format the announcement message to include excerpt of content
    if (!empty($row['ANNOUNCEMENT_ID']) && !empty($row['ANNOUNCEMENT_CONTENT'])) {
        $content = $row['ANNOUNCEMENT_CONTENT'];
        $excerpt = strlen($content) > 50 ? substr($content, 0, 50) . '...' : $content;
        $row['MESSAGE'] = "New announcement: " . $excerpt;
    }
    $notifications[] = $row;
}

// Get count of unread notifications
$query = "SELECT COUNT(*) as unread FROM notification WHERE USER_ID = ? AND IS_READ = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$unreadCount = $row['unread'];

// Return notifications as JSON
echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $unreadCount
]);
?>
