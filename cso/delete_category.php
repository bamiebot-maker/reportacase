<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($category_id > 0) {
    // Check if category has reports
    $check_query = "SELECT COUNT(*) as report_count FROM reports WHERE category_id = :category_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":category_id", $category_id);
    $check_stmt->execute();
    $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['report_count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete category. There are " . $result['report_count'] . " report(s) in this category.";
    } else {
        // Delete category
        $delete_query = "DELETE FROM categories WHERE id = :id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(":id", $category_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Category deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting category. Please try again.";
        }
    }
} else {
    $_SESSION['error_message'] = "Invalid category ID.";
}

header("Location: categories.php");
exit();
?>