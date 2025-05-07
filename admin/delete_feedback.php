<?php
session_start();
require '../db.php';

// Check if the user is an admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if feedback_id is provided
if (!isset($_POST['feedback_id']) || empty($_POST['feedback_id'])) {
    echo json_encode(['success' => false, 'message' => 'Feedback ID is required']);
    exit;
}

$feedback_id = intval($_POST['feedback_id']);

// Delete the feedback
$query = "DELETE FROM feedback WHERE FEEDBACK_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $feedback_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>