<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin', 'accountant']);

$paymentId = (int) ($_GET['id'] ?? 0);
$action = sanitize($_GET['action'] ?? 'approve');
$allowedActions = ['approve', 'reject'];

if (!$paymentId || !in_array($action, $allowedActions, true)) {
    $_SESSION['error'] = 'Invalid payment action.';
    redirect(BASE_URL . '/payments.php');
}

$paymentStmt = $pdo->prepare("SELECT id, approval_status FROM payments WHERE id = ?");
$paymentStmt->execute([$paymentId]);
$payment = $paymentStmt->fetch();

if (!$payment) {
    $_SESSION['error'] = 'Payment not found.';
    redirect(BASE_URL . '/payments.php');
}

if ($payment['approval_status'] !== 'pending') {
    $_SESSION['error'] = 'Only pending payments can be updated.';
    redirect(BASE_URL . '/payments.php');
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';
$stmt = $pdo->prepare("UPDATE payments SET approval_status = ?, approved_by = ?, approval_date = NOW() WHERE id = ?");
$stmt->execute([$newStatus, getCurrentUserId(), $paymentId]);

logAudit(strtoupper($newStatus), 'payments', $paymentId, ['approval_status' => 'pending'], ['approval_status' => $newStatus]);

$_SESSION['success'] = 'Payment ' . $newStatus . ' successfully.';
redirect(BASE_URL . '/payments.php');
