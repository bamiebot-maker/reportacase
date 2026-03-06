<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get officer ID from URL
$officer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($officer_id > 0) {
    // Check if officer has assigned cases
    $check_query = "SELECT COUNT(*) as case_count FROM cases WHERE officer_id = :officer_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":officer_id", $officer_id);
    $check_stmt->execute();
    $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['case_count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete officer. They have " . $result['case_count'] . " assigned case(s). Please reassign cases first.";
    } else {
        // Delete officer
        $delete_query = "DELETE FROM users WHERE id = :id AND role = 'Officer'";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(":id", $officer_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Officer deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting officer. Please try again.";
        }
    }
} else {
    $_SESSION['error_message'] = "Invalid officer ID.";
}

header("Location: officers.php");
exit();
?>