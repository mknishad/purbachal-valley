<?php
require_once 'auth.php';
requireLogin();
requireRole(['admin', 'accountant']);

$pageTitle = 'Members';
require_once 'layout.php';

$search = sanitize($_GET['search'] ?? '');
$status = sanitize($_GET['status'] ?? '');

$sql = "SELECT m.*, u.username, u.email as user_email FROM members m LEFT JOIN users u ON m.user_id = u.id WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (m.first_name LIKE ? OR m.last_name LIKE ? OR m.membership_number LIKE ? OR m.phone LIKE ? OR m.nid_number LIKE ?)";
    $params = array_fill(0, 5, "%$search%");
}
if ($status) {
    $sql .= " AND m.member_status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY m.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-8">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search by name, membership, phone, NID..." value="<?= $search ?>">
            <select name="status" class="form-select" style="width: 150px;">
                <option value="">All Status</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="expelled" <?= $status === 'expelled' ? 'selected' : '' ?>>Expelled</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="col-md-4 text-end">
        <a href="member-add.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Member</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member Info</th>
                        <th>Contact</th>
                        <th>NID</th>
                        <th>Investment</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): 
                        $totalInvestment = getMemberTotalInvestment($member['id']);
                        $totalPaid = getMemberTotalPaid($member['id']);
                        $due = $totalInvestment - $totalPaid;
                    ?>
                    <tr>
                        <td><?= $member['membership_number'] ?></td>
                        <td>
                            <strong><?= sanitize($member['first_name'] . ' ' . $member['last_name']) ?></strong><br>
                            <small class="text-muted"><?= $member['father_name'] ?></small>
                        </td>
                        <td>
                            <?= sanitize($member['phone']) ?><br>
                            <small class="text-muted"><?= sanitize($member['email']) ?></small>
                        </td>
                        <td><?= sanitize($member['nid_number']) ?></td>
                        <td><?= formatCurrency($totalInvestment) ?></td>
                        <td><span class="text-success"><?= formatCurrency($totalPaid) ?></span></td>
                        <td><span class="text-danger"><?= formatCurrency($due) ?></span></td>
                        <td>
                            <?php if ($member['member_status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php elseif ($member['member_status'] === 'inactive'): ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Expelled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="member-view.php?id=<?= $member['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <a href="member-edit.php?id=<?= $member['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="payment-add.php?member_id=<?= $member['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-plus"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>