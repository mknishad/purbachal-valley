<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin', 'accountant', 'member']);

$memberId = (int) ($_GET['member_id'] ?? 0);

if (getCurrentUserRole() === 'member') {
    $memberStmt = $pdo->prepare("SELECT id FROM members WHERE user_id = ?");
    $memberStmt->execute([getCurrentUserId()]);
    $linkedMember = $memberStmt->fetch();

    if (!$linkedMember || $memberId !== (int) $linkedMember['id']) {
        http_response_code(403);
        echo '<p class="text-danger mb-0">Access denied.</p>';
        exit;
    }
}

if (!$memberId) {
    echo '<p class="text-muted mb-0">Select a member to see details</p>';
    exit;
}

$memberStmt = $pdo->prepare("SELECT id, membership_number, first_name, last_name, email, phone, member_status, kyc_status FROM members WHERE id = ?");
$memberStmt->execute([$memberId]);
$member = $memberStmt->fetch();

if (!$member) {
    echo '<p class="text-danger mb-0">Member not found.</p>';
    exit;
}

$totalInvestment = (float) getMemberTotalInvestment($memberId);
$totalPaid = (float) getMemberTotalPaid($memberId);
$dueAmount = max($totalInvestment - $totalPaid, 0);

$pendingStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE member_id = ? AND approval_status = 'pending'");
$pendingStmt->execute([$memberId]);
$pendingAmount = (float) ($pendingStmt->fetch()['total'] ?? 0);

$latestStmt = $pdo->prepare("SELECT payment_number, amount, payment_date, approval_status FROM payments WHERE member_id = ? ORDER BY payment_date DESC, id DESC LIMIT 3");
$latestStmt->execute([$memberId]);
$latestPayments = $latestStmt->fetchAll();
?>

<div class="member-summary-stack">
    <div>
        <h6 class="mb-1"><?php echo sanitize($member['first_name'] . ' ' . $member['last_name']); ?></h6>
        <div class="text-muted small"><?php echo sanitize($member['membership_number']); ?></div>
    </div>

    <div class="member-summary-meta">
        <div><i class="fas fa-phone"></i> <?php echo sanitize($member['phone'] ?: 'N/A'); ?></div>
        <div><i class="fas fa-envelope"></i> <?php echo sanitize($member['email'] ?: 'N/A'); ?></div>
    </div>

    <div class="summary-metric-grid">
        <div class="summary-metric">
            <span>Investment</span>
            <strong><?php echo formatCurrency($totalInvestment); ?></strong>
        </div>
        <div class="summary-metric">
            <span>Paid</span>
            <strong><?php echo formatCurrency($totalPaid); ?></strong>
        </div>
        <div class="summary-metric">
            <span>Pending</span>
            <strong><?php echo formatCurrency($pendingAmount); ?></strong>
        </div>
        <div class="summary-metric">
            <span>Due</span>
            <strong><?php echo formatCurrency($dueAmount); ?></strong>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <span class="badge bg-success"><?php echo sanitize(ucfirst($member['member_status'])); ?></span>
        <span class="badge bg-info"><?php echo sanitize(ucfirst($member['kyc_status'] ?: 'Pending')); ?> KYC</span>
    </div>

    <div>
        <h6 class="summary-section-title">Recent Payments</h6>
        <?php if (count($latestPayments) > 0): ?>
            <div class="summary-payment-list">
                <?php foreach ($latestPayments as $payment): ?>
                    <div class="summary-payment-item">
                        <div>
                            <strong><?php echo sanitize($payment['payment_number']); ?></strong>
                            <span><?php echo formatDate($payment['payment_date']); ?></span>
                        </div>
                        <div class="text-end">
                            <strong><?php echo formatCurrency($payment['amount']); ?></strong>
                            <span><?php echo sanitize(ucfirst($payment['approval_status'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted small mb-0">No payments found.</p>
        <?php endif; ?>
    </div>
</div>
