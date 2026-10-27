<?php
/**
 * Student Dashboard — Campus Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db  = getDB();
$uid = currentUserId();

// Statistics
$activeListings   = (int) $db->prepare('SELECT COUNT(*) FROM products WHERE seller_id=? AND status="Available"')->execute([$uid]) ? $db->prepare('SELECT COUNT(*) FROM products WHERE seller_id=? AND status="Available"') : 0;
// Re-do properly
$stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE seller_id=? AND status="Available"');
$stmt->execute([$uid]);
$activeListings = (int) $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE seller_id=? AND status="Sold"');
$stmt->execute([$uid]);
$itemsSold = (int) $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM offers WHERE seller_id=?');
$stmt->execute([$uid]);
$offersReceived = (int) $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM offers WHERE seller_id=? AND status="Pending"');
$stmt->execute([$uid]);
$pendingOffers = (int) $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM offers WHERE buyer_id=?');
$stmt->execute([$uid]);
$offersSubmitted = (int) $stmt->fetchColumn();

// Recent listings
$stmt = $db->prepare(
    'SELECT id, title, price, status, product_data, created_at FROM products WHERE seller_id=? ORDER BY created_at DESC LIMIT 5'
);
$stmt->execute([$uid]);
$recentListings = $stmt->fetchAll();

// Recent offers (received)
$stmt = $db->prepare(
    'SELECT o.*, p.title AS product_title, u.full_name AS buyer_name
     FROM offers o
     JOIN products p ON p.id = o.product_id
     JOIN users u ON u.id = o.buyer_id
     WHERE o.seller_id=?
     ORDER BY o.created_at DESC LIMIT 5'
);
$stmt->execute([$uid]);
$recentOffers = $stmt->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
    <div class="mb-4">
        <h1 class="section-title"><i class="fas fa-tachometer-alt text-accent me-2"></i>Dashboard</h1>
        <p class="section-subtitle">Welcome back, <?= e(currentUserName()) ?>!</p>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-5">
        <div class="col-6 col-lg">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--accent-glow);color:var(--accent);"><i class="fas fa-box-open"></i></div>
                <div class="stat-value"><?= $activeListings ?></div>
                <div class="stat-label">Active Listings</div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(244,63,94,.12);color:var(--danger);"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value"><?= $itemsSold ?></div>
                <div class="stat-label">Items Sold</div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--blue-soft);color:var(--blue);"><i class="fas fa-inbox"></i></div>
                <div class="stat-value"><?= $offersReceived ?></div>
                <div class="stat-label">Offers Received</div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(245,158,11,.12);color:var(--warning);"><i class="fas fa-clock"></i></div>
                <div class="stat-value"><?= $pendingOffers ?></div>
                <div class="stat-label">Pending Offers</div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(139,92,246,.12);color:#8b5cf6;"><i class="fas fa-paper-plane"></i></div>
                <div class="stat-value"><?= $offersSubmitted ?></div>
                <div class="stat-label">Offers Submitted</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-5">
        <div class="col-md-6 col-lg-3">
            <a href="/campus-marketplace/create-listing.php" class="quick-action">
                <div class="qa-icon" style="background:var(--accent-glow);color:var(--accent);"><i class="fas fa-plus-circle"></i></div>
                <div>
                    <div class="fw-semibold small">Add New Listing</div>
                    <div class="text-muted" style="font-size:.75rem;">Sell an item</div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="/campus-marketplace/browse.php" class="quick-action">
                <div class="qa-icon" style="background:var(--blue-soft);color:var(--blue);"><i class="fas fa-store"></i></div>
                <div>
                    <div class="fw-semibold small">Browse Marketplace</div>
                    <div class="text-muted" style="font-size:.75rem;">Find items</div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="/campus-marketplace/my-offers.php" class="quick-action">
                <div class="qa-icon" style="background:rgba(245,158,11,.12);color:var(--warning);"><i class="fas fa-hand-holding-usd"></i></div>
                <div>
                    <div class="fw-semibold small">View My Offers</div>
                    <div class="text-muted" style="font-size:.75rem;">Track negotiations</div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="/campus-marketplace/my-listings.php" class="quick-action">
                <div class="qa-icon" style="background:rgba(244,63,94,.12);color:var(--danger);"><i class="fas fa-list"></i></div>
                <div>
                    <div class="fw-semibold small">Manage Listings</div>
                    <div class="text-muted" style="font-size:.75rem;">Edit your items</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Listings -->
        <div class="col-lg-6">
            <div class="bg-card border-card rounded-3 p-4">
                <h5 class="fw-semibold mb-3"><i class="fas fa-clock text-accent me-2"></i>Recent Listings</h5>
                <?php if (empty($recentListings)): ?>
                    <p class="text-muted small">No listings yet. <a href="/campus-marketplace/create-listing.php">Create one</a>.</p>
                <?php else: ?>
                    <?php foreach ($recentListings as $item):
                        $pData = json_decode($item['product_data'], true) ?: [];
                    ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
                        <div>
                            <a href="/campus-marketplace/product-details.php?id=<?= $item['id'] ?>" class="text-white small fw-medium text-decoration-none"><?= e($item['title']) ?></a>
                            <div class="text-muted" style="font-size:.72rem;"><?= timeAgo($item['created_at']) ?></div>
                        </div>
                        <div class="text-end">
                            <span class="text-accent fw-bold small"><?= formatPrice($item['price']) ?></span>
                            <div><span class="badge-status <?= strtolower($item['status']) ?>"><?= e($item['status']) ?></span></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Offers Received -->
        <div class="col-lg-6">
            <div class="bg-card border-card rounded-3 p-4">
                <h5 class="fw-semibold mb-3"><i class="fas fa-inbox text-accent me-2"></i>Recent Offers Received</h5>
                <?php if (empty($recentOffers)): ?>
                    <p class="text-muted small">No offers received yet.</p>
                <?php else: ?>
                    <?php foreach ($recentOffers as $offer): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
                        <div>
                            <div class="small fw-medium"><?= e($offer['buyer_name']) ?></div>
                            <div class="text-muted" style="font-size:.72rem;">on <?= e($offer['product_title']) ?> &middot; <?= timeAgo($offer['created_at']) ?></div>
                        </div>
                        <div class="text-end">
                            <span class="text-accent fw-bold small"><?= formatPrice($offer['offered_price']) ?></span>
                            <div><span class="badge-offer <?= strtolower($offer['status']) ?>"><?= e($offer['status']) ?></span></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
