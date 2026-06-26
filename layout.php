<?php
require_once 'auth.php';
require_once 'functions.php';
requireLogin();

$currentUserId = getCurrentUserId();
$currentUserRole = getCurrentUserRole();
$userFullName = $_SESSION['full_name'] ?? 'User';
$currentPage = basename($_SERVER['PHP_SELF']);
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$assetBase = ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');
$assetVersion = '20260626a';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - MR PURBACHAL VALLEY</title>
    <link href="<?= htmlspecialchars($assetBase . '/assets/vendor/bootstrap/css/bootstrap.min.css?v=' . $assetVersion, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase . '/assets/css/style.css?v=' . $assetVersion, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <div class="container-fluid app-shell">
        <div class="sidebar-backdrop" data-sidebar-close></div>
        <div class="row app-layout">
            <aside class="col-md-3 col-xl-2 sidebar p-0" id="appSidebar">
                <div class="sidebar-brand">
                    <div class="brand-mark"><i class="fas fa-building"></i></div>
                    <div>
                        <h6>MR PURBACHAL</h6>
                        <small>VALLEY</small>
                    </div>
                </div>
                <ul class="nav flex-column py-2">
                    <li class="nav-header">Main Menu</li>
                    <li><a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home me-2"></i> Dashboard</a></li>
                    <?php if (in_array($currentUserRole, ['admin', 'accountant'])): ?>
                    <li class="nav-header">Members</li>
                    <li><a href="members.php" class="<?= $currentPage === 'members.php' ? 'active' : '' ?>"><i class="fas fa-users me-2"></i> All Members</a></li>
                    <li><a href="member-add.php" class="<?= $currentPage === 'member-add.php' ? 'active' : '' ?>"><i class="fas fa-user-plus me-2"></i> Add Member</a></li>
                    <li><a href="member-documents.php" class="<?= $currentPage === 'member-documents.php' ? 'active' : '' ?>"><i class="fas fa-file-alt me-2"></i> Documents</a></li>
                    <?php endif; ?>
                    <li class="nav-header">Finance</li>
                    <?php if (in_array($currentUserRole, ['admin', 'accountant'])): ?>
                    <li><a href="payments.php" class="<?= $currentPage === 'payments.php' ? 'active' : '' ?>"><i class="fas fa-money-bill-wave me-2"></i> Payments</a></li>
                    <li><a href="payment-add.php" class="<?= $currentPage === 'payment-add.php' ? 'active' : '' ?>"><i class="fas fa-plus-circle me-2"></i> Add Payment</a></li>
                    <li><a href="expenses.php" class="<?= $currentPage === 'expenses.php' ? 'active' : '' ?>"><i class="fas fa-file-invoice-dollar me-2"></i> Expenses</a></li>
                    <?php endif; ?>
                    <li><a href="my-payments.php" class="<?= $currentPage === 'my-payments.php' ? 'active' : '' ?>"><i class="fas fa-history me-2"></i> My Payments</a></li>
                    <?php if (in_array($currentUserRole, ['admin'])): ?>
                    <li class="nav-header">Projects</li>
                    <li><a href="projects.php" class="<?= $currentPage === 'projects.php' ? 'active' : '' ?>"><i class="fas fa-building me-2"></i> Projects</a></li>
                    <li><a href="project-add.php" class="<?= $currentPage === 'project-add.php' ? 'active' : '' ?>"><i class="fas fa-plus me-2"></i> Add Project</a></li>
                    <li><a href="plots.php" class="<?= $currentPage === 'plots.php' ? 'active' : '' ?>"><i class="fas fa-th-large me-2"></i> Plots/Units</a></li>
                    <?php endif; ?>
                    <?php if (in_array($currentUserRole, ['admin', 'accountant'])): ?>
                    <li class="nav-header">Reports</li>
                    <li><a href="reports.php" class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar me-2"></i> Reports</a></li>
                    <?php endif; ?>
                    <?php if ($currentUserRole === 'admin'): ?>
                    <li class="nav-header">Settings</li>
                    <li><a href="settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><a href="users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>"><i class="fas fa-user-cog me-2"></i> Users</a></li>
                    <?php endif; ?>
                    <li class="nav-header">Account</li>
                    <li><a href="profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </aside>
            <main class="col-md-9 col-xl-10 p-0 app-main">
                <div class="topbar d-flex justify-content-between align-items-center px-4 py-3">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <button type="button" class="btn btn-light mobile-menu-btn" aria-label="Open navigation" aria-controls="appSidebar" aria-expanded="false" data-sidebar-toggle>
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="min-w-0">
                            <span class="page-kicker">Investment Management</span>
                            <h5 class="mb-0 text-truncate"><?= $pageTitle ?? 'Dashboard' ?></h5>
                        </div>
                    </div>
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
                <div class="content-area p-4">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>
