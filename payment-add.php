<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin', 'accountant']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = sanitize($_POST['member_id']);
    $projectId = sanitize($_POST['project_id']);
    $amount = floatval($_POST['amount']);
    $paymentMethod = sanitize($_POST['payment_method']);
    $paymentDate = sanitize($_POST['payment_date']);
    $bankName = sanitize($_POST['bank_name'] ?? '');
    $chequeNumber = sanitize($_POST['cheque_number'] ?? '');
    $transactionId = sanitize($_POST['transaction_id'] ?? '');
    $referenceNumber = sanitize($_POST['reference_number'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($memberId) || empty($amount) || empty($paymentDate)) {
        $_SESSION['error'] = 'Please fill required fields';
        redirect(BASE_URL . '/payment-add.php');
    }

    $date = DateTime::createFromFormat('Y-m-d', $paymentDate);
    if (!$date || $date->format('Y-m-d') !== $paymentDate) {
        $_SESSION['error'] = 'Payment date must be a valid date in YYYY-MM-DD format';
        redirect(BASE_URL . '/payment-add.php');
    }
    
    $paymentNumber = generatePaymentNumber();
    $autoApprove = getSetting('auto_approval', 'yes');
    $approvalStatus = $autoApprove === 'yes' ? 'approved' : 'pending';
    
    $stmt = $pdo->prepare("INSERT INTO payments (
        payment_number, member_id, project_id, amount, payment_method, payment_date,
        bank_name, cheque_number, transaction_id, reference_number, notes,
        approval_status, received_by, approval_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $paymentNumber, $memberId, $projectId ?: null, $amount, $paymentMethod, $paymentDate,
        $bankName, $chequeNumber, $transactionId, $referenceNumber, $notes,
        $approvalStatus, getCurrentUserId(), $approvalStatus === 'approved' ? date('Y-m-d H:i:s') : null
    ]);
    
    $paymentId = $pdo->lastInsertId();
    logAudit('CREATE', 'payments', $paymentId);
    
    $_SESSION['success'] = 'Payment added successfully! Payment No: ' . $paymentNumber;
    redirect(BASE_URL . '/payments.php');
}

$pageTitle = 'Add Payment';
require_once 'layout.php';

$memberId = sanitize($_GET['member_id'] ?? '');
$projectId = sanitize($_GET['project_id'] ?? '');

$members = $pdo->query("SELECT id, first_name, last_name, membership_number FROM members WHERE member_status = 'active' ORDER BY first_name")->fetchAll();
$projects = $pdo->query("SELECT id, project_name FROM projects WHERE status != 'completed' ORDER BY project_name")->fetchAll();
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add New Payment</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Member *</label>
                        <select name="member_id" class="form-select select2" required>
                            <option value="">Select Member</option>
                            <?php foreach ($members as $member): ?>
                            <option value="<?= $member['id'] ?>" <?= $memberId == $member['id'] ? 'selected' : '' ?>>
                                <?= $member['first_name'] . ' ' . $member['last_name'] . ' (' . $member['membership_number'] . ')' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Project</label>
                        <select name="project_id" class="form-select">
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $proj): ?>
                            <option value="<?= $proj['id'] ?>" <?= $projectId == $proj['id'] ? 'selected' : '' ?>><?= $proj['project_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount *</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Date *</label>
                        <input type="text" name="payment_date" class="form-control datepicker" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Method *</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="card">Card</option>
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
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Payment</button>
                        <a href="payments.php" class="btn btn-outline-primary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Member Summary</h6>
            </div>
            <div class="card-body" id="member-summary">
                <p class="text-muted">Select a member to see details</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>
