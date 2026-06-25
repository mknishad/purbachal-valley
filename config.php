<?php
/**
 * MR PURBACHAL VALLEY - Land Investment & Member Contribution Tracking System
 * Database Configuration
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '8889');
define('DB_NAME', 'purbachal_valley');
define('DB_USER', 'root');
define('DB_PASS', 'root');

define('BASE_URL', 'http://localhost:8888/purbachal-valley');

define('TIMEZONE', 'Asia/Dhaka');
date_default_timezone_set(TIMEZONE);

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}