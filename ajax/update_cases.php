<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// Only start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_POST && isset($_POST['case_id'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $case_id = intval($_POST['case_id']);
        $status = sanitizeInput($_POST['status']);
        $notes = sanitizeInput($_POST['notes'] ?? '');
        
        // Validate case ownership (for officers)
        if ($_SESSION['role'] === 'Officer') {
            $check_query = "SELECT id FROM cases WHERE id = :case_id AND officer_id = :officer_id";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(":case_id", $case_id);
            $check_stmt->bindParam(":officer_id", $_SESSION['user_id']);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'You are not authorized to update this case.']);
                exit();
            }
        }
        
        // Update case
        $query = "UPDATE cases SET status = :status, notes = :notes, updated_at = NOW() WHERE id = :case_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":notes", $notes);
        $stmt->bindParam(":case_id", $case_id);
        
        if ($stmt->execute()) {
            // Update report status
            $update_report_query = "UPDATE reports r 
                                   JOIN cases c ON r.id = c.report_id 
                                   SET r.status = :status 
                                   WHERE c.id = :case_id";
            $update_stmt = $db->prepare($update_report_query);
            $update_stmt->bindParam(":status", $status);
            $update_stmt->bindParam(":case_id", $case_id);
            $update_stmt->execute();
            
            // Notify CSO about case update
            $get_case_info = "SELECT r.report_code, c.officer_id FROM cases c 
                             JOIN reports r ON c.report_id = r.id 
                             WHERE c.id = :case_id";
            $info_stmt = $db->prepare($get_case_info);
            $info_stmt->bindParam(":case_id", $case_id);
            $info_stmt->execute();
            $case_info = $info_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($case_info) {
                $notify_query = "INSERT INTO notifications (user_id, message, link) 
                                SELECT id, CONCAT('Case updated: ', :report_code, ' - Status: ', :status), 'cso/cases.php' 
                                FROM users WHERE role = 'CSO'";
                $notify_stmt = $db->prepare($notify_query);
                $notify_stmt->bindParam(":report_code", $case_info['report_code']);
                $notify_stmt->bindParam(":status", $status);
                $notify_stmt->execute();
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Case updated successfully!',
                'status' => $status,
                'updated_at' => date('M d, Y H:i')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating case. Please try again.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>