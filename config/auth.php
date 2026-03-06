<?php
// Only start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function isCSO() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'CSO';
    }

    public static function isOfficer() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'Officer';
    }

    public static function redirectIfNotLoggedIn() {
        if (!self::isLoggedIn()) {
            header("Location: ../login.php");
            exit();
        }
    }

    public static function redirectByRole() {
        if (self::isLoggedIn()) {
            if (self::isCSO()) {
                header("Location: cso/dashboard.php");
            } else {
                header("Location: officer/dashboard.php");
            }
            exit();
        }
    }

    public static function requireCSO() {
        self::redirectIfNotLoggedIn();
        if (!self::isCSO()) {
            header("Location: ../index.php");
            exit();
        }
    }

    public static function requireOfficer() {
        self::redirectIfNotLoggedIn();
        if (!self::isOfficer()) {
            header("Location: ../index.php");
            exit();
        }
    }

    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>