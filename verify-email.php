<?php
require_once 'config.php';

$token = sanitize($_GET['token'] ?? '');
$email = sanitize($_GET['email'] ?? '');

if (!$token || !$email) {
    die('Invalid verification link');
}

$stmt = $pdo->prepare("SELECT id, first_name, email_verified FROM members WHERE email = ? AND verification_token = ?");
$stmt->execute([$email, $token]);
$member = $stmt->fetch();

if (!$member) {
    die('Invalid verification link or already verified');
}

if ($member['email_verified'] === 'yes') {
    $message = 'Email already verified! You can login.';
} else {
    $stmt = $pdo->prepare("UPDATE members SET email_verified = 'yes', verified_at = NOW(), verification_token = NULL WHERE id = ?");
    $stmt->execute([$member['id']]);
    $message = 'Email verified successfully! You can now login.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - MR PURBACHAL VALLEY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a472a 0%, #2d5a3f 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .verify-card { background: white; border-radius: 15px; padding: 40px; max-width: 500px; text-align: center; }
        .btn-primary { background: #1a472a; border-color: #1a472a; }
    </style>
</head>
<body>
    <div class="verify-card">
        <?php if ($member['email_verified'] === 'yes'): ?>
            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
            <h4>Already Verified</h4>
            <p><?php echo $message; ?></p>
        <?php else: ?>
            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
            <h4>Verification Successful!</h4>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <a href="login.php" class="btn btn-primary mt-3">Login Now</a>
    </div>
</body>
</html>