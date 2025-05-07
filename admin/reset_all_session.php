<?php
session_start();
require '../db.php';

// Check if user is admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// SQL query to update all users' sessions back to 30
$query = "UPDATE users SET SESSION = 30";
$result = mysqli_query($conn, $query);

// Check if query was successful
if ($result) {
    echo json_encode(['status' => 'success', 'message' => 'All sessions have been reset to 30']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to reset sessions']);
}
?>