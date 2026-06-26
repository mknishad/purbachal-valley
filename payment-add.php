<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin', 'accountant', 'member']);

$currentUserRole = getCurrentUserRole();
$currentUserId = getCurrentUserId();
$isMemberUser = $currentUserRole === 'member';
$memberAccount = null;

if ($isMemberUser) {
    $memberStmt = $pdo->prepare("SELECT id, first_name, last_name, membership_number FROM members WHERE user_id = ? AND member_status = 'active'");
    $memberStmt->execute([$currentUserId]);
    $memberAccount = $memberStmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isMemberUser) {
        if (!$memberAccount) {
            $_SESSION['error'] = 'No active member profile is linked to your account.';
            redirect(BASE_URL . '/my-payments.php');
        }
        $memberId = (int) $memberAccount['id'];
    } else {
        $memberId = (int) sanitize($_POST['member_id'] ?? 0);
    }

    $projectId = (int) sanitize($_POST['project_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $paymentMethod = sanitize($_POST['payment_method'] ?? '');
    $paymentDate = sanitize($_POST['payment_date'] ?? '');
    $bankName = sanitize($_POST['bank_name'] ?? '');
    $chequeNumber = sanitize($_POST['cheque_number'] ?? '');
    $transactionId = sanitize($_POST['transaction_id'] ?? '');
    $referenceNumber = sanitize($_POST['reference_number'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    $allowedMethods = ['cash', 'bank_transfer', 'cheque', 'mobile_banking', 'card', 'other'];

    if (!$memberId || $amount <= 0 || empty($paymentDate) || empty($paymentMethod)) {
        $_SESSION['error'] = 'Please fill all required fields with valid values.';
        redirect(BASE_URL . '/payment-add.php' . (!$isMemberUser && $memberId ? '?member_id=' . $memberId : ''));
    }

    if (!in_array($paymentMethod, $allowedMethods, true)) {
        $_SESSION['error'] = 'Please select a valid payment method.';
        redirect(BASE_URL . '/payment-add.php' . (!$isMemberUser && $memberId ? '?member_id=' . $memberId : ''));
    }

    $memberCheck = $pdo->prepare("SELECT id FROM members WHERE id = ? AND member_status = 'active'");
    $memberCheck->execute([$memberId]);
    if (!$memberCheck->fetch()) {
        $_SESSION['error'] = 'Please select a valid active member.';
        redirect(BASE_URL . '/payment-add.php');
    }

    if ($projectId) {
        $projectCheck = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND status != 'completed'");
        $projectCheck->execute([$projectId]);
        if (!$projectCheck->fetch()) {
            $_SESSION['error'] = 'Please select a valid active project.';
            redirect(BASE_URL . '/payment-add.php' . (!$isMemberUser ? '?member_id=' . $memberId : ''));
        }
    }

    $date = DateTime::createFromFormat('Y-m-d', $paymentDate);
    if (!$date || $date->format('Y-m-d') !== $paymentDate) {
        $_SESSION['error'] = 'Payment date must be a valid date in YYYY-MM-DD format.';
        redirect(BASE_URL . '/payment-add.php' . (!$isMemberUser ? '?member_id=' . $memberId : ''));
    }

    $paymentNumber = generatePaymentNumber();
    $autoApprove = getSetting('auto_approval', 'yes');
    $approvalStatus = (!$isMemberUser && $autoApprove === 'yes') ? 'approved' : 'pending';
    $approvalDate = $approvalStatus === 'approved' ? date('Y-m-d H:i:s') : null;
    $approvedBy = $approvalStatus === 'approved' ? $currentUserId : null;

    $stmt = $pdo->prepare("INSERT INTO payments (
        payment_number, member_id, project_id, amount, payment_method, payment_date,
        bank_name, cheque_number, transaction_id, reference_number, notes,
        approval_status, received_by, approved_by, approval_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $paymentNumber,
        $memberId,
        $projectId ?: null,
        $amount,
        $paymentMethod,
        $paymentDate,
        $bankName,
        $chequeNumber,
        $transactionId,
        $referenceNumber,
        $notes,
        $approvalStatus,
        $currentUserId,
        $approvedBy,
        $approvalDate
    ]);

    $paymentId = $pdo->lastInsertId();
    logAudit('CREATE', 'payments', $paymentId);

    if ($isMemberUser) {
        $_SESSION['success'] = 'Payment submitted successfully. Payment No: ' . $paymentNumber . '. It is waiting for admin approval.';
        redirect(BASE_URL . '/my-payments.php');
    }

    $_SESSION['success'] = 'Payment added successfully! Payment No: ' . $paymentNumber;
    redirect(BASE_URL . '/payments.php');
}

$pageTitle = $isMemberUser ? 'Submit Payment' : 'Add Payment';
require_once 'layout.php';

$memberId = $isMemberUser ? (int) ($memberAccount['id'] ?? 0) : (int) sanitize($_GET['member_id'] ?? 0);
$projectId = (int) sanitize($_GET['project_id'] ?? 0);

$members = [];
if (!$isMemberUser) {
    $members = $pdo->query("SELECT id, first_name, last_name, membership_number FROM members WHERE member_status = 'active' ORDER BY first_name")->fetchAll();
}
$projects = $pdo->query("SELECT id, project_name FROM projects WHERE status != 'completed' ORDER BY project_name")->fetchAll();
?>

<?php if ($isMemberUser && !$memberAccount): ?>
    <div class="alert alert-warning">No active member profile is linked to your user account yet.</div>
<?php else: ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $isMemberUser ? 'Submit Your Payment' : 'Add New Payment'; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <?php if ($isMemberUser): ?>
                    <input type="hidden" name="member_id" value="<?php echo $memberId; ?>">
                    <div class="col-md-6">
                        <label class="form-label">Member</label>
                        <input type="text" class="form-control" value="<?php echo sanitize($memberAccount['first_name'] . ' ' . $memberAccount['last_name'] . ' (' . $memberAccount['membership_number'] . ')'); ?>" disabled>
                    </div>
                    <?php else: ?>
                    <div class="col-md-6">
                        <label class="form-label">Member *</label>
                        <select name="member_id" id="paymentMemberSelect" class="form-select select2" required data-summary-url="<?php echo BASE_URL; ?>/payment-member-summary.php">
                            <option value="">Select Member</option>
                            <?php foreach ($members as $member): ?>
                            <option value="<?php echo $member['id']; ?>" <?php echo $memberId == $member['id'] ? 'selected' : ''; ?>>
                                <?php echo sanitize($member['first_name'] . ' ' . $member['last_name'] . ' (' . $member['membership_number'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                        <label class="form-label">Project</label>
                        <select name="project_id" class="form-select">
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $proj): ?>
                            <option value="<?php echo $proj['id']; ?>" <?php echo $projectId == $proj['id'] ? 'selected' : ''; ?>><?php echo sanitize($proj['project_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount *</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Date *</label>
                        <input type="text" name="payment_date" class="form-control datepicker" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Method *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="card">Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cheque/Transaction No</label>
                        <input type="text" name="transaction_id" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?php echo $isMemberUser ? 'Submit Payment' : 'Save Payment'; ?></button>
                        <a href="<?php echo $isMemberUser ? 'my-payments.php' : 'payments.php'; ?>" class="btn btn-outline-primary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Member Summary</h6>
            </div>
            <div class="card-body" id="member-summary">
                <p class="text-muted mb-0">Select a member to see details</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const summaryBox = document.getElementById('member-summary');
    const memberSelect = document.getElementById('paymentMemberSelect');
    const fixedMemberId = <?php echo json_encode($isMemberUser ? $memberId : null); ?>;
    const summaryUrl = <?php echo json_encode(BASE_URL . '/payment-member-summary.php'); ?>;

    function loadMemberSummary(memberId) {
        if (!memberId) {
            summaryBox.innerHTML = '<p class="text-muted mb-0">Select a member to see details</p>';
            return;
        }

        summaryBox.innerHTML = '<p class="text-muted mb-0">Loading summary...</p>';
        fetch(summaryUrl + '?member_id=' + encodeURIComponent(memberId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Summary request failed');
                return response.text();
            })
            .then(function (html) {
                summaryBox.innerHTML = html;
            })
            .catch(function () {
                summaryBox.innerHTML = '<p class="text-danger mb-0">Could not load member summary.</p>';
            });
    }

    if (memberSelect) {
        memberSelect.addEventListener('change', function () {
            loadMemberSummary(memberSelect.value);
        });
        loadMemberSummary(memberSelect.value);
    } else if (fixedMemberId) {
        loadMemberSummary(fixedMemberId);
    }
});
</script>
<?php endif; ?>

<?php require_once 'layout-end.php'; ?>
