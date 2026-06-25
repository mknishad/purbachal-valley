<?php
require_once 'config.php';

$paymentId = intval($_GET['id'] ?? 0);

if (!$paymentId) {
    die('Invalid request');
}

$stmt = $pdo->prepare("SELECT p.*, m.first_name, m.last_name, m.membership_number, m.phone, m.present_address, pr.project_name 
    FROM payments p 
    LEFT JOIN members m ON p.member_id = m.id 
    LEFT JOIN projects pr ON p.project_id = pr.id 
    WHERE p.id = ?");
$stmt->execute([$paymentId]);
$payment = $stmt->fetch();

if (!$payment) {
    die('Payment not found');
}

$orgName = getSetting('organization_name', 'MR PURBACHAL VALLEY');
$orgAddress = getSetting('organization_address', 'Dhaka, Bangladesh');
$currencySymbol = getSetting('currency_symbol', 'Tk');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - <?= $payment['payment_number'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
        .receipt { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border: 2px solid #333; }
        .receipt-header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .receipt-title { font-size: 24px; font-weight: bold; }
        .receipt-number { background: #f8f9fa; padding: 10px; text-align: center; font-weight: bold; margin: 20px 0; }
        .table-borderless td { padding: 5px 0; }
        .total-amount { font-size: 18px; font-weight: bold; }
        .signature-section { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-line { width: 200px; text-align: center; border-top: 1px solid #333; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <h2><?= $orgName ?></h2>
            <p><?= $orgAddress ?></p>
        </div>
        
        <div class="receipt-number">
            RECEIPT NO: <?= $payment['payment_number'] ?>
        </div>
        
        <table class="table table-borderless">
            <tr>
                <td><strong>Member Name:</strong></td>
                <td><?= $payment['first_name'] . ' ' . $payment['last_name'] ?></td>
            </tr>
            <tr>
                <td><strong>Membership Number:</strong></td>
                <td><?= $payment['membership_number'] ?></td>
            </tr>
            <tr>
                <td><strong>Contact:</strong></td>
                <td><?= $payment['phone'] ?></td>
            </tr>
            <tr>
                <td><strong>Address:</strong></td>
                <td><?= $payment['present_address'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td><strong>Project:</strong></td>
                <td><?= $payment['project_name'] ?? 'General' ?></td>
            </tr>
            <tr>
                <td><strong>Payment Date:</strong></td>
                <td><?= date('d-m-Y', strtotime($payment['payment_date'])) ?></td>
            </tr>
            <tr>
                <td><strong>Payment Method:</strong></td>
                <td><?= ucfirst($payment['payment_method']) ?></td>
            </tr>
            <?php if ($payment['bank_name']): ?>
            <tr>
                <td><strong>Bank:</strong></td>
                <td><?= $payment['bank_name'] ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($payment['transaction_id']): ?>
            <tr>
                <td><strong>Transaction ID:</strong></td>
                <td><?= $payment['transaction_id'] ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($payment['reference_number']): ?>
            <tr>
                <td><strong>Reference:</strong></td>
                <td><?= $payment['reference_number'] ?></td>
            </tr>
            <?php endif; ?>
        </table>
        
        <table class="table table-borderless">
            <tr>
                <td><strong>Amount (In Words):</strong></td>
                <td><?= ucwords(convertNumberToWords($payment['amount'])) ?> Taka Only</td>
            </tr>
        </table>
        
        <div class="text-center my-4">
            <h3 class="total-amount"><?= $currencySymbol ?> <?= number_format($payment['amount'], 2) ?></h3>
        </div>
        
        <?php if ($payment['notes']): ?>
        <p><strong>Note:</strong> <?= $payment['notes'] ?></p>
        <?php endif; ?>
        
        <div class="signature-section">
            <div class="signature-line">Receiver Signature</div>
            <div class="signature-line">Authorized Signature</div>
        </div>
        
        <div class="text-center mt-4">
            <small>This is a computer-generated receipt. Valid without signature.</small>
        </div>
    </div>
    
    <div class="no-print text-center mt-3">
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        <a href="payments.php" class="btn btn-secondary">Back</a>
    </div>
</body>
</html>

<?php 
function convertNumberToWords($number) {
    $words = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen', 'Twenty',
        'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    if ($number < 21) return $words[$number];
    return '';
}
?>