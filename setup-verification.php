<?php
require_once 'config.php';

try {
    $pdo->exec("ALTER TABLE members ADD COLUMN verification_token VARCHAR(255)");
    echo "verification_token added<br>";
} catch (Exception $e) {
    echo "verification_token: " . $e->getMessage() . "<br>";
}

try {
    $pdo->exec("ALTER TABLE members ADD COLUMN email_verified ENUM('yes','no') DEFAULT 'no'");
    echo "email_verified added<br>";
} catch (Exception $e) {
    echo "email_verified: " . $e->getMessage() . "<br>";
}

try {
    $pdo->exec("ALTER TABLE members ADD COLUMN verified_at DATETIME");
    echo "verified_at added<br>";
} catch (Exception $e) {
    echo "verified_at: " . $e->getMessage() . "<br>";
}

try {
    $pdo->exec("ALTER TABLE members ADD COLUMN user_id INT AFTER membership_number");
    echo "user_id added<br>";
} catch (Exception $e) {
    echo "user_id: " . $e->getMessage() . "<br>";
}

echo "Done!";
?>