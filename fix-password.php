<?php
require_once 'config.php';

$newHash = password_hash('admin123', PASSWORD_DEFAULT);
echo "New hash: " . $newHash . "<br>";

$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
$stmt->execute([$newHash]);

echo "Password updated! Try logging in now.";
?>