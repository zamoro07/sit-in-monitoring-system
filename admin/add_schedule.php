<?php
session_start();
require '../db.php'; // Include database connection

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $day = isset($_POST['day']) ? mysqli_real_escape_string($conn, $_POST['day']) : '';
    $laboratory = isset($_POST['laboratory']) ? mysqli_real_escape_string($conn, $_POST['laboratory']) : '';
    $time_start = isset($_POST['time_start']) ? mysqli_real_escape_string($conn, $_POST['time_start']) : '';
    $time_end = isset($_POST['time_end']) ? mysqli_real_escape_string($conn, $_POST['time_end']) : '';
    $subject = isset($_POST['subject']) ? mysqli_real_escape_string($conn, $_POST['subject']) : '';
    $professor = isset($_POST['professor']) ? mysqli_real_escape_string($conn, $_POST['professor']) : '';
    
    // Validate all fields are provided
    if (empty($day) || empty($laboratory) || empty($time_start) || empty($time_end) || empty($subject) || empty($professor)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Check if there's a scheduling conflict
    $check_sql = "SELECT * FROM lab_schedule 
                 WHERE DAY = ? 
                 AND LABORATORY = ? 
                 AND ((TIME_START BETWEEN ? AND ?) 
                      OR (TIME_END BETWEEN ? AND ?)
                      OR (? BETWEEN TIME_START AND TIME_END)
                      OR (? BETWEEN TIME_START AND TIME_END))";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ssssssss", $day, $laboratory, $time_start, $time_end, $time_start, $time_end, $time_start, $time_end);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'There is a scheduling conflict with an existing class']);
        exit;
    }
    
    // Insert new schedule
    $sql = "INSERT INTO lab_schedule (DAY, LABORATORY, TIME_START, TIME_END, SUBJECT, PROFESSOR) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $day, $laboratory, $time_start, $time_end, $subject, $professor);
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Schedule added successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to add schedule: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    // Not a POST request
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
