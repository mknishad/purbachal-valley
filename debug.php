<?php
require_once 'config.php';

echo "<h2>Debug Info</h2>";

// Check if database connection works
echo "<p><strong>Database:</strong> " . DB_NAME . " - Connected ✓</p>";

// Check if users table exists and has data
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users");
$userCount = $stmt->fetch()['cnt'];
echo "<p><strong>Users in database:</strong> $userCount</p>";

if ($userCount > 0) {
    $stmt = $pdo->query("SELECT id, username, email, role, status, password_hash FROM users");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Hash</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<tr><td>$row[0]</td><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>$row[4]</td><td>".substr($row[5], 0, 20)."...</td></tr>";
    }
    echo "</table>";
    
    // Test password verification
    $testPassword = 'admin123';
    $stmt = $pdo->query("SELECT password_hash FROM users WHERE username = 'admin'");
    $hash = $stmt->fetch()['password_hash'];
    echo "<p><strong>Stored hash:</strong> $hash</p>";
    echo "<p><strong>Password verify test:</strong> " . (password_verify($testPassword, $hash) ? 'SUCCESS ✓' : 'FAILED ✗') . "</p>";
} else {
    echo "<p style='color:red'>No users found! Please import database.sql</p>";
}

// Check tables
$tables = ['members', 'projects', 'payments', 'expenses', 'settings'];
echo "<h3>Table Status</h3><ul>";
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $cnt = $stmt->fetchColumn();
        echo "<li>$table: $cnt records</li>";
    } catch (Exception $e) {
        echo "<li style='color:red'>$table: Error - " . $e->getMessage() . "</li>";
    }
}
echo "</ul>";
?>