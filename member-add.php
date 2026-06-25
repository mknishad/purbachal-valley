<?php
require_once 'auth.php';
requireLogin();
requireRole(['admin', 'accountant']);

$pageTitle = 'Add New Member';
require_once 'layout.php';

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
    
    if (empty($firstName) || empty($phone)) {
        $_SESSION['error'] = 'Name and phone are required';
        redirect(BASE_URL . '/member-add.php');
    }
    
    $membershipNumber = generateMembershipNumber();
    
    $stmt = $pdo->prepare("INSERT INTO members (
        membership_number, first_name, last_name, father_name, mother_name, gender, date_of_birth,
        nid_number, email, phone, alternative_phone, present_address, permanent_address,
        occupation, employer_name, investment_type, nominee_name, nominee_relation, nominee_nid, nominee_phone,
        member_status, registration_date, kyc_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', CURDATE(), 'pending')");
    
    $stmt->execute([
        $membershipNumber, $firstName, $lastName, $fatherName, $motherName, $gender, $dateOfBirth,
        $nidNumber, $email, $phone, $alternativePhone, $presentAddress, $permanentAddress,
        $occupation, $employerName, $investmentType, $nomineeName, $nomineeRelation, $nomineeNid, $nomineePhone
    ]);
    
    $memberId = $pdo->lastInsertId();
    logAudit('CREATE', 'members', $memberId);
    
    $_SESSION['success'] = 'Member added successfully! Membership: ' . $membershipNumber;
    redirect(BASE_URL . '/members.php');
}
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
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Father's Name</label>
                        <input type="text" name="father_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mother's Name</label>
                        <input type="text" name="mother_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="text" name="date_of_birth" class="form-control datepicker">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NID Number</label>
                        <input type="text" name="nid_number" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone *</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alternative Phone</label>
                        <input type="tel" name="alternative_phone" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="occupation" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employer Name</label>
                        <input type="text" name="employer_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Investment Type</label>
                        <select name="investment_type" class="form-select">
                            <option value="individual">Individual</option>
                            <option value="group">Group</option>
                            <option value="organization">Organization</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Present Address</label>
                        <textarea name="present_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Permanent Address</label>
                        <textarea name="permanent_address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-12"><h6 class="mt-3 mb-2 border-bottom pb-2">Nominee Information</h6></div>
                    <div class="col-md-3">
                        <label class="form-label">Nominee Name</label>
                        <input type="text" name="nominee_name" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Relation</label>
                        <input type="text" name="nominee_relation" class="form-control" placeholder="e.g., Father, Wife">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nominee NID</label>
                        <input type="text" name="nominee_nid" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nominee Phone</label>
                        <input type="tel" name="nominee_phone" class="form-control">
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Member</button>
                        <a href="members.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout-end.php'; ?>