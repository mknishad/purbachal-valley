<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();

$pageTitle = 'My Profile';
require_once 'layout.php';

$userId = getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->execute([$fullName, $email, $phone, $userId]);
    
    $_SESSION['full_name'] = $fullName;
    $_SESSION['success'] = 'Profile updated successfully!';
    redirect(BASE_URL . '/profile.php');
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>My Profile</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $user['phone'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst($user['status']); ?>" disabled>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="change-password.php">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>