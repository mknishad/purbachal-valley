<?php
require_once 'auth.php';
requireLogin();
requireRole(['admin', 'accountant']);

$pageTitle = 'Expenses';
require_once 'layout.php';

$projectId = sanitize($_GET['project_id'] ?? '');
$category = sanitize($_GET['category'] ?? '');

$sql = "SELECT e.*, p.project_name, u.full_name as created_by_name 
    FROM expenses e 
    LEFT JOIN projects p ON e.project_id = p.id 
    LEFT JOIN users u ON e.created_by = u.id 
    WHERE 1=1";
$params = [];

if ($projectId) {
    $sql .= " AND e.project_id = ?";
    $params[] = $projectId;
}
if ($category) {
    $sql .= " AND e.expense_category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY e.payment_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$expenses = $stmt->fetchAll();

$projects = $pdo->query("SELECT id, project_name FROM projects ORDER BY project_name")->fetchAll();

$totalExpenses = getTotalExpenses();
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card orange">
            <div class="card-body">
                <h6>Total Expenses</h6>
                <h4><?= formatCurrency($totalExpenses) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <form method="GET" class="d-flex gap-2">
            <select name="project_id" class="form-select" style="width: 200px;">
                <option value="">All Projects</option>
                <?php foreach ($projects as $proj): ?>
                <option value="<?= $proj['id'] ?>" <?= $projectId == $proj['id'] ? 'selected' : '' ?>><?= $proj['project_name'] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="category" class="form-select" style="width: 180px;">
                <option value="">All Categories</option>
                <option value="land_purchase" <?php echo ($category === 'land_purchase' ? 'selected' : ''); ?>>Land Purchase</option>
                <option value="development" <?php echo ($category === 'development' ? 'selected' : ''); ?>>Development</option>
                <option value="legal" <?php echo ($category === 'legal' ? 'selected' : ''); ?>>Legal</option>
                <option value="registration" <?php echo ($category === 'registration' ? 'selected' : ''); ?>>Registration</option>
                <option value="tax" <?php echo ($category === 'tax' ? 'selected' : ''); ?>>Tax</option>
                <option value="office" <?php echo ($category === 'office' ? 'selected' : ''); ?>>Office</option>
                <option value="salary" <?php echo ($category === 'salary' ? 'selected' : ''); ?>>Salary</option>
                <option value="other" <?php echo ($category === 'other' ? 'selected' : ''); ?>>Other</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Expense List</h5>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="fas fa-plus"></i> Add Expense</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Expense No</th>
                        <th>Category</th>
                        <th>Project</th>
                        <th>Payee</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= formatDate($expense['payment_date']) ?></td>
                        <td><?= $expense['expense_number'] ?></td>
                        <td><?= ucwords(str_replace('_', ' ', $expense['expense_category'])) ?></td>
                        <td><?= sanitize($expense['project_name'] ?? 'N/A') ?></td>
                        <td><?= sanitize($expense['payee_name']) ?></td>
                        <td><?= formatCurrency($expense['amount']) ?></td>
                        <td>
                            <?php if ($expense['status'] === 'paid'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php elseif ($expense['status'] === 'approved'): ?>
                                <span class="badge bg-info">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="expense-save.php">
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Project</label>
                        <select name="project_id" class="form-select">
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $proj): ?>
                            <option value="<?= $proj['id'] ?>"><?= $proj['project_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category *</label>
                        <select name="expense_category" class="form-select" required>
                            <option value="land_purchase">Land Purchase</option>
                            <option value="development">Development</option>
                            <option value="legal">Legal</option>
                            <option value="registration">Registration</option>
                            <option value="tax">Tax</option>
                            <option value="office">Office</option>
                            <option value="salary">Salary</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount *</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Date *</label>
                        <input type="text" name="payment_date" class="form-control datepicker" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payee Name</label>
                        <input type="text" name="payee_name" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>