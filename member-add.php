<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();
requireRole(['admin', 'accountant']);

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
    $password = $_POST['password'] ?? '';
    $projectIds = normalizeProjectIds($_POST['project_ids'] ?? []);
    
    $requiredFields = [
        $firstName, $lastName, $fatherName, $motherName, $gender, $dateOfBirth,
        $nidNumber, $email, $phone, $alternativePhone, $presentAddress, $permanentAddress,
        $occupation, $employerName, $investmentType, $nomineeName, $nomineeRelation,
        $nomineeNid, $nomineePhone, $password
    ];

    foreach ($requiredFields as $field) {
        if (trim((string) $field) === '') {
            $_SESSION['error'] = 'Please fill all required fields';
            redirect(BASE_URL . '/member-add.php');
        }
    }

    if (empty($projectIds)) {
        $_SESSION['error'] = 'Please assign at least one project';
        redirect(BASE_URL . '/member-add.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address';
        redirect(BASE_URL . '/member-add.php');
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters';
        redirect(BASE_URL . '/member-add.php');
    }

    $gender = $gender !== '' ? $gender : null;
    $dateOfBirth = $dateOfBirth !== '' ? $dateOfBirth : null;

    if ($dateOfBirth !== null) {
        $date = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
        if (!$date || $date->format('Y-m-d') !== $dateOfBirth) {
            $_SESSION['error'] = 'Date of birth must be a valid date in YYYY-MM-DD format';
            redirect(BASE_URL . '/member-add.php');
        }
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR phone = ? LIMIT 1");
    $stmt->execute([$phone, $email, $phone]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'A user with this email or phone already exists';
        redirect(BASE_URL . '/member-add.php');
    }

    $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ? OR phone = ? LIMIT 1");
    $stmt->execute([$email, $phone]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'A member with this email or phone already exists';
        redirect(BASE_URL . '/member-add.php');
    }

    if (!validateProjectIds($projectIds)) {
        $_SESSION['error'] = 'Please select valid projects';
        redirect(BASE_URL . '/member-add.php');
    }
    
    $membershipNumber = generateMembershipNumber();
    $fullName = trim($firstName . ' ' . $lastName);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, full_name, phone, status) VALUES (?, ?, ?, 'member', ?, ?, 'active')");
        $stmt->execute([$phone, $email, $passwordHash, $fullName, $phone]);
        $userId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO members (
            membership_number, user_id, first_name, last_name, father_name, mother_name, gender, date_of_birth,
            nid_number, email, phone, alternative_phone, present_address, permanent_address,
            occupation, employer_name, investment_type, nominee_name, nominee_relation, nominee_nid, nominee_phone,
            member_status, registration_date, kyc_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', CURDATE(), 'pending')");

        $stmt->execute([
            $membershipNumber, $userId, $firstName, $lastName, $fatherName, $motherName, $gender, $dateOfBirth,
            $nidNumber, $email, $phone, $alternativePhone, $presentAddress, $permanentAddress,
            $occupation, $employerName, $investmentType, $nomineeName, $nomineeRelation, $nomineeNid, $nomineePhone
        ]);

        $memberId = $pdo->lastInsertId();
        syncMemberProjects($memberId, $projectIds, getCurrentUserId());
        logAudit('CREATE', 'members', $memberId);
        logAudit('CREATE', 'users', $userId);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Failed to add member. Please check the email and phone are unique.';
        redirect(BASE_URL . '/member-add.php');
    }
    
    $_SESSION['success'] = 'Member added successfully! Membership: ' . $membershipNumber;
    redirect(BASE_URL . '/members.php');
}

$pageTitle = 'Add New Member';
$projects = getAssignableProjects();
require_once 'layout.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add New Member</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Father's Name *</label>
                        <input type="text" name="father_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mother's Name *</label>
                        <input type="text" name="mother_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth *</label>
                        <input type="text" name="date_of_birth" class="form-control datepicker" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NID Number *</label>
                        <input type="text" name="nid_number" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone *</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password *</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alternative Phone *</label>
                        <input type="tel" name="alternative_phone" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Occupation *</label>
                        <input type="text" name="occupation" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employer Name *</label>
                        <input type="text" name="employer_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Investment Type *</label>
                        <select name="investment_type" class="form-select" required>
                            <option value="individual">Individual</option>
                            <option value="group">Group</option>
                            <option value="organization">Organization</option>
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
                                    <input type="checkbox" name="project_ids[]" class="form-check-input" value="<?php echo $project['id']; ?>">
                                    <span class="form-check-label">
                                        <?php echo sanitize($project['project_name'] . ($project['project_code'] ? ' (' . $project['project_code'] . ')' : '')); ?>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="invalid-feedback">Please select at least one project.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Present Address *</label>
                        <textarea name="present_address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Permanent Address *</label>
                        <textarea name="permanent_address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-12"><h6 class="mt-3 mb-2 border-bottom pb-2">Nominee Information</h6></div>
                    <div class="col-md-3">
                        <label class="form-label">Nominee Name *</label>
                        <input type="text" name="nominee_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Relation *</label>
                        <input type="text" name="nominee_relation" class="form-control" placeholder="e.g., Father, Wife" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nominee NID *</label>
                        <input type="text" name="nominee_nid" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nominee Phone *</label>
                        <input type="tel" name="nominee_phone" class="form-control" required>
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Member</button>
                        <a href="members.php" class="btn btn-outline-primary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>
