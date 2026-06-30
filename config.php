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

$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
);
$scheme = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8888';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/purbachal-valley/index.php'));
$appBasePath = ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');

define('BASE_URL', $scheme . '://' . $host . $appBasePath);

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
    return htmlspecialchars(trim((string) ($data ?? '')), ENT_QUOTES, 'UTF-8');
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
