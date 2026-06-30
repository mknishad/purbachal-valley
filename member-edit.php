<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin', 'accountant']);

$memberId = intval($_GET['id'] ?? $_POST['id'] ?? 0);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name'] ?? '');
    $fatherName = sanitize($_POST['father_name'] ?? '');
    $motherName = sanitize($_POST['mother_name'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $dateOfBirth = sanitize($_POST['date_of_birth'] ?? '');
    $nidNumber = sanitize($_POST['nid_number'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone']);
    $alternativePhone = sanitize($_POST['alternative_phone'] ?? '');
    $presentAddress = sanitize($_POST['present_address'] ?? '');
    $permanentAddress = sanitize($_POST['permanent_address'] ?? '');
    $occupation = sanitize($_POST['occupation'] ?? '');
    $employerName = sanitize($_POST['employer_name'] ?? '');
    $investmentType = sanitize($_POST['investment_type'] ?? 'individual');
    $nomineeName = sanitize($_POST['nominee_name'] ?? '');
    $nomineeRelation = sanitize($_POST['nominee_relation'] ?? '');
    $nomineeNid = sanitize($_POST['nominee_nid'] ?? '');
    $nomineePhone = sanitize($_POST['nominee_phone'] ?? '');
    $memberStatus = sanitize($_POST['member_status'] ?? 'active');
    $kycStatus = sanitize($_POST['kyc_status'] ?? 'pending');
    $projectIds = normalizeProjectIds($_POST['project_ids'] ?? []);

    $requiredFields = [
        $firstName, $lastName, $fatherName, $motherName, $gender, $dateOfBirth,
        $nidNumber, $email, $phone, $alternativePhone, $presentAddress, $permanentAddress,
        $occupation, $employerName, $investmentType, $nomineeName, $nomineeRelation,
        $nomineeNid, $nomineePhone, $memberStatus, $kycStatus
    ];

    foreach ($requiredFields as $field) {
        if (trim((string) $field) === '') {
            $_SESSION['error'] = 'Please fill all required fields';
            redirect(BASE_URL . '/member-edit.php?id=' . $memberId);
        }
    }

    if (empty($projectIds)) {
        $_SESSION['error'] = 'Please assign at least one project';
        redirect(BASE_URL . '/member-edit.php?id=' . $memberId);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address';
        redirect(BASE_URL . '/member-edit.php?id=' . $memberId);
    }

    $allowedGenders = ['male', 'female', 'other'];
    $allowedInvestmentTypes = ['individual', 'group', 'organization'];
    $allowedMemberStatuses = ['active', 'inactive', 'expelled'];
    $allowedKycStatuses = ['pending', 'verified', 'rejected'];

    $gender = in_array($gender, $allowedGenders, true) ? $gender : null;
    $investmentType = in_array($investmentType, $allowedInvestmentTypes, true) ? $investmentType : 'individual';
    $memberStatus = in_array($memberStatus, $allowedMemberStatuses, true) ? $memberStatus : 'active';
    $kycStatus = in_array($kycStatus, $allowedKycStatuses, true) ? $kycStatus : 'pending';
    $dateOfBirth = $dateOfBirth !== '' ? $dateOfBirth : null;

    if ($dateOfBirth !== null) {
        $date = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
        if (!$date || $date->format('Y-m-d') !== $dateOfBirth) {
            $_SESSION['error'] = 'Date of birth must be a valid date in YYYY-MM-DD format';
            redirect(BASE_URL . '/member-edit.php?id=' . $memberId);
        }
    }

    if (!validateProjectIds($projectIds)) {
        $_SESSION['error'] = 'Please select valid projects';
        redirect(BASE_URL . '/member-edit.php?id=' . $memberId);
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE members SET
            first_name = ?, last_name = ?, father_name = ?, mother_name = ?, gender = ?, date_of_birth = ?,
            nid_number = ?, email = ?, phone = ?, alternative_phone = ?, present_address = ?, permanent_address = ?,
            occupation = ?, employer_name = ?, investment_type = ?, nominee_name = ?, nominee_relation = ?,
            nominee_nid = ?, nominee_phone = ?, member_status = ?, kyc_status = ?
            WHERE id = ?");

        $stmt->execute([
            $firstName, $lastName, $fatherName, $motherName, $gender, $dateOfBirth,
            $nidNumber, $email, $phone, $alternativePhone, $presentAddress, $permanentAddress,
            $occupation, $employerName, $investmentType, $nomineeName, $nomineeRelation,
            $nomineeNid, $nomineePhone, $memberStatus, $kycStatus, $memberId
        ]);

        syncMemberProjects($memberId, $projectIds, getCurrentUserId());
        logAudit('UPDATE', 'members', $memberId, $member);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Failed to update member';
        redirect(BASE_URL . '/member-edit.php?id=' . $memberId);
    }

    $_SESSION['success'] = 'Member updated successfully';
    redirect(BASE_URL . '/member-view.php?id=' . $memberId);
}

$pageTitle = 'Edit Member';
$projects = getAssignableProjects();
$assignedProjectIds = getMemberAssignedProjectIds($memberId);
require_once 'layout.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1">Edit Member</h4>
        <span class="text-muted"><?php echo sanitize($member['membership_number']); ?></span>
    </div>
    <a href="member-view.php?id=<?php echo $memberId; ?>" class="btn btn-light"><i class="fas fa-eye"></i> View Profile</a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Member Information</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <input type="hidden" name="id" value="<?php echo $memberId; ?>">

            <div class="col-md-4">
                <label class="form-label">First Name *</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo sanitize($member['first_name']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Last Name *</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo sanitize($member['last_name']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Gender *</label>
                <select name="gender" class="form-select" required>
                    <option value="">Select</option>
                    <option value="male" <?php echo $member['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo $member['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                    <option value="other" <?php echo $member['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Father's Name *</label>
                <input type="text" name="father_name" class="form-control" value="<?php echo sanitize($member['father_name']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mother's Name *</label>
                <input type="text" name="mother_name" class="form-control" value="<?php echo sanitize($member['mother_name']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date of Birth *</label>
                <input type="text" name="date_of_birth" class="form-control datepicker" value="<?php echo sanitize($member['date_of_birth']); ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">NID Number *</label>
                <input type="text" name="nid_number" class="form-control" value="<?php echo sanitize($member['nid_number']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone *</label>
                <input type="tel" name="phone" class="form-control" value="<?php echo sanitize($member['phone']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="<?php echo sanitize($member['email']); ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Alternative Phone *</label>
                <input type="tel" name="alternative_phone" class="form-control" value="<?php echo sanitize($member['alternative_phone']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Occupation *</label>
                <input type="text" name="occupation" class="form-control" value="<?php echo sanitize($member['occupation']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Employer Name *</label>
                <input type="text" name="employer_name" class="form-control" value="<?php echo sanitize($member['employer_name']); ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Investment Type *</label>
                <select name="investment_type" class="form-select" required>
                    <option value="individual" <?php echo $member['investment_type'] === 'individual' ? 'selected' : ''; ?>>Individual</option>
                    <option value="group" <?php echo $member['investment_type'] === 'group' ? 'selected' : ''; ?>>Group</option>
                    <option value="organization" <?php echo $member['investment_type'] === 'organization' ? 'selected' : ''; ?>>Organization</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Project *</label>
                <div class="dropdown checkbox-picker" data-checkbox-picker data-required="true">
                    <button type="button" class="form-select project-picker-toggle text-start" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <span data-picker-label>Select Project</span>
                    </button>
                    <div class="dropdown-menu project-picker-menu w-100">
                        <?php foreach ($projects as $project): ?>
                        <label class="dropdown-item form-check project-picker-option">
                            <input type="checkbox" name="project_ids[]" class="form-check-input" value="<?php echo $project['id']; ?>" <?php echo in_array((int) $project['id'], $assignedProjectIds, true) ? 'checked' : ''; ?>>
                            <span class="form-check-label">
                                <?php echo sanitize($project['project_name'] . ($project['project_code'] ? ' (' . $project['project_code'] . ')' : '')); ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="invalid-feedback">Please select at least one project.</div>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Member Status *</label>
                <select name="member_status" class="form-select" required>
                    <option value="active" <?php echo $member['member_status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $member['member_status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="expelled" <?php echo $member['member_status'] === 'expelled' ? 'selected' : ''; ?>>Expelled</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">KYC Status *</label>
                <select name="kyc_status" class="form-select" required>
                    <option value="pending" <?php echo $member['kyc_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="verified" <?php echo $member['kyc_status'] === 'verified' ? 'selected' : ''; ?>>Verified</option>
                    <option value="rejected" <?php echo $member['kyc_status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Present Address *</label>
                <textarea name="present_address" class="form-control" rows="2" required><?php echo sanitize($member['present_address']); ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Permanent Address *</label>
                <textarea name="permanent_address" class="form-control" rows="2" required><?php echo sanitize($member['permanent_address']); ?></textarea>
            </div>

            <div class="col-md-12"><h6 class="mt-3 mb-2 border-bottom pb-2">Nominee Information</h6></div>
            <div class="col-md-3">
                <label class="form-label">Nominee Name *</label>
                <input type="text" name="nominee_name" class="form-control" value="<?php echo sanitize($member['nominee_name']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Relation *</label>
                <input type="text" name="nominee_relation" class="form-control" value="<?php echo sanitize($member['nominee_relation']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nominee NID *</label>
                <input type="text" name="nominee_nid" class="form-control" value="<?php echo sanitize($member['nominee_nid']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nominee Phone *</label>
                <input type="tel" name="nominee_phone" class="form-control" value="<?php echo sanitize($member['nominee_phone']); ?>" required>
            </div>

            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <a href="member-view.php?id=<?php echo $memberId; ?>" class="btn btn-outline-primary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>
