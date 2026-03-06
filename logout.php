<?php
session_start();

// Record logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    include 'config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    $activity_query = "INSERT INTO user_activity (user_id, activity) VALUES (:user_id, 'Logged out')";
    $activity_stmt = $db->prepare($activity_query);
    $activity_stmt->bindParam(":user_id", $_SESSION['user_id']);
    $activity_stmt->execute();
}

// Destroy session
session_destroy();

// Redirect to home page
header("Location: index.php");
exit();
?>