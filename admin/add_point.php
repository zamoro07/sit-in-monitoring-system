<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idno = mysqli_real_escape_string($conn, $_POST['idno']);

    // Update points in users table
    $sql = "UPDATE users SET POINTS = POINTS + 1 WHERE IDNO = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idno);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
