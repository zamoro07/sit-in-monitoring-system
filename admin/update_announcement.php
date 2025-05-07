<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo "unauthorized";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announcement_id'], $_POST['content'])) {
    $id = $_POST['announcement_id'];
    $content = $_POST['content'];
    
    $stmt = $conn->prepare("UPDATE announcement SET CONTENT = ? WHERE ID = ?");
    $stmt->bind_param("si", $content, $id);
    
    if ($stmt->execute()) {
        // Create notifications for all users about the updated announcement
        $notifyAllUsers = $conn->prepare("INSERT INTO notification (USER_ID, ANNOUNCEMENT_ID, MESSAGE, IS_READ, CREATED_AT) 
                                          SELECT STUD_NUM, ?, 'An announcement has been updated', 0, NOW() 
                                          FROM users");
        $notifyAllUsers->bind_param("i", $id);
        $notifyAllUsers->execute();
        $notifyAllUsers->close();
        
        echo "success";
    } else {
        echo "error";
    }
    $stmt->close();
} else {
    echo "invalid";
}
?>
