<?php
session_start();
require '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Check if ID is provided and confirmed flag is set
if (isset($_GET['id']) && isset($_GET['confirmed']) && $_GET['confirmed'] === 'true') {
    $studentId = (int)$_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM users WHERE STUD_NUM = ?");
    $stmt->bind_param("i", $studentId);
    
    if ($stmt->execute()) {
        $_SESSION['toast'] = [
            'icon' => 'success',
            'title' => 'Student deleted successfully',
            'background' => '#10B981'
        ];
    } else {
        $_SESSION['toast'] = [
            'icon' => 'error',
            'title' => 'Failed to delete student',
            'background' => '#EF4444'
        ];
    }
    $stmt->close();
}

header("Location: admin_studlist.php");
exit();
?>