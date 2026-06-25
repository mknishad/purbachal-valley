<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin', 'accountant']);

$pageTitle = 'Dashboard';
require_once 'layout.php';

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
