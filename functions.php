<?php
function getSetting($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

function generateMembershipNumber() {
    global $pdo;
    $prefix = getSetting('membership_prefix', 'MRPV');
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM members");
    $count = $stmt->fetch()['cnt'] + 1;
    return $prefix . '-' . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function generatePaymentNumber() {
    global $pdo;
    $prefix = getSetting('payment_prefix', 'PMT');
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM payments");
    $count = $stmt->fetch()['cnt'] + 1;
    return $prefix . '-' . $year . str_pad($count, 6, '0', STR_PAD_LEFT);
}

function generateReceiptNumber() {
    global $pdo;
    $prefix = getSetting('invoice_prefix', 'RCP');
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM receipts");
    $count = $stmt->fetch()['cnt'] + 1;
    return $prefix . '-' . $year . str_pad($count, 6, '0', STR_PAD_LEFT);
}

function calculateDueAmount($memberId, $projectId = null) {
    global $pdo;
    $sql = "SELECT SUM(i.total_investment_amount) - COALESCE(SUM(p.amount), 0) as due 
            FROM investment_plans i 
            LEFT JOIN payments p ON p.investment_plan_id = i.id AND p.approval_status = 'approved'
            WHERE i.member_id = ?";
    $params = [$memberId];
    if ($projectId) {
        $sql .= " AND i.project_id = ?";
        $params[] = $projectId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result['due'] ?? 0;
}

function getMemberTotalPaid($memberId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE member_id = ? AND approval_status = 'approved'");
    $stmt->execute([$memberId]);
    return $stmt->fetch()['total'] ?? 0;
}

function getMemberTotalInvestment($memberId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_investment_amount), 0) as total FROM investment_plans WHERE member_id = ? AND status != 'cancelled'");
    $stmt->execute([$memberId]);
    return $stmt->fetch()['total'] ?? 0;
}

function getProjectTotalCollection($projectId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE project_id = ? AND approval_status = 'approved'");
    $stmt->execute([$projectId]);
    return $stmt->fetch()['total'] ?? 0;
}

function getProjectTotalExpenses($projectId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE project_id = ? AND status = 'paid'");
    $stmt->execute([$projectId]);
    return $stmt->fetch()['total'] ?? 0;
}

function getTotalCollection() {
    global $pdo;
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE approval_status = 'approved'");
    return $stmt->fetch()['total'] ?? 0;
}

function getTotalExpenses() {
    global $pdo;
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE status = 'paid'");
    return $stmt->fetch()['total'] ?? 0;
}

function getTotalMembers($status = null) {
    global $pdo;
    $sql = "SELECT COUNT(*) as total FROM members WHERE 1=1";
    if ($status) {
        $sql .= " AND member_status = '$status'";
    }
    $stmt = $pdo->query($sql);
    return $stmt->fetch()['total'] ?? 0;
}

function getTotalProjects() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects");
    return $stmt->fetch()['total'] ?? 0;
}

function getAssignableProjects() {
    global $pdo;
    return $pdo->query("SELECT id, project_name, project_code FROM projects ORDER BY project_name")->fetchAll();
}

function normalizeProjectIds($projectIds) {
    if (!is_array($projectIds)) {
        return [];
    }

    $normalized = [];
    foreach ($projectIds as $projectId) {
        $projectId = (int) $projectId;
        if ($projectId > 0) {
            $normalized[] = $projectId;
        }
    }

    return array_values(array_unique($normalized));
}

function validateProjectIds(array $projectIds) {
    global $pdo;
    if (empty($projectIds)) {
        return false;
    }

    $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM projects WHERE id IN ($placeholders)");
    $stmt->execute($projectIds);
    return (int) ($stmt->fetch()['total'] ?? 0) === count($projectIds);
}

function syncMemberProjects($memberId, array $projectIds, $assignedBy = null) {
    global $pdo;

    $deleteStmt = $pdo->prepare("DELETE FROM member_projects WHERE member_id = ?");
    $deleteStmt->execute([$memberId]);

    if (empty($projectIds)) {
        return;
    }

    $insertStmt = $pdo->prepare("INSERT INTO member_projects (member_id, project_id, assigned_by) VALUES (?, ?, ?)");
    foreach ($projectIds as $projectId) {
        $insertStmt->execute([$memberId, $projectId, $assignedBy]);
    }
}

function getMemberAssignedProjects($memberId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.id, p.project_name, p.project_code
        FROM member_projects mp
        INNER JOIN projects p ON p.id = mp.project_id
        WHERE mp.member_id = ?
        ORDER BY p.project_name");
    $stmt->execute([$memberId]);
    return $stmt->fetchAll();
}

function getMemberAssignedProjectIds($memberId) {
    return array_map('intval', array_column(getMemberAssignedProjects($memberId), 'id'));
}

function formatCurrency($amount) {
    $symbol = getSetting('currency_symbol', 'Tk');
    return $symbol . ' ' . number_format($amount, 2);
}

function formatDate($date, $format = null) {
    if (!$date) return '';
    if (!$format) $format = getSetting('date_format', 'Y-m-d');
    return date($format, strtotime($date));
}

function logAudit($action, $tableName, $recordId, $oldValues = null, $newValues = null) {
    global $pdo;
    $userId = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $tableName, $recordId, json_encode($oldValues), json_encode($newValues), $ip, $agent]);
}

function sendNotification($userId, $memberId, $type, $title, $message, $link = '') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, member_id, notification_type, title, message, link) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $memberId, $type, $title, $message, $link]);
}
