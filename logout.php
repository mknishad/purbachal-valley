<?php
require_once 'auth.php';
requireLogin();
$pageTitle = 'Logout';
?>

<?php
session_destroy();
session_unset();
$_SESSION['success'] = 'You have been logged out successfully';
redirect(BASE_URL . '/login.php');
?>