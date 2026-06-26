<?php
require_once 'auth.php';
requireLogin();
requireRole(['admin']);

$pageTitle = 'Add Project';
require_once 'layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = sanitize($_POST['project_name']);
    $projectCode = sanitize($_POST['project_code'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $totalLandArea = floatval($_POST['total_land_area'] ?? 0);
    $landUnit = sanitize($_POST['land_unit'] ?? 'sqft');
    $totalAcquisitionCost = floatval($_POST['total_acquisition_cost'] ?? 0);
    $acquisitionDate = sanitize($_POST['acquisition_date'] ?? '');
    $projectType = sanitize($_POST['project_type'] ?? 'residential');
    
    if (empty($projectName)) {
        $_SESSION['error'] = 'Project name is required';
        redirect(BASE_URL . '/project-add.php');
    }
    
    $stmt = $pdo->prepare("INSERT INTO projects (
        project_name, project_code, location, description, total_land_area, land_unit,
        total_acquisition_cost, acquisition_date, project_type, status, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'planning', ?)");
    
    $stmt->execute([
        $projectName, $projectCode, $location, $description, $totalLandArea, $landUnit,
        $totalAcquisitionCost, $acquisitionDate, $projectType, getCurrentUserId()
    ]);
    
    $projectId = $pdo->lastInsertId();
    logAudit('CREATE', 'projects', $projectId);
    
    $_SESSION['success'] = 'Project added successfully!';
    redirect(BASE_URL . '/projects.php');
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add New Project</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Project Name *</label>
                        <input type="text" name="project_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Project Code</label>
                        <input type="text" name="project_code" class="form-control" placeholder="e.g., PV-001">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Land Area</label>
                        <input type="number" name="total_land_area" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unit</label>
                        <select name="land_unit" class="form-select">
                            <option value="sqft">Sq Ft</option>
                            <option value="sqm">Sq M</option>
                            <option value="decimal">Decimal</option>
                            <option value="bigha">Bigha</option>
                            <option value="acre">Acre</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Project Type</label>
                        <select name="project_type" class="form-select">
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="mixed">Mixed</option>
                            <option value="apartment">Apartment</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Acquisition Cost</label>
                        <input type="number" name="total_acquisition_cost" class="form-control" step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Acquisition Date</label>
                        <input type="text" name="acquisition_date" class="form-control datepicker">
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Project</button>
                        <a href="projects.php" class="btn btn-outline-primary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>
