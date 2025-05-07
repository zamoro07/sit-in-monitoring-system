<?php
session_start();
require '../db.php';

// Check if user is admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json);

if (!isset($data->studentId)) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID is required']);
    exit;
}

$studentId = $data->studentId;

// Reset session to 30 for the specific student
$stmt = $conn->prepare("UPDATE users SET session = 30 WHERE STUD_NUM = ?");
$stmt->bind_param("i", $studentId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Student session has been reset successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to reset student session']);
}

$stmt->close();
$conn->close();
?>
