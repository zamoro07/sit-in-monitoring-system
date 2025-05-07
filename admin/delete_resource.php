<?php
session_start();
require '../db.php';

// Check if user is admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo "unauthorized";
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "error: no id provided";
    exit;
}

$resourceId = (int)$_GET['id'];

// Prepare and execute delete query
$stmt = $conn->prepare("DELETE FROM resources WHERE RESOURCES_ID = ?");
$stmt->bind_param("i", $resourceId);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
