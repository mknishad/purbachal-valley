<?php
require_once 'config.php';

$memberId = intval($_GET['id'] ?? 0);

if (!$memberId) {
    die('Invalid member');
}

$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$memberId]);
$member = $stmt->fetch();

if (!$member) {
    die('Member not found');
}

$orgName = 'MR PURBACHAL VALLEY';
$orgAddress = 'Dhaka, Bangladesh';

require_once 'functions.php';
$assignedProjects = getMemberAssignedProjects($memberId);
$assignedProjectNames = array_map(function ($project) {
    return $project['project_name'];
}, $assignedProjects);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Details - <?= $member['membership_number'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
        .member-details { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #063f45; padding-bottom: 15px; margin-bottom: 20px; }
        .section-title { background: #063f45; color: white; padding: 8px 15px; margin: 20px 0 10px; }
        .info-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .info-table td:first-child { font-weight: 600; width: 200px; background: #f8f9fa; }
        .photo-section img { max-width: 150px; max-height: 150px; border: 2px solid #063f45; }
        .signature-img { max-width: 200px; height: 80px; border: 1px solid #ccc; }
        .print-btn { position: fixed; top: 20px; right: 20px; }
    </style>
</head>
<body>
    <button class="btn btn-primary no-print print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Print
    </button>
    <button class="btn btn-success no-print print-btn" style="top: 60px;" onclick="window.print(); setTimeout(() => window.close(), 100)">
        <i class="fas fa-download"></i> Save PDF
    </button>
    
    <div class="member-details">
        <div class="header">
            <h3><?php echo $orgName; ?></h3>
            <p><?php echo $orgAddress; ?></p>
            <h4>Member Details</h4>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-3 text-center">
                <?php if ($member['photo'] && file_exists($member['photo'])): ?>
                    <img src="<?php echo $member['photo']; ?>" class="photo-section mb-3">
                <?php else: ?>
                    <div class="bg-light d-inline-block p-5 mb-3">No Photo</div>
                <?php endif; ?>
            </div>
            <div class="col-md-9">
                <h4><?php echo $member['first_name'] . ' ' . $member['last_name']; ?></h4>
                <p><strong>Membership:</strong> <?php echo $member['membership_number']; ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($member['member_status']); ?></p>
            </div>
        </div>
        
        <div class="section-title">Personal Information</div>
        <table class="table info-table">
            <tr><td>Father's Name</td><td><?php echo $member['father_name'] ?: 'N/A'; ?></td></tr>
            <tr><td>Mother's Name</td><td><?php echo $member['mother_name'] ?: 'N/A'; ?></td></tr>
            <tr><td>Gender</td><td><?php echo ucfirst($member['gender'] ?: 'N/A'); ?></td></tr>
            <tr><td>Date of Birth</td><td><?php echo $member['date_of_birth'] ? formatDate($member['date_of_birth']) : 'N/A'; ?></td></tr>
            <tr><td>NID Number</td><td><?php echo $member['nid_number'] ?: 'N/A'; ?></td></tr>
            <tr><td>Phone</td><td><?php echo $member['phone']; ?></td></tr>
            <tr><td>Email</td><td><?php echo $member['email'] ?: 'N/A'; ?></td></tr>
            <tr><td>Present Address</td><td><?php echo $member['present_address'] ?: 'N/A'; ?></td></tr>
            <tr><td>Permanent Address</td><td><?php echo $member['permanent_address'] ?: 'N/A'; ?></td></tr>
            <tr><td>Occupation</td><td><?php echo $member['occupation'] ?: 'N/A'; ?></td></tr>
            <tr><td>Investment Type</td><td><?php echo ucfirst($member['investment_type'] ?: 'Individual'); ?></td></tr>
            <tr><td>Project</td><td><?php echo $assignedProjectNames ? implode(', ', array_map('sanitize', $assignedProjectNames)) : 'N/A'; ?></td></tr>
        </table>
        
        <div class="section-title">Nominee Information</div>
        <table class="table info-table">
            <tr><td>Nominee Name</td><td><?php echo $member['nominee_name'] ?: 'N/A'; ?></td></tr>
            <tr><td>Relation</td><td><?php echo $member['nominee_relation'] ?: 'N/A'; ?></td></tr>
            <tr><td>Nominee NID</td><td><?php echo $member['nominee_nid'] ?: 'N/A'; ?></td></tr>
            <tr><td>Nominee Phone</td><td><?php echo $member['nominee_phone'] ?: 'N/A'; ?></td></tr>
        </table>
        
        <div class="section-title">Documents</div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <strong>NID Front</strong><br>
                <?php if ($member['nid_image_front'] && file_exists($member['nid_image_front'])): ?>
                    <img src="<?php echo $member['nid_image_front']; ?>" style="max-width: 100%; max-height: 120px;">
                <?php else: ?>
                    <span class="text-muted">Not uploaded</span>
                <?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <strong>NID Back</strong><br>
                <?php if ($member['nid_image_back'] && file_exists($member['nid_image_back'])): ?>
                    <img src="<?php echo $member['nid_image_back']; ?>" style="max-width: 100%; max-height: 120px;">
                <?php else: ?>
                    <span class="text-muted">Not uploaded</span>
                <?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <strong>Nominee NID</strong><br>
                <?php if ($member['nominee_nid_image'] && file_exists($member['nominee_nid_image'])): ?>
                    <img src="<?php echo $member['nominee_nid_image']; ?>" style="max-width: 100%; max-height: 120px;">
                <?php else: ?>
                    <span class="text-muted">Not uploaded</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="section-title">Digital Signature</div>
        <div class="row">
            <div class="col-md-6">
                <strong>Member Signature</strong><br>
                <?php if ($member['signature'] && file_exists($member['signature'])): ?>
                    <img src="<?php echo $member['signature']; ?>" class="signature-img">
                <?php else: ?>
                    <span class="text-muted">Not uploaded</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-5 pt-4" style="border-top: 1px solid #ccc;">
            <div class="row">
                <div class="col-md-6">
                    <p>Date: <?php echo date('d-m-Y'); ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Authorized Signature</p>
                    <div style="height: 50px;"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
