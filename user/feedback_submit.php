<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sitinId = isset($_POST['sitinId']) ? (int)$_POST['sitinId'] : 0;
    $laboratory = isset($_POST['laboratory']) ? $_POST['laboratory'] : '';
    $feedback = isset($_POST['feedback']) ? $_POST['feedback'] : '';
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    
    // Get user's IDNO
    $stmt = $conn->prepare("SELECT IDNO FROM users WHERE STUD_NUM = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $stmt = $conn->prepare("INSERT INTO feedback (IDNO, LABORATORY, DATE, FEEDBACK, RATING) VALUES (?, ?, CURRENT_DATE(), ?, ?)");
        $stmt->bind_param("issi", $user['IDNO'], $laboratory, $feedback, $rating);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
