<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();

$pageTitle = 'Dashboard';
require_once 'layout.php';

$currentUserRole = getCurrentUserRole();
$currentUserId = getCurrentUserId();

if ($currentUserRole === 'member') {
    $memberStmt = $pdo->prepare("SELECT * FROM members WHERE user_id = ?");
    $memberStmt->execute([$currentUserId]);
    $member = $memberStmt->fetch();

    if ($member) {
        $totalPaid = getMemberTotalPaid($member['id']);
        $totalInvestment = getMemberTotalInvestment($member['id']);
        $due = $totalInvestment - $totalPaid;

        $paymentsStmt = $pdo->prepare("SELECT p.*, pr.project_name
            FROM payments p
            LEFT JOIN projects pr ON p.project_id = pr.id
            WHERE p.member_id = ?
            ORDER BY p.payment_date DESC, p.id DESC
            LIMIT 5");
        $paymentsStmt->execute([$member['id']]);
        $recentPayments = $paymentsStmt->fetchAll();
    } else {
        $totalPaid = 0;
        $totalInvestment = 0;
        $due = 0;
        $recentPayments = [];
    }
    ?>

    <div class="dashboard-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h4>Welcome back, <?php echo sanitize($_SESSION['full_name']); ?>.</h4>
                <p>View your own investment summary, paid amount, due balance, and recent payment history.</p>
            </div>
            <div class="col-lg-4 d-flex justify-content-lg-end gap-2 flex-wrap mt-3 mt-lg-0">
                <a href="payment-add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Submit Payment</a>
                <a href="my-payments.php" class="btn btn-warning"><i class="fas fa-history"></i> My Payments</a>
            </div>
        </div>
    </div>

    <?php if (!$member): ?>
        <div class="alert alert-warning">No member profile is linked to your user account yet.</div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card blue">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-1">My Investment</h6>
                        <h4><?php echo formatCurrency($totalInvestment); ?></h4>
                    </div>
                    <span class="metric-icon"><i class="fas fa-chart-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card green">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-1">Total Paid</h6>
                        <h4><?php echo formatCurrency($totalPaid); ?></h4>
                    </div>
                    <span class="metric-icon"><i class="fas fa-sack-dollar"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card red">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-1">Due Balance</h6>
                        <h4><?php echo formatCurrency($due); ?></h4>
                    </div>
                    <span class="metric-icon"><i class="fas fa-clock"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">My Member Profile</h5></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th>Membership</th><td><?php echo sanitize($member['membership_number'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Name</th><td><?php echo sanitize(trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?: 'N/A'); ?></td></tr>
                            <tr><th>Phone</th><td><?php echo sanitize($member['phone'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Email</th><td><?php echo sanitize($member['email'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Status</th><td><?php echo sanitize(ucfirst($member['member_status'] ?? 'N/A')); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Payments</h5>
                    <a href="my-payments.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Payment No</th>
                                    <th>Project</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentPayments)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">No payments found</td></tr>
                                <?php endif; ?>
                                <?php foreach ($recentPayments as $payment): ?>
                                <tr>
                                    <td><?php echo formatDate($payment['payment_date']); ?></td>
                                    <td><?php echo sanitize($payment['payment_number']); ?></td>
                                    <td><?php echo sanitize($payment['project_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatCurrency($payment['amount']); ?></td>
                                    <td><?php echo sanitize(ucfirst($payment['approval_status'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once 'layout-end.php';
    exit;
}

$totalCollection = getTotalCollection();
$totalExpenses = getTotalExpenses();
$netBalance = $totalCollection - $totalExpenses;
$totalMembers = getTotalMembers('active');
$totalProjects = getTotalProjects();

$pendingDues = 0;
$stmt = $pdo->query("SELECT SUM(total_investment_amount) - COALESCE(SUM(p.amount), 0) as due 
    FROM investment_plans i 
    LEFT JOIN payments p ON p.investment_plan_id = i.id AND p.approval_status = 'approved'");
$pendingDues = $stmt->fetch()['due'] ?? 0;
?>

<div class="dashboard-hero">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h4>Welcome back, <?php echo sanitize($_SESSION['full_name']); ?>.</h4>
            <p>Monitor capital collection, expenses, dues, and member activity from one focused investment control center.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a href="payment-add.php" class="btn btn-warning"><i class="fas fa-plus-circle"></i> Record Payment</a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card blue">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-1">Total Collection</h6>
                    <h4><?php echo formatCurrency($totalCollection); ?></h4>
                </div>
                <span class="metric-icon"><i class="fas fa-sack-dollar"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card orange">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-1">Total Expenses</h6>
                    <h4><?php echo formatCurrency($totalExpenses); ?></h4>
                </div>
                <span class="metric-icon"><i class="fas fa-file-invoice-dollar"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card green">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-1">Net Balance</h6>
                    <h4><?php echo formatCurrency($netBalance); ?></h4>
                </div>
                <span class="metric-icon"><i class="fas fa-chart-line"></i></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card red">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="text-muted mb-1">Pending Dues</h6>
                    <h4><?php echo formatCurrency($pendingDues); ?></h4>
                </div>
                <span class="metric-icon"><i class="fas fa-clock"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card mini-card">
            <div class="card-body text-center">
                <span class="metric-icon"><i class="fas fa-users"></i></span>
                <div>
                    <h5><?php echo $totalMembers; ?></h5>
                    <small class="text-muted">Active Members</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mini-card">
            <div class="card-body text-center">
                <span class="metric-icon"><i class="fas fa-building"></i></span>
                <div>
                    <h5><?php echo $totalProjects; ?></h5>
                    <small class="text-muted">Total Projects</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Quick Actions</h6>
                <div class="d-grid gap-2 quick-actions">
                    <a href="payment-add.php" class="btn btn-success"><i class="fas fa-plus-circle"></i> Add Payment</a>
                    <a href="members.php" class="btn btn-info"><i class="fas fa-users"></i> View Members</a>
                    <a href="reports.php" class="btn btn-primary"><i class="fas fa-chart-pie"></i> View Reports</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>
