<?php
require_once 'auth.php';
requireLogin();
requireRole(['admin']);

$pageTitle = 'Settings';
require_once 'layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'organization_name' => sanitize($_POST['organization_name']),
        'organization_address' => sanitize($_POST['organization_address']),
        'currency' => sanitize($_POST['currency']),
        'currency_symbol' => sanitize($_POST['currency_symbol']),
        'date_format' => sanitize($_POST['date_format']),
        'membership_prefix' => sanitize($_POST['membership_prefix']),
        'payment_prefix' => sanitize($_POST['payment_prefix']),
        'invoice_prefix' => sanitize($_POST['invoice_prefix']),
        'auto_approval' => sanitize($_POST['auto_approval']),
        'email_notification' => sanitize($_POST['email_notification']),
        'sms_notification' => sanitize($_POST['sms_notification'])
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$key, $value]);
    }
    
    logAudit('UPDATE', 'settings', 0);
    $_SESSION['success'] = 'Settings updated successfully!';
    redirect(BASE_URL . '/settings.php');
}

$currentSettings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while ($row = $stmt->fetch()) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">System Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Organization Name</label>
                        <input type="text" name="organization_name" class="form-control" value="<?= $currentSettings['organization_name'] ?? 'MR PURBACHAL VALLEY' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Organization Address</label>
                        <input type="text" name="organization_address" class="form-control" value="<?= $currentSettings['organization_address'] ?? '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Currency Code</label>
                        <input type="text" name="currency" class="form-control" value="<?= $currentSettings['currency'] ?? 'BDT' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Currency Symbol</label>
                        <input type="text" name="currency_symbol" class="form-control" value="<?= $currentSettings['currency_symbol'] ?? 'Tk' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Format</label>
                        <select name="date_format" class="form-select">
                            <option value="Y-m-d" <?= ($currentSettings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                            <option value="d-m-Y" <?= ($currentSettings['date_format'] ?? '') === 'd-m-Y' ? 'selected' : '' ?>>DD-MM-YYYY</option>
                            <option value="m/d/Y" <?= ($currentSettings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Membership Prefix</label>
                        <input type="text" name="membership_prefix" class="form-control" value="<?= $currentSettings['membership_prefix'] ?? 'MRPV' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Prefix</label>
                        <input type="text" name="payment_prefix" class="form-control" value="<?= $currentSettings['payment_prefix'] ?? 'PMT' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Receipt/Invoice Prefix</label>
                        <input type="text" name="invoice_prefix" class="form-control" value="<?= $currentSettings['invoice_prefix'] ?? 'RCP' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Auto-approve Payments</label>
                        <select name="auto_approval" class="form-select">
                            <option value="yes" <?= ($currentSettings['auto_approval'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes</option>
                            <option value="no" <?= ($currentSettings['auto_approval'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email Notifications</label>
                        <select name="email_notification" class="form-select">
                            <option value="yes" <?= ($currentSettings['email_notification'] ?? '') === 'yes' ? 'selected' : '' ?>>Enable</option>
                            <option value="no" <?= ($currentSettings['email_notification'] ?? '') === 'no' ? 'selected' : '' ?>>Disable</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">SMS Notifications</label>
                        <select name="sms_notification" class="form-select">
                            <option value="yes" <?= ($currentSettings['sms_notification'] ?? '') === 'yes' ? 'selected' : '' ?>>Enable</option>
                            <option value="no" <?= ($currentSettings['sms_notification'] ?? '') === 'no' ? 'selected' : '' ?>>Disable</option>
                        </select>
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">System Info</h6>
            </div>
            <div class="card-body">
                <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                <p><strong>Database:</strong> MySQL</p>
                <p><strong>Server Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>