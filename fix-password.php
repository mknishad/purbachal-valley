<?php
require_once 'config.php';

$resetKey = 'pv-admin-reset-20260701';

if (($_GET['key'] ?? '') !== $resetKey) {
    http_response_code(403);
    exit('Forbidden');
}

$passwordHash = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password_hash, role, full_name, status)
    VALUES ('admin', 'admin@purbachalvalley.com', ?, 'admin', 'System Administrator', 'active')
    ON DUPLICATE KEY UPDATE
        username = VALUES(username),
        email = VALUES(email),
        password_hash = VALUES(password_hash),
        role = 'admin',
        full_name = VALUES(full_name),
        status = 'active'
");
$stmt->execute([$passwordHash]);

echo "Admin account is ready. Username: admin Password: admin123. Delete this file from the server now.";
?>
