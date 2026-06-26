<?php
require_once 'auth.php';

if (isLoggedIn()) {
    redirect(BASE_URL . '/dashboard.php');
}

redirect(BASE_URL . '/login.php');
