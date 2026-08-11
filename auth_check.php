<?php
// includes/auth_check.php - Session Authentication Middleware
 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
function requireAuth($roleRequired = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=please_login");
        exit;
    }
 
    if ($roleRequired !== null && $_SESSION['role'] !== $roleRequired) {
        if ($_SESSION['role'] === 'driver') {
            header("Location: driver_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
}
 
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['full_name'] ?? 'User',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? 'user',
        'wallet_balance' => $_SESSION['wallet_balance'] ?? 0.00
    ];
}
 
