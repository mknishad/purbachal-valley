<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();

$pageTitle = 'Member Documents';
require_once 'layout.php';

$memberId = intval($_GET['member_id'] ?? 0);

$members = $pdo->query("SELECT id, membership_number, first_name, last_name FROM members WHERE member_status = 'active' ORDER BY first_name")->fetchAll();

if ($memberId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$memberId]);
    $member = $stmt->fetch();
    
    $documents = $pdo->prepare("SELECT * FROM documents WHERE member_id = ? ORDER BY upload_date DESC");
    $stmt->execute([$memberId]);
    $docs = $documents->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])) {
    $memberId = intval($_POST['member_id']);
    $docType = sanitize($_POST['doc_type']);
    
    $targetDir = "uploads/documents/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $fileName = basename($_FILES["document_file"]["name"]);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = $memberId . '_' . $docType . '_' . time() . '.' . $fileExt;
    $targetPath = $targetDir . $newFileName;
    
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
    
    if (in_array($fileExt, $allowedExt)) {
        if (move_uploaded_file($_FILES["document_file"]["tmp_name"], $targetPath)) {
            $stmt = $pdo->prepare("INSERT INTO documents (document_type, member_id, file_name, file_path, file_size, mime_type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$docType, $memberId, $newFileName, $targetPath, $_FILES["document_file"]["size"], $_FILES["document_file"]["type"], getCurrentUserId()]);
            
            if ($docType === 'nid_front') {
                $pdo->prepare("UPDATE members SET nid_image_front = ? WHERE id = ?")->execute([$targetPath, $memberId]);
            } elseif ($docType === 'nid_back') {
                $pdo->prepare("UPDATE members SET nid_image_back = ? WHERE id = ?")->execute([$targetPath, $memberId]);
            } elseif ($docType === 'photo') {
                $pdo->prepare("UPDATE members SET photo = ? WHERE id = ?")->execute([$targetPath, $memberId]);
            } elseif ($docType === 'signature') {
                $pdo->prepare("UPDATE members SET signature = ? WHERE id = ?")->execute([$targetPath, $memberId]);
            } elseif ($docType === 'nominee_nid') {
                $pdo->prepare("UPDATE members SET nominee_nid_image = ? WHERE id = ?")->execute([$targetPath, $memberId]);
            }
            
            $_SESSION['success'] = 'Document uploaded successfully!';
        } else {
            $_SESSION['error'] = 'Failed to upload file';
        }
    } else {
        $_SESSION['error'] = 'Invalid file type';
    }
    redirect(BASE_URL . '/member-documents.php?member_id=' . $memberId);
}

if (isset($_GET['delete_doc'])) {
    $docId = intval($_GET['delete_doc']);
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([$docId]);
    $doc = $stmt->fetch();
    
    if ($doc && file_exists($doc['file_path'])) {
        unlink($doc['file_path']);
        $pdo->prepare("DELETE FROM documents WHERE id = ?")->execute([$docId]);
        $_SESSION['success'] = 'Document deleted';
    }
    redirect(BASE_URL . '/member-documents.php?member_id=' . $memberId);
}
?>

<div class="row mb-4">
    <div class="col-md-4">
        <label class="form-label">Select Member</label>
        <select id="memberSelect" class="form-select" onchange="if(this.value) window.location.href='?member_id='+this.value">
            <option value="">Select Member</option>
            <?php foreach ($members as $m): ?>
            <option value="<?php echo $m['id']; ?>" <?php echo ($memberId == $m['id'] ? 'selected' : ''); ?>>
                <?php echo $m['first_name'] . ' ' . $m['last_name'] . ' (' . $m['membership_number'] . ')'; ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php if ($memberId > 0 && $member): ?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Upload Documents</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="member_id" value="<?php echo $memberId; ?>">
                    <input type="hidden" name="upload_document" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select name="doc_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="photo">Member Photo</option>
                            <option value="nid_front">NID Front</option>
                            <option value="nid_back">NID Back</option>
                            <option value="signature">Digital Signature</option>
                            <option value="nominee_nid">Nominee NID</option>
                            <option value="other">Other Document</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" name="document_file" class="form-control" accept="image/*,.pdf,.doc,.docx" required>
                        <small class="text-muted">Supported: JPG, PNG, PDF, DOC (Max 5MB)</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Uploaded Documents</h5>
                <a href="member-details-pdf.php?id=<?php echo $memberId; ?>" class="btn btn-success btn-sm" target="_blank">
                    <i class="fas fa-download"></i> Download PDF
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php 
                    $docTypes = [
                        'photo' => 'Member Photo',
                        'nid_front' => 'NID Front',
                        'nid_back' => 'NID Back',
                        'signature' => 'Digital Signature',
                        'nominee_nid' => 'Nominee NID'
                    ];
                    
                    foreach ($docTypes as $type => $label):
                        $stmt = $pdo->prepare("SELECT * FROM documents WHERE member_id = ? AND document_type = ? ORDER BY id DESC LIMIT 1");
                        $stmt->execute([$memberId, $type]);
                        $doc = $stmt->fetch();
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card" style="background: #f8f9fa;">
                            <div class="card-body text-center p-2">
                                <strong><?php echo $label; ?></strong>
                                <?php if ($doc): ?>
                                    <br>
                                    <?php if (in_array($doc['mime_type'], ['image/jpeg', 'image/png', 'image/gif'])): ?>
                                        <img src="<?php echo $doc['file_path']; ?>" class="img-thumbnail mt-2" style="max-height: 100px;">
                                    <?php else: ?>
                                        <i class="fas fa-file-<?php echo ($doc['mime_type'] === 'application/pdf') ? 'pdf' : 'alt'; ?> fa-2x mt-2"></i>
                                    <?php endif; ?>
                                    <br>
                                    <a href="<?php echo $doc['file_path']; ?>" class="btn btn-sm btn-info mt-1" target="_blank">View</a>
                                    <a href="?member_id=<?php echo $memberId; ?>&delete_doc=<?php echo $doc['id']; ?>" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Delete?')">Delete</a>
                                <?php else: ?>
                                    <br><span class="text-muted">Not uploaded</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'layout-end.php'; ?>