<?php
ob_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username and password are required';
        redirect(BASE_URL . '/login.php');
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {

        login($user['id'], $user['role'], $user['full_name']);
        
        logAudit('LOGIN', 'users', $user['id']);
        
        redirect(BASE_URL . '/dashboard.php');
    } else {
        $_SESSION['error'] = 'Invalid username or password';
        redirect(BASE_URL . '/login.php');
    }
} else {
    redirect(BASE_URL . '/login.php');
}