<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin']);

$pageTitle = 'User Management';
require_once 'layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $role = sanitize($_POST['role']);
        $fullName = sanitize($_POST['full_name']);
        
        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'All fields are required';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, full_name, status) VALUES (?, ?, ?, ?, ?, 'active')");
            try {
                $stmt->execute([$username, $email, $hash, $role, $fullName]);
                $_SESSION['success'] = 'User created successfully!';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Username or email already exists';
            }
        }
    } elseif ($_POST['action'] === 'update') {
        $userId = intval($_POST['user_id']);
        $fullName = sanitize($_POST['full_name']);
        $role = sanitize($_POST['role']);
        $status = sanitize($_POST['status']);
        
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, role = ?, status = ? WHERE id = ?");
        $stmt->execute([$fullName, $role, $status, $userId]);
        $_SESSION['success'] = 'User updated successfully!';
    } elseif ($_POST['action'] === 'reset_password') {
        $userId = intval($_POST['user_id']);
        $newPassword = $_POST['new_password'];
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);
        $_SESSION['success'] = 'Password reset successfully!';
    }
    
    redirect(BASE_URL . '/users.php');
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-12 text-end">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['full_name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td>
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge bg-primary">Admin</span>
                        <?php elseif ($user['role'] === 'accountant'): ?>
                            <span class="badge bg-success">Accountant</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Member</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['status'] === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $user['id']; ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#resetModal<?php echo $user['id']; ?>">
                            <i class="fas fa-key"></i>
                        </button>
                    </td>
                </tr>
                
                <div class="modal fade" id="editModal<?php echo $user['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="role" class="form-select">
                                            <option value="admin" <?php echo ($user['role'] === 'admin' ? 'selected' : ''); ?>>Admin</option>
                                            <option value="accountant" <?php echo ($user['role'] === 'accountant' ? 'selected' : ''); ?>>Accountant</option>
                                            <option value="member" <?php echo ($user['role'] === 'member' ? 'selected' : ''); ?>>Member</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" <?php echo ($user['status'] === 'active' ? 'selected' : ''); ?>>Active</option>
                                            <option value="inactive" <?php echo ($user['status'] === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="modal fade" id="resetModal<?php echo $user['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Reset Password</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="reset_password">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Reset Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="accountant">Accountant</option>
                            <option value="member">Member</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>