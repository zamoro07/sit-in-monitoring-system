<?php
session_start();
require_once 'db_connect.php';
require_once 'notification_functions.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $idno = $_POST['idno'];
    $fullName = $_POST['fullname'];
    $course = $_POST['course'];
    $yearLevel = $_POST['year_level'];
    $purpose = $_POST['purpose'];
    $laboratory = $_POST['laboratory'];
    $pcNum = $_POST['pcnum'];
    $date = $_POST['date'];
    $timeIn = $_POST['time_in'];
    
    // Validate the data
    if (empty($idno) || empty($fullName) || empty($course) || empty($yearLevel) || 
        empty($purpose) || empty($laboratory) || empty($pcNum) || empty($date) || empty($timeIn)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: reservation.php");
        exit();
    }
    
    // Insert the reservation
    $sql = "INSERT INTO reservation (IDNO, FULL_NAME, COURSE, YEAR_LEVEL, PURPOSE, LABORATORY, PC_NUM, DATE, TIME_IN, TIME_OUT, STATUS) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '00:00:00', 'Pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssiis", $idno, $fullName, $course, $yearLevel, $purpose, $laboratory, $pcNum, $date, $timeIn);
    
    if ($stmt->execute()) {
        $reservation_id = $stmt->insert_id;
        
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
            $message = "New reservation from $fullName for Lab $laboratory on $date";
            create_notification($user_id, $reservation_id, null, null, $message);
        }
        
        $_SESSION['success'] = "Reservation submitted successfully. Waiting for approval.";
        header("Location: student_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Error submitting reservation: " . $conn->error;
        header("Location: reservation.php");
        exit();
    }
}
?>
