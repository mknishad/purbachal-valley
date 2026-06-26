<?php
session_start();
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$assetBase = ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');
$assetVersion = '20260625b';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MR PURBACHAL VALLEY</title>
    <link href="<?= htmlspecialchars($assetBase . '/assets/vendor/bootstrap/css/bootstrap.min.css?v=' . $assetVersion, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase . '/assets/css/style.css?v=' . $assetVersion, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="brand-mark"><i class="fas fa-building"></i></div>
                            <div>
                                <h3>MR PURBACHAL VALLEY</h3>
                                <p>Land Investment & Contribution System</p>
                            </div>
                        </div>
                    </div>
                    <div class="login-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="authenticate.php">
                            <div class="mb-3">
                                <label class="form-label">Username or Email</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="password-wrapper">
                                    <input type="password" name="password" id="loginPassword" class="form-control" required>
                                    <i class="fas fa-eye toggle-password" onclick="togglePassword('loginPassword', this)"></i>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-login w-100">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
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
        
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            allowInput: true,
            placeholder: "Select date"
        });
    </script>
</body>
</html>
