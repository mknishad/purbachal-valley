<?php
require_once 'config.php';
require_once 'auth.php';
requireLogin();
requireRole(['admin', 'accountant']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = sanitize($_POST['project_id'] ?? '');
    $expenseCategory = sanitize($_POST['expense_category']);
    $amount = floatval($_POST['amount']);
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash');
    $paymentDate = sanitize($_POST['payment_date']);
    $payeeName = sanitize($_POST['payee_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    
    if (empty($expenseCategory) || empty($amount) || empty($paymentDate)) {
        $_SESSION['error'] = 'Please fill required fields';
        redirect(BASE_URL . '/expenses.php');
    }
    
    $expenseNumber = 'EXP-' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO expenses (
        expense_number, project_id, expense_category, amount, payment_method, 
        payment_date, payee_name, description, status, created_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
    
    $stmt->execute([
        $expenseNumber, $projectId ?: null, $expenseCategory, $amount, $paymentMethod,
        $paymentDate, $payeeName, $description, getCurrentUserId()
    ]);
    
    logAudit('CREATE', 'expenses', $pdo->lastInsertId());
    
    $_SESSION['success'] = 'Expense added successfully!';
    redirect(BASE_URL . '/expenses.php');
} else {
    redirect(BASE_URL . '/expenses.php');
}