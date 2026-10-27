<?php
/**
 * Received Offers (Offers on My Listings) — Campus Marketplace
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
           u.full_name AS buyer_name, u.college_email AS buyer_email
    FROM offers o
    JOIN products p ON p.id = o.product_id
    JOIN users u ON u.id = o.buyer_id
    WHERE o.seller_id = ?
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

$pageTitle = 'Received Offers';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="section-title"><i class="fas fa-inbox text-accent me-2"></i>Received Offers</h1>
            <p class="section-subtitle mb-0">Review, negotiate, accept, or decline offers submitted by campus buyers</p>
        </div>
        <a href="/campus-marketplace/my-listings.php" class="btn btn-outline-accent">
            <i class="fas fa-list me-1"></i>My Listings
        </a>
    </div>

    <!-- Status Filter Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 flex-wrap">
        <?php
        $tabs = ['all' => 'All Received', 'Pending' => 'Pending Action', 'Accepted' => 'Accepted', 'Rejected' => 'Rejected', 'Countered' => 'Countered'];
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
            <i class="fas fa-handshake-slash fa-3x text-muted mb-3"></i>
            <h4 class="text-light">No offers received yet</h4>
            <p class="text-muted">When interested students make an offer on your items, they'll appear here.</p>
            <a href="/campus-marketplace/create-listing.php" class="btn btn-accent mt-2">
                <i class="fas fa-plus me-1"></i>Post a New Listing
            </a>
        </div>
    <?php else: ?>
        <div class="card bg-navy-mid border-card">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase" style="border-bottom: 2px solid var(--card-border);">
                            <th>Item</th>
                            <th>Buyer</th>
                            <th>Listing Price</th>
                            <th>Offered Price</th>
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
                                    <span class="text-light small fw-medium"><?= e($off['buyer_name']) ?></span>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= e($off['buyer_email']) ?></div>
                                </td>
                                <td>
                                    <span class="text-muted"><?= formatCurrency($off['original_price']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-accent"><?= formatCurrency($off['offered_price']) ?></span>
                                    <?php
                                    $diff = (float)$off['offered_price'] - (float)$off['original_price'];
                                    $pct  = round(($diff / max(1, (float)$off['original_price'])) * 100);
                                    ?>
                                    <span class="badge <?= $pct >= 0 ? 'bg-success' : 'bg-warning text-dark' ?> ms-1" style="font-size: 0.7rem;">
                                        <?= $pct >= 0 ? "+{$pct}%" : "{$pct}%" ?>
                                    </span>
                                </td>
                                <td>
                                    <?= statusBadge($off['status']) ?>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= timeAgo($off['created_at']) ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="/campus-marketplace/product-details.php?id=<?= $off['product_id'] ?>" class="btn btn-sm btn-outline-light" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <?php if ($off['status'] === 'Pending'): ?>
                                            <button type="button" class="btn btn-sm btn-success" title="Accept Offer" onclick="confirmAction('Accept offer of <?= formatCurrency($off['offered_price']) ?>? This will mark the listing as Reserved.', () => updateOfferStatus(<?= $off['id'] ?>, 'accept'))">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Reject Offer" onclick="confirmAction('Reject this offer?', () => updateOfferStatus(<?= $off['id'] ?>, 'reject'))">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" title="Counter Offer" data-bs-toggle="modal" data-bs-target="#counterModal<?= $off['id'] ?>">
                                                <i class="fas fa-reply"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Counter Offer Modal -->
                                    <?php if ($off['status'] === 'Pending'): ?>
                                        <div class="modal fade text-start" id="counterModal<?= $off['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content bg-navy-mid border-card">
                                                    <div class="modal-header border-bottom-card">
                                                        <h5 class="modal-title text-light"><i class="fas fa-reply text-info me-2"></i>Counter Offer</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="text-muted small mb-3">
                                                            Original Listing: <strong><?= formatCurrency($off['original_price']) ?></strong> &bull;
                                                            Buyer's Offer: <strong class="text-accent"><?= formatCurrency($off['offered_price']) ?></strong>
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label text-light small">Your Counter Price (₹)</label>
                                                            <input type="number" step="0.01" min="1" id="counterPrice<?= $off['id'] ?>" class="form-control bg-navy border-card text-light" placeholder="e.g. 1200">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label text-light small">Optional Message</label>
                                                            <textarea id="counterMsg<?= $off['id'] ?>" class="form-control bg-navy border-card text-light" rows="2" placeholder="e.g. Can do ₹1,200 if picked up today"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top-card">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="button" class="btn btn-info btn-sm text-dark fw-semibold" onclick="handleCounterSubmit(<?= $off['id'] ?>)">Send Counter Offer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>

<script>
function handleCounterSubmit(offerId) {
    const price = document.getElementById('counterPrice' + offerId).value;
    const msg = document.getElementById('counterMsg' + offerId).value;
    if (!price || parseFloat(price) <= 0) {
        showToast('Please enter a valid counter price.', 'error');
        return;
    }
    submitCounterOffer(offerId, price, msg);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
