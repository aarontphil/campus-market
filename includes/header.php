<?php
/**
 * Shared HTML header — Campus Marketplace
 *
 * Expected variables (optional):
 *   $pageTitle  — string, appended to the site name
 *   $extraHead  — string, additional <head> markup
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/functions.php';

$pageTitle = isset($pageTitle) ? e($pageTitle) . ' — Campus Marketplace' : 'Campus Second-Hand Marketplace';
$base = '/campus-marketplace/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A campus marketplace for students to buy and sell second-hand textbooks, gadgets, and hostel essentials.">
    <title><?= $pageTitle ?></title>

    <!-- Google Fonts — Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= $base ?>assets/css/style.css" rel="stylesheet">

    <?= $extraHead ?? '' ?>
</head>
<body>

<!-- ============ Toast Container ============ -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999" id="toastContainer"></div>

<!-- ============ Navbar ============ -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center me-3" href="<?= $base ?>">
            <i class="fas fa-store me-2 text-accent"></i>
            <span>Campus<strong>Market</strong></span>
        </a>
        <a href="<?= $base ?>hub.php" class="badge rounded-pill bg-primary bg-opacity-25 text-info border border-info border-opacity-25 text-decoration-none px-2 py-1 me-auto d-none d-sm-inline-flex align-items-center gap-1" title="All-In-One Navigation Hub">
            <i class="fas fa-network-wired"></i> Master Hub
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= $base ?>"><i class="fas fa-home me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $base ?>browse.php"><i class="fas fa-th-large me-1"></i>Browse Items</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-info fw-semibold" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-compass me-1"></i>All Pages
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow">
                        <li><h6 class="dropdown-header text-uppercase small text-muted">Public Catalog</h6></li>
                        <li><a class="dropdown-item" href="<?= $base ?>"><i class="fas fa-home me-2 text-primary"></i>1. Home Page</a></li>
                        <li><a class="dropdown-item" href="<?= $base ?>browse.php"><i class="fas fa-th-large me-2 text-info"></i>2. Browse & Search Items</a></li>
                        <li><a class="dropdown-item" href="<?= $base ?>product-details.php?id=1"><i class="fas fa-tag me-2 text-warning"></i>3. Product Details (#1 DBMS Book)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header text-uppercase small text-muted">Authentication</h6></li>
                        <li><a class="dropdown-item" href="<?= $base ?>login.php"><i class="fas fa-sign-in-alt me-2 text-success"></i>4. Student Login</a></li>
                        <li><a class="dropdown-item" href="<?= $base ?>register.php"><i class="fas fa-user-plus me-2 text-accent"></i>5. Register Account</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header text-uppercase small text-muted">Student Portal (Buyer / Seller)</h6></li>
                        <li><a class="dropdown-item" href="<?= $base ?>dashboard.php"><i class="fas fa-tachometer-alt me-2 text-accent"></i>6. Student Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?= $base ?>create-listing.php"><i class="fas fa-plus-circle me-2 text-success"></i>7. Post New Listing</a></li>
                        <li><a class="dropdown-item" href="<?= $base ?>my-listings.php"><i class="fas fa-boxes me-2 text-warning"></i>8. My Listings</a></li>
                        <li><a class="dropdown-item" href="<?= $base ?>edit-listing.php?id=1"><i class="fas fa-edit me-2 text-info"></i>9. Edit Listing (#1)</a></li>
                        <li><a class="dropdown-item" href="<?= $base ?>my-offers.php"><i class="fas fa-hand-holding-usd me-2 text-primary"></i>10. My Sent Offers</a></li>
                        <li><a class="dropdown-item" href="<?= $base ?>received-offers.php"><i class="fas fa-inbox me-2 text-danger"></i>11. Received Offers & Counter</a></li>
                        <li><a class="dropdown-item" href="<?= $base ?>profile.php"><i class="fas fa-user-gear me-2 text-light"></i>12. Student Profile & Settings</a></li>
                    </ul>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-2">
                <!-- 1-Click Persona Switcher for effortless testing -->
                <li class="nav-item dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1 text-light py-1 px-2" type="button" data-bs-toggle="dropdown" style="font-size:0.8rem; border-color: rgba(255,255,255,0.15);">
                        <i class="fas fa-users text-accent"></i>
                        <span>Switch Persona: <strong><?= e(currentUserName()) ?></strong></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow" style="min-width: 230px;">
                        <li><h6 class="dropdown-header small text-muted">Instant Test Personas</h6></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="<?= $base ?>switch-user.php?user_id=1&redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? '/campus-marketplace/') ?>"><span><strong>Arjun Nair</strong><br><small class="text-muted">Seller (Books, Tech)</small></span><?php if (currentUserId() == 1): ?><i class="fas fa-check text-accent"></i><?php endif; ?></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="<?= $base ?>switch-user.php?user_id=2&redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? '/campus-marketplace/') ?>"><span><strong>Joel Mathew</strong><br><small class="text-muted">Offers & Negotiator</small></span><?php if (currentUserId() == 2): ?><i class="fas fa-check text-accent"></i><?php endif; ?></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="<?= $base ?>switch-user.php?user_id=3&redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? '/campus-marketplace/') ?>"><span><strong>Meera Krishnan</strong><br><small class="text-muted">Active Campus Buyer</small></span><?php if (currentUserId() == 3): ?><i class="fas fa-check text-accent"></i><?php endif; ?></a></li>
                    </ul>
                </li>

                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link py-1" href="<?= $base ?>dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link py-1" href="<?= $base ?>my-listings.php"><i class="fas fa-list me-1"></i>Listings</a></li>
                    <li class="nav-item"><a class="nav-link py-1" href="<?= $base ?>received-offers.php"><i class="fas fa-inbox me-1"></i>Offers</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle py-1" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1 text-accent"></i><?= e(currentUserName()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                            <li><a class="dropdown-item" href="<?= $base ?>profile.php"><i class="fas fa-id-card me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="<?= $base ?>create-listing.php"><i class="fas fa-plus-circle me-2"></i>New Listing</a></li>
                            <li><a class="dropdown-item" href="<?= $base ?>my-offers.php"><i class="fas fa-hand-holding-usd me-2"></i>Sent Offers</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $base ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link py-1" href="<?= $base ?>login.php"><i class="fas fa-sign-in-alt me-1"></i>Login</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-accent btn-sm ms-lg-2 px-3 py-1" href="<?= $base ?>register.php"><i class="fas fa-user-plus me-1"></i>Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- ============ Flash Messages ============ -->
<?php
$flashSuccess = getFlash('success');
$flashError   = getFlash('error');
if ($flashSuccess || $flashError): ?>
<div class="container mt-3">
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= e($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= e($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<main>
