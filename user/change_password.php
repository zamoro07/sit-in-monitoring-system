<?php
session_start();
include '../db.php'; // Ensure this file contains database connection

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Adjust according to your session variable
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        die("All fields are required.");
    }

    if ($new_password !== $confirm_password) {
        die("New passwords do not match.");
    }

    // Fetch current password from database
    $query = "SELECT PASSWORD_HASH FROM users WHERE IDNO = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();
    } else {
        die("SQL Error: " . $conn->error);
    }

    if (!$hashed_password || !password_verify($current_password, $hashed_password)) {
        die("Current password is incorrect.");
    }

    // Hash the new password
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in database
    $update_query = "UPDATE users SET PASSWORD_HASH = ? WHERE IDNO = ?";
    if ($update_stmt = $conn->prepare($update_query)) {
        $update_stmt->bind_param("ss", $new_hashed_password, $user_id);
        if ($update_stmt->execute()) {
            echo "Password updated successfully.";
        } else {
            echo "Failed to update password.";
        }
        $update_stmt->close();
    } else {
        die("SQL Error: " . $conn->error);
    }
}
?>
