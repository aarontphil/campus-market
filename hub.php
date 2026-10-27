<?php
/**
 * All-In-One Unified Portal — Campus Marketplace
 * Single link connecting and providing live previews/navigation for every page in the system.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Master Navigation Hub';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$productsCount = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$offersCount   = (int)$db->query("SELECT COUNT(*) FROM offers")->fetchColumn();
$usersCount    = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>

<div class="container py-4">
    <!-- Header banner -->
    <div class="p-4 mb-4 rounded-4" style="background: linear-gradient(135deg, rgba(30,58,138,0.3), rgba(0,214,143,0.15)); border: 1px solid rgba(0,214,143,0.3);">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-success mb-2 px-3 py-2 text-uppercase fw-semibold" style="letter-spacing:1px;">
                    <i class="fas fa-network-wired me-1"></i> Unified Navigation Portal
                </span>
                <h1 class="display-6 fw-bold text-white mb-2">Campus Marketplace Hub</h1>
                <p class="text-muted mb-0">
                    Access all 12 frontend pages, live forms, product detail views, buyer/seller workflows, and switch demo student personas from a single place.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-inline-flex gap-2">
                    <span class="stat-chip"><i class="fas fa-boxes text-accent"></i> <?= $productsCount ?> Products</span>
                    <span class="stat-chip"><i class="fas fa-comments-dollar text-warning"></i> <?= $offersCount ?> Offers</span>
                    <span class="stat-chip"><i class="fas fa-user-graduate text-info"></i> <?= $usersCount ?> Students</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Active Persona Indicator -->
    <div class="card bg-card border-card mb-4 shadow-sm">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <small class="text-muted text-uppercase fw-semibold">Current Active Session:</small>
                <h5 class="mb-0 text-white mt-1">
                    <i class="fas fa-user-circle text-accent me-2"></i><?= e(currentUserName()) ?> 
                    <?php if (isLoggedIn()): ?>
                        <span class="badge bg-primary ms-2"><?= e(currentUserEmail()) ?></span>
                    <?php else: ?>
                        <span class="badge bg-secondary ms-2">Guest / Not Logged In</span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="small text-muted me-1">1-Click Switch:</span>
                <a href="<?= $base ?>switch-user.php?user_id=1&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-sm <?= (currentUserId() == 1) ? 'btn-accent' : 'btn-outline-light' ?>">
                    <i class="fas fa-user me-1"></i> Arjun Nair (Seller)
                </a>
                <a href="<?= $base ?>switch-user.php?user_id=2&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-sm <?= (currentUserId() == 2) ? 'btn-accent' : 'btn-outline-light' ?>">
                    <i class="fas fa-user me-1"></i> Joel Mathew (Negotiator)
                </a>
                <a href="<?= $base ?>switch-user.php?user_id=3&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-sm <?= (currentUserId() == 3) ? 'btn-accent' : 'btn-outline-light' ?>">
                    <i class="fas fa-user me-1"></i> Meera Krishnan (Buyer)
                </a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= $base ?>logout.php" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- All Pages Directory Grid -->
    <h4 class="text-white fw-bold mb-3"><i class="fas fa-th-list me-2 text-accent"></i>All Pages Index</h4>
    
    <div class="row g-3">
        <!-- 1. Home Page -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary fs-4">
                        <i class="fas fa-home"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Home / Landing</h5>
                        <small class="text-muted"><code>/index.php</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Hero section, categories showcase, live search bar, and recently added listings.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>index.php" class="btn btn-sm btn-outline-accent w-100">Open Home Page <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 2. Browse & Search -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info fs-4">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Browse Catalog</h5>
                        <small class="text-muted"><code>/browse.php</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Multi-filter catalog with price ranges, conditions, categories, sorting, and pagination.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>browse.php" class="btn btn-sm btn-outline-accent w-100">Open Browse Catalog <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 3. Product Details -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Product Details</h5>
                        <small class="text-muted"><code>/product-details.php?id=1</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">View specifications, seller details, and make counter or price negotiation offers.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>product-details.php?id=1" class="btn btn-sm btn-outline-accent w-100">View Product #1 <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 4. Login Page -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success fs-4">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Student Login</h5>
                        <small class="text-muted"><code>/login.php</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Login with university email, BCrypt password check, with 1-click test fill buttons.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>login.php" class="btn btn-sm btn-outline-accent w-100">Open Login Page <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 5. Register Page -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-secondary bg-opacity-10 text-light fs-4">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Registration</h5>
                        <small class="text-muted"><code>/register.php</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Register new student accounts with <code>.edu</code> institutional verification rules.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>register.php" class="btn btn-sm btn-outline-accent w-100">Open Register Page <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 6. Student Dashboard -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger fs-4">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Student Dashboard</h5>
                        <small class="text-muted"><code>/dashboard.php</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Personal KPI stats, active listings count, pending offers counter, and quick shortcuts.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>dashboard.php" class="btn btn-sm btn-outline-accent w-100">Open Dashboard <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 7. Create Listing -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success fs-4">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Post New Listing</h5>
                        <small class="text-muted"><code>/create-listing.php</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Add items with dynamic key-value specifications stored in JSON format and image upload.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>create-listing.php" class="btn btn-sm btn-outline-accent w-100">Create Listing <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 8. My Listings -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary fs-4">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">My Listings</h5>
                        <small class="text-muted"><code>/my-listings.php</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Manage seller items with status filters: Available, Reserved, Sold, and Archived.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>my-listings.php" class="btn btn-sm btn-outline-accent w-100">View My Listings <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 9. Edit Listing -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info fs-4">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Edit Listing</h5>
                        <small class="text-muted"><code>/edit-listing.php?id=1</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Edit title, price, specifications, category, or mark status as Reserved / Sold.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>edit-listing.php?id=1" class="btn btn-sm btn-outline-accent w-100">Edit Listing #1 <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 10. Received Offers -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger fs-4">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Received Offers Inbox</h5>
                        <small class="text-muted"><code>/received-offers.php</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Seller proposal inbox with Accept, Reject, or Counter-Offer interactive negotiation modal.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>received-offers.php" class="btn btn-sm btn-outline-accent w-100">Open Received Offers <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 11. My Sent Offers -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">My Sent Offers</h5>
                        <small class="text-muted"><code>/my-offers.php</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Buyer negotiation tracker showing status (Pending, Countered, Accepted) & withdraw button.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>my-offers.php" class="btn btn-sm btn-outline-accent w-100">View Sent Offers <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- 12. Student Profile -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-card border-card h-100 p-3 hover-card">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 p-3 bg-light bg-opacity-10 text-light fs-4">
                        <i class="fas fa-user-gear"></i>
                    </div>
                    <div>
                        <h5 class="text-white mb-0">Profile & Settings</h5>
                        <small class="text-muted"><code>/profile.php</code></small>
                    </div>
                </div>
                <p class="text-muted small mb-3">Update student information, campus verification badge status, and password changes.</p>
                <div class="mt-auto">
                    <a href="<?= $base ?>profile.php" class="btn btn-sm btn-outline-accent w-100">Open Profile <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
    transform: translateY(-4px);
    border-color: var(--accent) !important;
    box-shadow: 0 8px 24px rgba(0, 214, 143, 0.15);
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
