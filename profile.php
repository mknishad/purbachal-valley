<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();

$userId = getCurrentUserId();
$currentUserRole = getCurrentUserRole();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? 'update_profile');

    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userPassword = $stmt->fetch();

        if (!$userPassword || !password_verify($currentPassword, $userPassword['password_hash'])) {
            $_SESSION['error'] = 'Current password is incorrect';
            redirect(BASE_URL . '/profile.php');
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = 'New password must be at least 6 characters';
            redirect(BASE_URL . '/profile.php');
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New password and confirmation do not match';
            redirect(BASE_URL . '/profile.php');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);

        $_SESSION['success'] = 'Password changed successfully!';
        redirect(BASE_URL . '/profile.php');
    } else {
        if ($currentUserRole === 'member') {
            $_SESSION['error'] = 'Member profile details are read-only';
            redirect(BASE_URL . '/profile.php');
        }

        $fullName = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);

        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$fullName, $email, $phone, $userId]);

        $_SESSION['full_name'] = $fullName;
        $_SESSION['success'] = 'Profile updated successfully!';
        redirect(BASE_URL . '/profile.php');
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$member = null;
$recentPayments = [];
$totalInvestment = 0;
$totalPaid = 0;
$due = 0;
$assignedProjects = [];

if ($currentUserRole === 'member') {
    $memberStmt = $pdo->prepare("SELECT * FROM members WHERE user_id = ?");
    $memberStmt->execute([$userId]);
    $member = $memberStmt->fetch();

    if ($member) {
        $totalInvestment = getMemberTotalInvestment($member['id']);
        $totalPaid = getMemberTotalPaid($member['id']);
        $due = $totalInvestment - $totalPaid;
        $assignedProjects = getMemberAssignedProjects($member['id']);

        $paymentsStmt = $pdo->prepare("SELECT p.*, pr.project_name
            FROM payments p
            LEFT JOIN projects pr ON p.project_id = pr.id
            WHERE p.member_id = ?
            ORDER BY p.payment_date DESC, p.id DESC
            LIMIT 6");
        $paymentsStmt->execute([$member['id']]);
        $recentPayments = $paymentsStmt->fetchAll();
    }
}

$pageTitle = 'My Profile';
require_once 'layout.php';
?>

<?php if ($currentUserRole === 'member'): ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1"><?php echo sanitize($member ? trim($member['first_name'] . ' ' . $member['last_name']) : $user['full_name']); ?></h4>
        <span class="text-muted"><?php echo $member ? sanitize($member['membership_number']) : 'No linked member profile'; ?></span>
    </div>
    <span class="badge bg-secondary">Read Only</span>
</div>

<?php if (!$member): ?>
    <div class="alert alert-warning">No member profile is linked to your user account yet.</div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card blue">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Investment</h6>
                    <h4><?php echo formatCurrency($totalInvestment); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card green">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Paid</h6>
                    <h4><?php echo formatCurrency($totalPaid); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card red">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Due</h6>
                    <h4><?php echo formatCurrency($due); ?></h4>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Personal Information</h5></div>
            <div class="card-body p-0">
                <table class="table detail-table mb-0">
                    <tbody>
                        <tr><th>Membership</th><td><?php echo sanitize($member['membership_number'] ?? 'N/A'); ?></td></tr>
                        <tr><th>Full Name</th><td><?php echo sanitize($member ? trim($member['first_name'] . ' ' . $member['last_name']) : $user['full_name']); ?></td></tr>
                        <tr><th>Father's Name</th><td><?php echo sanitize($member['father_name'] ?? 'N/A'); ?></td></tr>
                        <tr><th>Mother's Name</th><td><?php echo sanitize($member['mother_name'] ?? 'N/A'); ?></td></tr>
                        <tr><th>Gender</th><td><?php echo sanitize(ucfirst($member['gender'] ?? 'N/A')); ?></td></tr>
                        <tr><th>Date of Birth</th><td><?php echo !empty($member['date_of_birth']) ? formatDate($member['date_of_birth']) : 'N/A'; ?></td></tr>
                        <tr><th>NID Number</th><td><?php echo sanitize($member['nid_number'] ?? 'N/A'); ?></td></tr>
                        <tr><th>Email</th><td><?php echo sanitize($member['email'] ?? $user['email']); ?></td></tr>
                        <tr><th>Phone</th><td><?php echo sanitize($member['phone'] ?? $user['phone']); ?></td></tr>
                        <tr><th>Alternative Phone</th><td><?php echo sanitize($member['alternative_phone'] ?? 'N/A'); ?></td></tr>
                        <tr><th>Occupation</th><td><?php echo sanitize($member['occupation'] ?? 'N/A'); ?></td></tr>
                        <tr><th>Employer</th><td><?php echo sanitize($member['employer_name'] ?? 'N/A'); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Address</h5></div>
            <div class="card-body">
                <h6>Present Address</h6>
                <p class="text-muted"><?php echo nl2br(sanitize($member['present_address'] ?? 'N/A')); ?></p>
                <h6>Permanent Address</h6>
                <p class="text-muted mb-0"><?php echo nl2br(sanitize($member['permanent_address'] ?? 'N/A')); ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Nominee Information</h5></div>
            <div class="card-body p-0">
                <table class="table detail-table mb-0">
                    <tbody>
                        <tr><th>Name</th><td><?php echo sanitize($member['nominee_name'] ?? 'N/A'); ?></td></tr>
                        <tr><th>Relation</th><td><?php echo sanitize($member['nominee_relation'] ?? 'N/A'); ?></td></tr>
                        <tr><th>NID</th><td><?php echo sanitize($member['nominee_nid'] ?? 'N/A'); ?></td></tr>
                        <tr><th>Phone</th><td><?php echo sanitize($member['nominee_phone'] ?? 'N/A'); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Membership</h5></div>
            <div class="card-body p-0">
                <table class="table detail-table mb-0">
                    <tbody>
                        <tr><th>Status</th><td><?php echo sanitize(ucfirst($member['member_status'] ?? 'N/A')); ?></td></tr>
                        <tr><th>KYC Status</th><td><?php echo sanitize(ucfirst($member['kyc_status'] ?? 'N/A')); ?></td></tr>
                        <tr><th>Investment Type</th><td><?php echo sanitize(ucfirst($member['investment_type'] ?? 'N/A')); ?></td></tr>
                        <tr>
                            <th>Project</th>
                            <td>
                                <?php if (empty($assignedProjects)): ?>
                                    N/A
                                <?php else: ?>
                                    <div class="project-badge-list">
                                        <?php foreach ($assignedProjects as $project): ?>
                                            <span class="badge bg-info"><?php echo sanitize($project['project_name']); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><th>Registration Date</th><td><?php echo !empty($member['registration_date']) ? formatDate($member['registration_date']) : 'N/A'; ?></td></tr>
                        <tr><th>Account Username</th><td><?php echo sanitize($user['username']); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent Payments</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentPayments)): ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">No payments found</td></tr>
                            <?php endif; ?>
                            <?php foreach ($recentPayments as $payment): ?>
                            <tr>
                                <td><?php echo formatDate($payment['payment_date']); ?></td>
                                <td><?php echo formatCurrency($payment['amount']); ?></td>
                                <td><?php echo sanitize(ucfirst($payment['approval_status'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Change Password</h5></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="current_password" class="form-control" required>
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; exit; ?>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>My Profile</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
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
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="current_password" class="form-control" required>
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>
