<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin', 'accountant']);

$pageTitle = 'Member Reports';
require_once 'layout.php';

$status = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT * FROM members WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR membership_number LIKE ? OR phone LIKE ?)";
    $params = array_fill(0, 4, "%$search%");
}
if ($status) {
    $sql .= " AND member_status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name, membership, phone..." value="<?php echo $search; ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo ($status === 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="inactive" <?php echo ($status === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-success w-100" onclick="window.print()">Print</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Member Report - Total: <?php echo count($members); ?> Members</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Membership #</th>
                        <th>Name</th>
                        <th>Father/Husband</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Investment</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalInvestment = 0;
                    $totalPaid = 0;
                    $totalDue = 0;
                    
                    foreach ($members as $m): 
                        $investment = getMemberTotalInvestment($m['id']);
                        $paid = getMemberTotalPaid($m['id']);
                        $due = $investment - $paid;
                        
                        $totalInvestment += $investment;
                        $totalPaid += $paid;
                        $totalDue += $due;
                    ?>
                    <tr>
                        <td><?php echo $m['membership_number']; ?></td>
                        <td><?php echo $m['first_name'] . ' ' . $m['last_name']; ?></td>
                        <td><?php echo $m['father_name']; ?></td>
                        <td><?php echo $m['phone']; ?></td>
                        <td><?php echo $m['email']; ?></td>
                        <td><?php echo formatCurrency($investment); ?></td>
                        <td><?php echo formatCurrency($paid); ?></td>
                        <td><?php echo formatCurrency($due); ?></td>
                        <td>
                            <?php if ($m['member_status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold; background: #f8f9fa;">
                        <td colspan="5">TOTAL</td>
                        <td><?php echo formatCurrency($totalInvestment); ?></td>
                        <td><?php echo formatCurrency($totalPaid); ?></td>
                        <td><?php echo formatCurrency($totalDue); ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>