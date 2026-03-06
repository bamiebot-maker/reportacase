<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// Only start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo '<li><a class="dropdown-item text-center" href="#">Please login</a></li>';
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get unread notifications
$query = "SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($notifications)) {
    echo '<li><a class="dropdown-item text-center" href="#">No new notifications</a></li>';
} else {
    foreach ($notifications as $notification) {
        echo '<li><a class="dropdown-item d-flex justify-content-between align-items-start" href="' . $notification['link'] . '" onclick="markNotificationRead(' . $notification['id'] . ')">';
        echo '<div class="ms-2 me-auto">';
        echo '<div class="fw-bold small">' . htmlspecialchars($notification['message']) . '</div>';
        echo '<small class="text-muted">' . formatDate($notification['created_at']) . '</small>';
        echo '</div>';
        echo '</a></li>';
    }
    echo '<li><hr class="dropdown-divider"></li>';
    echo '<li><a class="dropdown-item text-center" href="#">View All Notifications</a></li>';
}
?>