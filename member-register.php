<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Registration - MR PURBACHAL VALLEY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a472a 0%, #2d5a3f 100%); min-height: 100vh; padding: 30px 0; }
        .reg-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-width: 900px; margin: 0 auto; }
        .reg-header { background: linear-gradient(135deg, #1a472a 0%, #2d5a3f 100%); color: white; padding: 25px; border-radius: 15px 15px 0 0; text-align: center; }
        .reg-body { padding: 30px; }
        .form-control:focus { border-color: #1a472a; box-shadow: 0 0 0 0.2rem rgba(26,71,42,0.25); }
        .btn-primary { background: #1a472a; border-color: #1a472a; }
        .btn-primary:hover { background: #2d5a3f; border-color: #2d5a3f; }
        .password-wrapper { position: relative; }
        .password-wrapper .form-control { padding-right: 45px; }
        .toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="reg-card">
            <div class="reg-header">
                <h4><i class="fas fa-building"></i> MR PURBACHAL VALLEY</h4>
                <p class="mb-0">Member Registration</p>
            </div>
            <div class="reg-body">
                <?php
                require_once 'config.php';
                
                $error = '';
                $success = '';
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $firstName = sanitize($_POST['first_name']);
                    $lastName = sanitize($_POST['last_name'] ?? '');
                    $email = sanitize($_POST['email']);
                    $phone = sanitize($_POST['phone']);
                    $password = $_POST['password'];
                    $confirmPassword = $_POST['confirm_password'];
                    $fatherName = sanitize($_POST['father_name'] ?? '');
                    
                    if (empty($firstName) || empty($email) || empty($phone) || empty($password)) {
                        $error = 'Please fill all required fields';
                    } elseif ($password !== $confirmPassword) {
                        $error = 'Passwords do not match';
                    } elseif (strlen($password) < 6) {
                        $error = 'Password must be at least 6 characters';
                    } else {
                        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ? OR phone = ?");
                        $stmt->execute([$email, $phone]);
                        if ($stmt->fetch()) {
                            $error = 'Email or phone already registered';
                        } else {
                            $token = bin2hex(random_bytes(32));
                            $hash = password_hash($password, PASSWORD_DEFAULT);
                            $membershipNumber = generateMembershipNumber();
                            
                            $pdo->beginTransaction();
                            try {
                                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, full_name, status) VALUES (?, ?, ?, 'member', ?, 'active')");
                                $stmt->execute([$email, $email, $hash, $firstName . ' ' . $lastName]);
                                $userId = $pdo->lastInsertId();
                                
                                $stmt = $pdo->prepare("INSERT INTO members (membership_number, user_id, first_name, last_name, father_name, email, phone, email_verified, verification_token, member_status, registration_date) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, 'no', ?, 'active', CURDATE())");
                                $stmt->execute([$membershipNumber, $userId, $firstName, $lastName, $fatherName, $email, $phone, $token]);
                                
                                $pdo->commit();
                                
                                $verifyLink = BASE_URL . '/verify-email.php?token=' . $token . '&email=' . $email;
                                
                                $success = 'Registration successful! Please check your email to verify account.';
                                
                                $success .= '<br><strong>Verification Link:</strong> <a href="' . $verifyLink . '">Click here to verify</a>';
                                
                                mail($email, 'Verify your MR PURBACHAL VALLEY account', 
                                    "Hello $firstName,\n\nClick the link below to verify your email:\n$verifyLink\n\nRegards,\nMR PURBACHAL VALLEY");
                                
                            } catch (Exception $e) {
                                $pdo->rollBack();
                                $error = 'Registration failed. Please try again.';
                            }
                        }
                    }
                }
                ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
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
                            <label class="form-label">Father's Name</label>
                            <input type="text" name="father_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone *</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="regPassword" class="form-control" required minlength="6">
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('regPassword', this)"></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password *</label>
                            <div class="password-wrapper">
                                <input type="password" name="confirm_password" id="regConfirm" class="form-control" required>
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('regConfirm', this)"></i>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">I agree to the terms and conditions</label>
                            </div>
                        </div>
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Register
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr(".datepicker", { dateFormat: "Y-m-d", allowInput: true });
        
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>