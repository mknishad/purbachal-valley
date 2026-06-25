<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();

$currentUserId = getCurrentUserId();
$currentUserRole = getCurrentUserRole();
$userFullName = $_SESSION['full_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - MR PURBACHAL VALLEY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-green: #1a472a;
            --secondary-green: #2d5a3f;
        }
        .sidebar { min-height: 100vh; background: var(--primary-green); }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background: var(--secondary-green); border-left-color: #4caf50; }
        .sidebar .nav-header { padding: 20px; color: #a8d5a8; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .topbar { background: white; border-bottom: 1px solid #e0e0e0; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .stat-card { border-left: 4px solid; }
        .stat-card.blue { border-left-color: var(--primary-green); }
        .stat-card.green { border-left-color: #4caf50; }
        .stat-card.orange { border-left-color: #f39c12; }
        .stat-card.red { border-left-color: #e74c3c; }
        
        .sidebar-brand { 
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
        }
        
        .btn-primary {
            background: var(--primary-green);
            border-color: var(--primary-green);
        }
        .btn-primary:hover {
            background: var(--secondary-green);
            border-color: var(--secondary-green);
        }
        
        .password-wrapper { position: relative; }
        .password-wrapper .form-control { padding-right: 45px; }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
        .toggle-password:hover { color: var(--primary-green); }
        
        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(26, 71, 42, 0.25);
        }
        
        .flatpickr-input {
            background: white !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="text-center py-3 border-bottom border-secondary sidebar-brand">
                    <h6 class="text-white mb-0" style="color: white !important; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                        <i class="fas fa-building"></i> MR PURBACHAL
                    </h6>
                    <small style="color: white !important; opacity: 0.9;">VALLEY</small>
                </div>
                <ul class="nav flex-column py-2">
                    <li class="nav-header">Main Menu</li>
                    <li><a href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a></li>
                    <?php if (in_array($currentUserRole, ['admin', 'accountant'])): ?>
                    <li class="nav-header">Members</li>
                    <li><a href="members.php"><i class="fas fa-users me-2"></i> All Members</a></li>
                    <li><a href="member-add.php"><i class="fas fa-user-plus me-2"></i> Add Member</a></li>
                    <li><a href="member-documents.php"><i class="fas fa-file-alt me-2"></i> Documents</a></li>
                    <?php endif; ?>
                    <li class="nav-header">Finance</li>
                    <?php if (in_array($currentUserRole, ['admin', 'accountant'])): ?>
                    <li><a href="payments.php"><i class="fas fa-money-bill-wave me-2"></i> Payments</a></li>
                    <li><a href="payment-add.php"><i class="fas fa-plus-circle me-2"></i> Add Payment</a></li>
                    <li><a href="expenses.php"><i class="fas fa-file-invoice-dollar me-2"></i> Expenses</a></li>
                    <?php endif; ?>
                    <li><a href="my-payments.php"><i class="fas fa-history me-2"></i> My Payments</a></li>
                    <?php if (in_array($currentUserRole, ['admin'])): ?>
                    <li class="nav-header">Projects</li>
                    <li><a href="projects.php"><i class="fas fa-building me-2"></i> Projects</a></li>
                    <li><a href="project-add.php"><i class="fas fa-plus me-2"></i> Add Project</a></li>
                    <li><a href="plots.php"><i class="fas fa-th-large me-2"></i> Plots/Units</a></li>
                    <?php endif; ?>
                    <?php if (in_array($currentUserRole, ['admin', 'accountant'])): ?>
                    <li class="nav-header">Reports</li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar me-2"></i> Reports</a></li>
                    <?php endif; ?>
                    <?php if ($currentUserRole === 'admin'): ?>
                    <li class="nav-header">Settings</li>
                    <li><a href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><a href="users.php"><i class="fas fa-user-cog me-2"></i> Users</a></li>
                    <?php endif; ?>
                    <li class="nav-header">Account</li>
                    <li><a href="profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
            <div class="col-md-10 p-0">
                <div class="topbar d-flex justify-content-between align-items-center px-4 py-2">
                    <h5 class="mb-0"><?= $pageTitle ?? 'Dashboard' ?></h5>
                    <div class="d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?= sanitize($userFullName) ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>