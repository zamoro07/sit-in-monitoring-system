<?php
session_start();
require '../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM announcement WHERE ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // For AJAX requests, return a success message
        echo "success";
    } else {
        // For AJAX requests, return an error message
        echo "error";
    }
    
    $stmt->close();
    
    // Don't redirect if this is an AJAX request
    exit();
}

// Redirect back to dashboard (this will only happen for non-AJAX requests)
header("Location: admin_dashboard.php");
exit();
?>