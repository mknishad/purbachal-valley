<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

session_start();

require_once 'config.php';

global $pdo;

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isAccountant() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'accountant';
}

function hasAccess($roles = []) {
    if (!isset($_SESSION['role'])) return false;
    $userRole = $_SESSION['role'];
    return in_array($userRole, $roles, true);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/login.php');
    }
}

function requireRole($roles) {
    requireLogin();
    if (!hasAccess($roles)) {
        $_SESSION['error'] = 'Access denied';
        redirect(BASE_URL . '/dashboard.php');
    }
}

function login($userId, $role, $fullName) {
    global $pdo;
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;
    $_SESSION['full_name'] = $fullName;
    $_SESSION['login_time'] = time();
    
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$userId]);
}

function logout() {
    session_destroy();
    redirect(BASE_URL . '/login.php');
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}