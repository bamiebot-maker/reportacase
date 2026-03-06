<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// Only start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $report_code = generateReportCode();
        $reporter_name = sanitizeInput($_POST['reporter_name'] ?? '');
        $contact = sanitizeInput($_POST['contact'] ?? '');
        $department = sanitizeInput($_POST['department']);
        $location = sanitizeInput($_POST['location']);
        $category_id = intval($_POST['category_id']);
        $description = sanitizeInput($_POST['description']);
        $priority = sanitizeInput($_POST['priority'] ?? 'medium');
        
        // Validate required fields
        if (empty($department) || empty($location) || empty($category_id) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit();
        }
        
        $query = "INSERT INTO reports (report_code, reporter_name, contact, department, location, category_id, description, priority) 
                  VALUES (:report_code, :reporter_name, :contact, :department, :location, :category_id, :description, :priority)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":report_code", $report_code);
        $stmt->bindParam(":reporter_name", $reporter_name);
        $stmt->bindParam(":contact", $contact);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":priority", $priority);
        
        if ($stmt->execute()) {
            // Notify CSO about new report
            $notify_query = "INSERT INTO notifications (user_id, message, link) 
                            SELECT id, CONCAT('New ', :priority, ' priority report: ', :report_code), 'cso/reports.php' 
                            FROM users WHERE role = 'CSO'";
            $notify_stmt = $db->prepare($notify_query);
            $notify_stmt->bindParam(":report_code", $report_code);
            $notify_stmt->bindParam(":priority", $priority);
            $notify_stmt->execute();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Report submitted successfully!', 
                'report_code' => $report_code
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting report. Please try again.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No data received.']);
}
?>