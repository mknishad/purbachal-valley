<?php
require_once 'auth.php';
requireLogin();
requireRole(['admin', 'accountant', 'member']);

$pageTitle = 'My Payments';
require_once 'layout.php';

$currentUserId = getCurrentUserId();

$memberStmt = $pdo->prepare("SELECT id FROM members WHERE user_id = ?");
$memberStmt->execute([$currentUserId]);
$member = $memberStmt->fetch();

if ($member) {
    $payments = $pdo->prepare("SELECT p.*, pr.project_name 
        FROM payments p 
        LEFT JOIN projects pr ON p.project_id = pr.id 
        WHERE p.member_id = ? 
        ORDER BY p.payment_date DESC");
    $payments->execute([$member['id']]);
    $payments = $payments->fetchAll();
    
    $totalPaid = getMemberTotalPaid($member['id']);
    $totalInvestment = getMemberTotalInvestment($member['id']);
    $due = $totalInvestment - $totalPaid;
} else {
    $payments = [];
    $totalPaid = 0;
    $totalInvestment = 0;
    $due = 0;
}
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card blue">
            <div class="card-body">
                <h6>Total Investment</h6>
                <h4><?= formatCurrency($totalInvestment) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card green">
            <div class="card-body">
                <h6>Total Paid</h6>
                <h4><?= formatCurrency($totalPaid) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card red">
            <div class="card-body">
                <h6>Due Amount</h6>
                <h4><?= formatCurrency($due) ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <h5 class="mb-0">Payment History</h5>
            <?php if (getCurrentUserRole() === 'member'): ?>
            <a href="payment-add.php" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Submit Payment</a>
            <?php endif; ?>
        </div>
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
                        <th>Method</th>
                        <th>Status</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($payments) > 0): ?>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= formatDate($payment['payment_date']) ?></td>
                            <td><?= $payment['payment_number'] ?></td>
                            <td><?= sanitize($payment['project_name'] ?? 'N/A') ?></td>
                            <td><?= formatCurrency($payment['amount']) ?></td>
                            <td><?= ucfirst($payment['payment_method']) ?></td>
                            <td>
                                <?php if ($payment['approval_status'] === 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif ($payment['approval_status'] === 'pending'): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($payment['approval_status'] === 'approved'): ?>
                                <a href="receipt.php?id=<?= $payment['id'] ?>" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No payments found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>
