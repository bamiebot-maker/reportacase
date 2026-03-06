<?php
function generateReportCode() {
    return 'FUD-' . strtoupper(uniqid());
}

function formatDate($date) {
    return date('M d, Y H:i', strtotime($date));
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'secondary',
        'investigating' => 'warning',
        'resolved' => 'success'
    ];
    return '<span class="badge bg-' . ($badges[$status] ?? 'secondary') . '">' . ucfirst($status) . '</span>';
}

function getPriorityBadge($priority) {
    $badges = [
        'low' => 'success',
        'medium' => 'warning',
        'high' => 'danger'
    ];
    return '<span class="badge bg-' . ($badges[$priority] ?? 'secondary') . '">' . ucfirst($priority) . '</span>';
}

function sanitizeInput($data) {
    if ($data === null) {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Simple debug function
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}
?>