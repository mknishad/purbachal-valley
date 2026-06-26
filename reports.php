<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin', 'accountant']);

$pageTitle = 'Reports';
require_once 'layout.php';

$reportType = sanitize($_GET['report_type'] ?? 'financial');
$projectId = sanitize($_GET['project_id'] ?? '');

$projects = $pdo->query("SELECT id, project_name FROM projects ORDER BY project_name")->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Report Type</label>
                <select name="report_type" class="form-select">
                    <option value="financial" <?php echo ($reportType === 'financial' ? 'selected' : ''); ?>>Financial Summary</option>
                    <option value="member" <?php echo ($reportType === 'member' ? 'selected' : ''); ?>>Member Report</option>
                    <option value="due" <?php echo ($reportType === 'due' ? 'selected' : ''); ?>>Due Collection Report</option>
                    <option value="project" <?php echo ($reportType === 'project' ? 'selected' : ''); ?>>Project Report</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Project</label>
                <select name="project_id" class="form-select">
                    <option value="">All Projects</option>
                    <?php foreach ($projects as $proj): ?>
                    <option value="<?php echo $proj['id']; ?>" <?php echo ($projectId == $proj['id'] ? 'selected' : ''); ?>><?php echo $proj['project_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Generate</button>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-secondary w-100" onclick="window.print()">Print</button>
            </div>
        </form>
    </div>
</div>

<?php if ($reportType === 'financial'): 
$totalCollection = getTotalCollection();
$totalExpenses = getTotalExpenses();
$netBalance = $totalCollection - $totalExpenses;
?>
<div class="card mb-4">
    <div class="card-header"><h5>Financial Summary Report</h5></div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="alert alert-success">
                    <h6>Total Collection</h6>
                    <h4><?php echo formatCurrency($totalCollection); ?></h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-danger">
                    <h6>Total Expenses</h6>
                    <h4><?php echo formatCurrency($totalExpenses); ?></h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-primary">
                    <h6>Net Balance</h6>
                    <h4><?php echo formatCurrency($netBalance); ?></h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-info">
                    <h6>Total Members</h6>
                    <h4><?php echo getTotalMembers(); ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>
<?php elseif ($reportType === 'member'): 
$members = $pdo->query("SELECT * FROM members ORDER BY first_name")->fetchAll();
?>
<div class="card">
    <div class="card-header"><h5>Member Report</h5></div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th>Membership</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Total Investment</th>
                    <th>Total Paid</th>
                    <th>Due</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m):
                    $investment = getMemberTotalInvestment($m['id']);
                    $paid = getMemberTotalPaid($m['id']);
                    $due = $investment - $paid;
                ?>
                <tr>
                    <td><?php echo $m['membership_number']; ?></td>
                    <td><?php echo $m['first_name'] . ' ' . $m['last_name']; ?></td>
                    <td><?php echo $m['phone']; ?></td>
                    <td><?php echo formatCurrency($investment); ?></td>
                    <td><?php echo formatCurrency($paid); ?></td>
                    <td><?php echo formatCurrency($due); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once 'layout-end.php'; ?>
