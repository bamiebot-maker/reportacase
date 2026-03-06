<?php
require_once '../config/database.php';

// Only start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo 'unauthorized';
    exit();
}

if ($_POST && isset($_POST['notification_id'])) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $_POST['notification_id']);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'invalid';
}
?>