<?php
/**
 * My Offers (Offers Sent) — Campus Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db = getDB();
$uid = currentUserId();

$statusFilter = $_GET['status'] ?? 'all';

$sql = '
    SELECT o.*, p.title AS product_title, p.price AS original_price, p.image_path, p.status AS product_status,
           u.full_name AS seller_name, u.college_email AS seller_email
    FROM offers o
    JOIN products p ON p.id = o.product_id
    JOIN users u ON u.id = o.seller_id
    WHERE o.buyer_id = ?
';
$params = [$uid];

if ($statusFilter !== 'all' && in_array($statusFilter, ['Pending', 'Accepted', 'Rejected', 'Countered', 'Withdrawn'], true)) {
    $sql .= ' AND o.status = ?';
    $params[] = $statusFilter;
}

$sql .= ' ORDER BY o.created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$offers = $stmt->fetchAll();

$pageTitle = 'My Offers Sent';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="section-title"><i class="fas fa-paper-plane text-accent me-2"></i>My Offers (Sent)</h1>
            <p class="section-subtitle mb-0">Track all offers and price proposals you've made to sellers</p>
        </div>
        <a href="/campus-marketplace/browse.php" class="btn btn-outline-accent">
            <i class="fas fa-search me-1"></i>Browse More Items
        </a>
    </div>

    <!-- Status Filter Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 flex-wrap">
        <?php
        $tabs = ['all' => 'All Offers', 'Pending' => 'Pending', 'Accepted' => 'Accepted', 'Rejected' => 'Rejected', 'Countered' => 'Countered'];
        foreach ($tabs as $key => $label): ?>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === $key ? 'active' : '' ?>"
                   href="?status=<?= $key ?>"
                   style="<?= $statusFilter === $key ? 'background:var(--accent);color:var(--navy);font-weight:600;' : 'background:var(--navy-mid);color:var(--text-muted);border:1px solid var(--card-border);' ?>"
                ><?= $label ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (empty($offers)): ?>
        <div class="empty-state text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h4 class="text-light">No offers found</h4>
            <p class="text-muted">You haven't made any offers in this category yet.</p>
            <a href="/campus-marketplace/browse.php" class="btn btn-accent mt-2">
                <i class="fas fa-shopping-bag me-1"></i>Explore Listings
            </a>
        </div>
    <?php else: ?>
        <div class="card bg-navy-mid border-card">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase" style="border-bottom: 2px solid var(--card-border);">
                            <th>Item</th>
                            <th>Seller</th>
                            <th>Listing Price</th>
                            <th>Your Offer</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($offers as $off): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= e(getImageUrl($off['image_path'])) ?>" alt="" class="rounded me-3" style="width: 48px; height: 48px; object-fit: cover;">
                                        <div>
                                            <a href="/campus-marketplace/product-details.php?id=<?= $off['product_id'] ?>" class="text-light fw-semibold text-decoration-none hover-accent">
                                                <?= e($off['product_title']) ?>
                                            </a>
                                            <?php if ($off['message']): ?>
                                                <div class="small text-muted text-truncate" style="max-width: 260px;" title="<?= e($off['message']) ?>">
                                                    <i class="far fa-comment-dots me-1"></i>"<?= e($off['message']) ?>"
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-light small"><?= e($off['seller_name']) ?></span>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= e($off['seller_email']) ?></div>
                                </td>
                                <td>
                                    <span class="text-muted text-decoration-line-through"><?= formatCurrency($off['original_price']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-accent"><?= formatCurrency($off['offered_price']) ?></span>
                                </td>
                                <td>
                                    <?= statusBadge($off['status']) ?>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= timeAgo($off['created_at']) ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="/campus-marketplace/product-details.php?id=<?= $off['product_id'] ?>" class="btn btn-sm btn-outline-light" title="View Product & Negotiation">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($off['status'] === 'Pending'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Withdraw Offer" onclick="confirmAction('Withdraw your offer of <?= formatCurrency($off['offered_price']) ?>?', () => updateOfferStatus(<?= $off['id'] ?>, 'cancel'))">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
