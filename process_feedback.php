<?php
session_start();
require_once 'db_connect.php';
require_once 'notification_functions.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $idno = $_POST['idno'];
    $laboratory = $_POST['laboratory'];
    $feedback = $_POST['feedback'];
    $rating = $_POST['rating'];
    
    // Validate the data
    if (empty($idno) || empty($laboratory) || empty($feedback) || !isset($rating)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: feedback.php");
        exit();
    }
    
    // Insert the feedback
    $sql = "INSERT INTO feedback (IDNO, LABORATORY, DATE, FEEDBACK, RATING) 
            VALUES (?, ?, CURDATE(), ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $idno, $laboratory, $feedback, $rating);
    
    if ($stmt->execute()) {
        $feedback_id = $stmt->insert_id;
        
        // Get the user ID
        $user_id_query = "SELECT STUD_NUM FROM users WHERE IDNO = ?";
        $user_stmt = $conn->prepare($user_id_query);
        $user_stmt->bind_param("i", $idno);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_result->num_rows > 0) {
            $user_row = $user_result->fetch_assoc();
            $user_id = $user_row['STUD_NUM'];
            
            // Create notification (though this is now handled by the trigger)
            $message = "New feedback received for $laboratory with rating $rating";
            create_notification($user_id, null, null, $feedback_id, $message);
        }
        
        $_SESSION['success'] = "Feedback submitted successfully.";
        header("Location: student_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Error submitting feedback: " . $conn->error;
        header("Location: feedback.php");
        exit();
    }
}
?>
