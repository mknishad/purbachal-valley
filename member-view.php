<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin', 'accountant']);

$memberId = intval($_GET['id'] ?? 0);
if ($memberId <= 0) {
    $_SESSION['error'] = 'Invalid member selected';
    redirect(BASE_URL . '/members.php');
}

$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$memberId]);
$member = $stmt->fetch();

if (!$member) {
    $_SESSION['error'] = 'Member not found';
    redirect(BASE_URL . '/members.php');
}

$payments = $pdo->prepare("SELECT p.*, pr.project_name FROM payments p LEFT JOIN projects pr ON p.project_id = pr.id WHERE p.member_id = ? ORDER BY p.payment_date DESC, p.id DESC LIMIT 8");
$payments->execute([$memberId]);
$recentPayments = $payments->fetchAll();

$totalInvestment = getMemberTotalInvestment($memberId);
$totalPaid = getMemberTotalPaid($memberId);
$due = $totalInvestment - $totalPaid;
$assignedProjects = getMemberAssignedProjects($memberId);

$pageTitle = 'Member Profile';
require_once 'layout.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1"><?php echo sanitize($member['first_name'] . ' ' . $member['last_name']); ?></h4>
        <span class="text-muted"><?php echo sanitize($member['membership_number']); ?></span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="member-edit.php?id=<?php echo $memberId; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
        <a href="member-documents.php?member_id=<?php echo $memberId; ?>" class="btn btn-info"><i class="fas fa-file-alt"></i> Documents</a>
        <a href="member-details-pdf.php?id=<?php echo $memberId; ?>" target="_blank" class="btn btn-success"><i class="fas fa-print"></i> Printable</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card blue">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Investment</h6>
                <h4><?php echo formatCurrency($totalInvestment); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card green">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Paid</h6>
                <h4><?php echo formatCurrency($totalPaid); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card red">
            <div class="card-body">
                <h6 class="text-muted mb-1">Due</h6>
                <h4><?php echo formatCurrency($due); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Personal Information</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <tr><th>Father's Name</th><td><?php echo sanitize($member['father_name'] ?: 'N/A'); ?></td></tr>
                        <tr><th>Mother's Name</th><td><?php echo sanitize($member['mother_name'] ?: 'N/A'); ?></td></tr>
                        <tr><th>Gender</th><td><?php echo sanitize(ucfirst($member['gender'] ?: 'N/A')); ?></td></tr>
                        <tr><th>Date of Birth</th><td><?php echo $member['date_of_birth'] ? formatDate($member['date_of_birth']) : 'N/A'; ?></td></tr>
                        <tr><th>NID Number</th><td><?php echo sanitize($member['nid_number'] ?: 'N/A'); ?></td></tr>
                        <tr><th>Phone</th><td><?php echo sanitize($member['phone'] ?: 'N/A'); ?></td></tr>
                        <tr><th>Alternative Phone</th><td><?php echo sanitize($member['alternative_phone'] ?: 'N/A'); ?></td></tr>
                        <tr><th>Email</th><td><?php echo sanitize($member['email'] ?: 'N/A'); ?></td></tr>
                        <tr><th>Occupation</th><td><?php echo sanitize($member['occupation'] ?: 'N/A'); ?></td></tr>
                        <tr><th>Employer</th><td><?php echo sanitize($member['employer_name'] ?: 'N/A'); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Address</h5></div>
            <div class="card-body">
                <h6>Present Address</h6>
                <p class="text-muted"><?php echo nl2br(sanitize($member['present_address'] ?: 'N/A')); ?></p>
                <h6>Permanent Address</h6>
                <p class="text-muted mb-0"><?php echo nl2br(sanitize($member['permanent_address'] ?: 'N/A')); ?></p>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Membership</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <tr><th>Status</th><td><span class="badge bg-success"><?php echo sanitize(ucfirst($member['member_status'])); ?></span></td></tr>
                        <tr><th>KYC Status</th><td><?php echo sanitize(ucfirst($member['kyc_status'] ?: 'Pending')); ?></td></tr>
                        <tr><th>Investment Type</th><td><?php echo sanitize(ucfirst($member['investment_type'] ?: 'Individual')); ?></td></tr>
                        <tr>
                            <th>Project</th>
                            <td>
                                <?php if (empty($assignedProjects)): ?>
                                    N/A
                                <?php else: ?>
                                    <div class="project-badge-list">
                                        <?php foreach ($assignedProjects as $project): ?>
                                            <span class="badge bg-info"><?php echo sanitize($project['project_name']); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr><th>Registration Date</th><td><?php echo $member['registration_date'] ? formatDate($member['registration_date']) : 'N/A'; ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Nominee</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <tr><th>Name</th><td><?php echo sanitize($member['nominee_name'] ?: 'N/A'); ?></td></tr>
                        <tr><th>Relation</th><td><?php echo sanitize($member['nominee_relation'] ?: 'N/A'); ?></td></tr>
                        <tr><th>NID</th><td><?php echo sanitize($member['nominee_nid'] ?: 'N/A'); ?></td></tr>
                        <tr><th>Phone</th><td><?php echo sanitize($member['nominee_phone'] ?: 'N/A'); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent Payments</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentPayments)): ?>
                            <tr><td colspan="3" class="text-muted text-center py-4">No payments found</td></tr>
                        <?php endif; ?>
                        <?php foreach ($recentPayments as $payment): ?>
                        <tr>
                            <td><?php echo formatDate($payment['payment_date']); ?></td>
                            <td><?php echo formatCurrency($payment['amount']); ?></td>
                            <td><?php echo sanitize(ucfirst($payment['approval_status'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>
