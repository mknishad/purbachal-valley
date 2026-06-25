<?php
require_once 'auth.php';
requireLogin();
requireRole(['admin']);

$pageTitle = 'Plots / Units';
require_once 'layout.php';

$projectId = sanitize($_GET['project_id'] ?? '');

$projects = $pdo->query("SELECT id, project_name FROM projects ORDER BY project_name")->fetchAll();

$sql = "SELECT pl.*, p.project_name FROM plots pl LEFT JOIN projects p ON pl.project_id = p.id WHERE 1=1";
if ($projectId) $sql .= " AND pl.project_id = $projectId";
$sql .= " ORDER BY pl.plot_number";

$plots = $pdo->query($sql)->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-8">
        <form method="GET" class="d-flex gap-2">
            <select name="project_id" class="form-select" style="width: 250px;" onchange="this.form.submit()">
                <option value="">All Projects</option>
                <?php foreach ($projects as $proj): ?>
                <option value="<?= $proj['id'] ?>" <?= $projectId == $proj['id'] ? 'selected' : '' ?>><?= $proj['project_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#plotModal"><i class="fas fa-plus"></i> Add Plot</button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Plot No</th>
                    <th>Project</th>
                    <th>Size</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plots as $plot): ?>
                <tr>
                    <td><?= $plot['plot_number'] ?></td>
                    <td><?= $plot['project_name'] ?></td>
                    <td><?= number_format($plot['plot_size']) ?> <?= $plot['size_unit'] ?></td>
                    <td><?= ucfirst($plot['plot_type']) ?></td>
                    <td><?= formatCurrency($plot['total_price']) ?></td>
                    <td>
                        <?php if ($plot['status'] === 'available'): ?>
                            <span class="badge bg-success">Available</span>
                        <?php elseif ($plot['status'] === 'sold'): ?>
                            <span class="badge bg-danger">Sold</span>
                        <?php elseif ($plot['status'] === 'allocated'): ?>
                            <span class="badge bg-primary">Allocated</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= $plot['status'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        <a href="#" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="plotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Plot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="plot-save.php">
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Project *</label>
                        <select name="project_id" class="form-select" required>
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $proj): ?>
                            <option value="<?= $proj['id'] ?>"><?= $proj['project_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Plot Number *</label>
                        <input type="text" name="plot_number" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Size</label>
                        <input type="number" name="plot_size" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unit</label>
                        <select name="size_unit" class="form-select">
                            <option value="sqft">Sq Ft</option>
                            <option value="sqm">Sq M</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Price Per Unit</label>
                        <input type="number" name="price_per_unit" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Price</label>
                        <input type="number" name="total_price" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Plot Type</label>
                        <select name="plot_type" class="form-select">
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="apartment">Apartment</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Plot</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>