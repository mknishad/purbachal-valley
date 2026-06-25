<?php
require_once 'config.php';
require_once 'auth.php';
requireLogin();
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = sanitize($_POST['project_id']);
    $plotNumber = sanitize($_POST['plot_number']);
    $plotSize = floatval($_POST['plot_size'] ?? 0);
    $sizeUnit = sanitize($_POST['size_unit'] ?? 'sqft');
    $pricePerUnit = floatval($_POST['price_per_unit'] ?? 0);
    $totalPrice = floatval($_POST['total_price'] ?? 0);
    $plotType = sanitize($_POST['plot_type'] ?? 'residential');
    
    if (empty($projectId) || empty($plotNumber)) {
        $_SESSION['error'] = 'Project and plot number are required';
        redirect(BASE_URL . '/plots.php');
    }
    
    if (!$totalPrice && $plotSize && $pricePerUnit) {
        $totalPrice = $plotSize * $pricePerUnit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO plots (project_id, plot_number, plot_size, size_unit, price_per_unit, total_price, plot_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'available')");
    $stmt->execute([$projectId, $plotNumber, $plotSize, $sizeUnit, $pricePerUnit, $totalPrice, $plotType]);
    
    logAudit('CREATE', 'plots', $pdo->lastInsertId());
    
    $_SESSION['success'] = 'Plot added successfully!';
    redirect(BASE_URL . '/plots.php');
} else {
    redirect(BASE_URL . '/plots.php');
}