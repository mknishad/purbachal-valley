<?php
require_once 'auth.php';
requireLogin();
requireRole(['admin', 'accountant', 'member']);

$pageTitle = 'Payments';
require_once 'layout.php';

$search = sanitize($_GET['search'] ?? '');
$status = sanitize($_GET['status'] ?? '');
$projectId = sanitize($_GET['project_id'] ?? '');

$sql = "SELECT p.*, m.first_name, m.last_name, m.membership_number, pr.project_name 
    FROM payments p 
    LEFT JOIN members m ON p.member_id = m.id 
    LEFT JOIN projects pr ON p.project_id = pr.id 
    WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (m.first_name LIKE ? OR m.last_name LIKE ? OR p.payment_number LIKE ? OR p.reference_number LIKE ?)";
    $params = array_fill(0, 4, "%$search%");
}
if ($status) {
    $sql .= " AND p.approval_status = ?";
    $params[] = $status;
}
if ($projectId) {
    $sql .= " AND p.project_id = ?";
    $params[] = $projectId;
}

$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll();

$projects = $pdo->query("SELECT id, project_name FROM projects WHERE status != 'completed' ORDER BY project_name")->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-10">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search member, payment no..." value="<?= $search ?>">
            <select name="project_id" class="form-select" style="width: 200px;">
                <option value="">All Projects</option>
                <?php foreach ($projects as $proj): ?>
                <option value="<?= $proj['id'] ?>" <?= $projectId == $proj['id'] ? 'selected' : '' ?>><?= $proj['project_name'] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-select" style="width: 150px;">
                <option value="">All Status</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="col-md-2 text-end">
        <a href="payment-add.php" class="btn btn-success"><i class="fas fa-plus"></i> Add Payment</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payment No</th>
                        <th>Member</th>
                        <th>Project</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= formatDate($payment['payment_date']) ?></td>
                        <td><?= $payment['payment_number'] ?></td>
                        <td>
                            <strong><?= sanitize($payment['first_name'] . ' ' . $payment['last_name']) ?></strong><br>
                            <small class="text-muted"><?= $payment['membership_number'] ?></small>
                        </td>
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
                            <a href="receipt.php?id=<?= $payment['id'] ?>" class="btn btn-sm btn-info" target="_blank"><i class="fas fa-print"></i></a>
                            <?php if ($payment['approval_status'] === 'pending'): ?>
                            <a href="payment-approve.php?id=<?= $payment['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>