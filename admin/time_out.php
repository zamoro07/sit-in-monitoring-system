<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sitinId = isset($_POST['sitin_id']) ? (int)$_POST['sitin_id'] : 0;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First get the student's IDNO and laboratory info
        $getInfoStmt = $conn->prepare("SELECT IDNO, LABORATORY FROM curr_sitin WHERE SITIN_ID = ?");
        $getInfoStmt->bind_param("i", $sitinId);
        $getInfoStmt->execute();
        $result = $getInfoStmt->get_result();
        $row = $result->fetch_assoc();
        $idno = $row['IDNO'];
        $laboratory = $row['LABORATORY'];
        $getInfoStmt->close();
        
        // Update curr_sitin record
        $updateSitinStmt = $conn->prepare("UPDATE curr_sitin SET TIME_OUT = NOW(), STATUS = 'Completed' WHERE SITIN_ID = ?");
        $updateSitinStmt->bind_param("i", $sitinId);
        $updateSitinStmt->execute();
        $updateSitinStmt->close();
        
        // Decrease session count by 1
        $updateSessionStmt = $conn->prepare("UPDATE users SET SESSION = SESSION - 1 WHERE IDNO = ?");
        $updateSessionStmt->bind_param("s", $idno);
        $updateSessionStmt->execute();
        $updateSessionStmt->close();

        // Update computer status to available
        // Extract lab number from laboratory name (e.g., "Lab 524" -> "lab524")
        $labNumber = strtolower(str_replace(' ', '', $laboratory));
        $available = 'available';
        
        $updateComputerStmt = $conn->prepare("UPDATE computer SET STATUS = ? WHERE LABORATORY = ?");
        $updateComputerStmt->bind_param("ss", $available, $labNumber);
        $updateComputerStmt->execute();
        $updateComputerStmt->close();
        
        $conn->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update record: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
