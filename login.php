<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MR PURBACHAL VALLEY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #1a472a 0%, #2d5a3f 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .login-card { 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.4); 
            overflow: hidden; 
        }
        .login-header { 
            background: linear-gradient(135deg, #1a472a 0%, #2d5a3f 100%); 
            color: white; 
            padding: 30px; 
            text-align: center; 
        }
        .login-header h3 {
            color: white !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .login-header p {
            color: rgba(255,255,255,0.9) !important;
        }
        .login-body { padding: 40px; }
        .form-control { border-radius: 10px; padding: 12px 15px; border: 2px solid #e0e0e0; }
        .form-control:focus { border-color: #1a472a; box-shadow: 0 0 0 3px rgba(26,71,42,0.1); }
        .btn-login { 
            background: linear-gradient(135deg, #1a472a 0%, #2d5a3f 100%); 
            border: none; 
            border-radius: 10px; 
            padding: 12px; 
            font-weight: 600; 
            color: white;
        }
        .btn-login:hover { opacity: 0.9; }
        
        .password-wrapper { position: relative; }
        .password-wrapper .form-control { padding-right: 45px; }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .toggle-password:hover { color: #1a472a; }
        
        .flatpickr-input {
            background: white !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <h3><i class="fas fa-building"></i> MR PURBACHAL VALLEY</h3>
                        <p class="mb-0">Land Investment & Contribution System</p>
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
                        <div class="text-center mt-3">
                            <small class="text-muted">Default: admin / admin123</small>
                        </div>
                        <div class="text-center mt-3">
                            <p>New member? <a href="member-register.php" class="text-success">Register here</a></p>
                        </div>
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