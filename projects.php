<?php
require_once 'auth.php';
requireLogin();
requireRole(['admin']);

$pageTitle = 'Projects';
require_once 'layout.php';

$status = sanitize($_GET['status'] ?? '');

$sql = "SELECT * FROM projects WHERE 1=1";
if ($status) $sql .= " AND status = '$status'";
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->query($sql);
$projects = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-8"></div>
    <div class="col-md-4 text-end">
        <a href="project-add.php" class="btn btn-success"><i class="fas fa-plus"></i> Add Project</a>
    </div>
</div>

<div class="row">
    <?php foreach ($projects as $project): 
        $collection = getProjectTotalCollection($project['id']);
        $expenses = getProjectTotalExpenses($project['id']);
    ?>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><?= sanitize($project['project_name']) ?></h6>
                <?php if ($project['status'] === 'completed'): ?>
                    <span class="badge bg-success">Completed</span>
                <?php elseif ($project['status'] === 'development'): ?>
                    <span class="badge bg-primary">Development</span>
                <?php elseif ($project['status'] === 'acquisition'): ?>
                    <span class="badge bg-info">Acquisition</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Planning</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p><i class="fas fa-map-marker-alt me-2"></i><?= sanitize($project['location'] ?? 'N/A') ?></p>
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Total Area</small>
                        <p><?= number_format($project['total_land_area']) ?> <?= $project['land_unit'] ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Acquisition Cost</small>
                        <p><?= formatCurrency($project['total_acquisition_cost']) ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Collection</small>
                        <p class="text-success"><?= formatCurrency($collection) ?></p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Expenses</small>
                        <p class="text-danger"><?= formatCurrency($expenses) ?></p>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="project-view.php?id=<?= $project['id'] ?>" class="btn btn-sm btn-info">View</a>
                <a href="project-edit.php?id=<?= $project['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="plots.php?project_id=<?= $project['id'] ?>" class="btn btn-sm btn-primary">Plots</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once 'layout-end.php'; ?>