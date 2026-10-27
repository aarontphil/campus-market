<?php
/**
 * Product Details — Campus Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$productId = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare(
    'SELECT p.*, u.full_name AS seller_name, u.college_email AS seller_email
     FROM products p
     JOIN users u ON u.id = p.seller_id
     WHERE p.id = ?'
);
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Product not found.');
    header('Location: /campus-marketplace/browse.php');
    exit;
}

$productData = json_decode($product['product_data'], true) ?: [];
$category    = $productData['category'] ?? 'Other';
$condition   = $productData['condition'] ?? 'N/A';
$specs       = $productData['specifications'] ?? [];

// Fetch offers for this product (visible to buyer/seller)
$offers = [];
if (isLoggedIn()) {
    $uid = currentUserId();
    if ((int)$product['seller_id'] === $uid) {
        // Seller sees all offers
        $ostmt = $db->prepare(
            'SELECT o.*, u.full_name AS buyer_name
             FROM offers o
             JOIN users u ON u.id = o.buyer_id
             WHERE o.product_id = ?
             ORDER BY o.created_at DESC'
        );
        $ostmt->execute([$productId]);
    } else {
        // Buyer sees only their offers
        $ostmt = $db->prepare(
            'SELECT o.*, u.full_name AS buyer_name
             FROM offers o
             JOIN users u ON u.id = o.buyer_id
             WHERE o.product_id = ? AND o.buyer_id = ?
             ORDER BY o.created_at DESC'
        );
        $ostmt->execute([$productId, $uid]);
    }
    $offers = $ostmt->fetchAll();
}

$isSeller = isLoggedIn() && (int) $product['seller_id'] === currentUserId();
$canOffer = isLoggedIn() && !$isSeller && $product['status'] === 'Available';

$pageTitle = $product['title'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
    <div class="row g-4">
        <!-- Image -->
        <div class="col-lg-5">
            <?php $imgUrl = getProductImageUrl($product['image_path'] ?? null, $product['title'], $category); ?>
            <div class="detail-img-wrap rounded-4 overflow-hidden shadow-sm" style="background:#0f172a; max-height:420px;">
                <img src="<?= e($imgUrl) ?>" alt="<?= e($product['title']) ?>" class="w-100 h-100 object-fit-cover" style="min-height:320px;">
            </div>
        </div>

        <!-- Info -->
        <div class="col-lg-7">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge-category"><?= e($category) ?></span>
                <span class="badge-condition"><?= e($condition) ?></span>
                <span class="badge-status <?= strtolower($product['status']) ?>"><?= e($product['status']) ?></span>
            </div>

            <h1 class="fs-3 fw-bold mb-2"><?= e($product['title']) ?></h1>
            <p class="fs-2 fw-bold text-accent mb-3"><?= formatPrice($product['price']) ?></p>

            <table class="detail-meta-table mb-3">
                <tr><th><i class="fas fa-user me-1"></i> Seller</th><td><?= e($product['seller_name']) ?></td></tr>
                <tr><th><i class="fas fa-calendar me-1"></i> Listed</th><td><?= timeAgo($product['created_at']) ?> (<?= date('d M Y', strtotime($product['created_at'])) ?>)</td></tr>
                <?php if ($product['status'] === 'Sold' || $product['status'] === 'Archived'): ?>
                <tr><th><i class="fas fa-archive me-1"></i> Archived</th><td><?= $product['archived_at'] ? date('d M Y', strtotime($product['archived_at'])) : 'N/A' ?></td></tr>
                <?php endif; ?>
            </table>

            <?php if (!empty($specs)): ?>
            <div class="bg-card border-card rounded-3 p-3 mb-3">
                <h6 class="text-muted small mb-2"><i class="fas fa-cogs me-1"></i> Specifications</h6>
                <table class="table table-sm table-borderless mb-0" style="color:var(--text)">
                    <?php foreach ($specs as $key => $val): ?>
                    <tr><td class="text-muted" style="width:40%"><?= e(ucfirst(str_replace('_',' ',$key))) ?></td><td><?= e($val) ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>

            <div class="mb-4">
                <h6 class="text-muted small mb-1">Description</h6>
                <p class="mb-0"><?= nl2br(e($product['description'])) ?></p>
            </div>

            <!-- Actions -->
            <div class="d-flex flex-wrap gap-2">
                <?php if ($isSeller): ?>
                    <a href="/campus-marketplace/edit-listing.php?id=<?= $productId ?>" class="btn btn-outline-light"><i class="fas fa-edit me-1"></i>Edit Listing</a>
                <?php endif; ?>

                <?php if ($canOffer): ?>
                    <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#offerModal">
                        <i class="fas fa-hand-holding-usd me-1"></i>Make an Offer
                    </button>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="/campus-marketplace/login.php" class="btn btn-accent"><i class="fas fa-sign-in-alt me-1"></i>Login to Make Offer</a>
                <?php endif; ?>

                <a href="/campus-marketplace/browse.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Browse</a>
            </div>
        </div>
    </div>

    <!-- Offers Section -->
    <?php if (isLoggedIn() && !empty($offers)): ?>
    <div class="mt-5">
        <h4 class="section-title mb-3">
            <i class="fas fa-comments text-accent me-2"></i>
            <?= $isSeller ? 'Offers Received' : 'Your Offers' ?>
        </h4>
        <?php foreach ($offers as $offer): ?>
        <div class="offer-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <span class="offer-price"><?= formatPrice($offer['offered_price']) ?></span>
                    <span class="badge-offer <?= strtolower($offer['status']) ?> ms-2"><?= e($offer['status']) ?></span>
                    <?php if ($offer['parent_offer_id']): ?>
                        <span class="badge bg-secondary bg-opacity-25 text-light ms-1" style="font-size:.7rem">Counteroffer</span>
                    <?php endif; ?>
                </div>
                <div class="offer-user">
                    <i class="fas fa-user"></i> <?= e($offer['buyer_name']) ?> &middot; <?= timeAgo($offer['created_at']) ?>
                </div>
            </div>
            <?php if ($offer['message']): ?>
                <div class="offer-msg"><?= nl2br(e($offer['message'])) ?></div>
            <?php endif; ?>

            <!-- Offer actions -->
            <?php if ($offer['status'] === 'Pending'): ?>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <?php if ($isSeller): ?>
                    <button class="btn btn-sm btn-success" onclick="updateOfferStatus(<?= $offer['id'] ?>, 'accept')">
                        <i class="fas fa-check me-1"></i>Accept
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="updateOfferStatus(<?= $offer['id'] ?>, 'reject')">
                        <i class="fas fa-times me-1"></i>Reject
                    </button>
                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#counterModal<?= $offer['id'] ?>">
                        <i class="fas fa-exchange-alt me-1"></i>Counter
                    </button>
                <?php elseif ((int)$offer['buyer_id'] === currentUserId()): ?>
                    <button class="btn btn-sm btn-outline-warning" onclick="confirmAction('Withdraw this offer?', () => updateOfferStatus(<?= $offer['id'] ?>, 'withdraw'))">
                        <i class="fas fa-undo me-1"></i>Withdraw
                    </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($isSeller && $offer['status'] === 'Pending'): ?>
        <!-- Counter-offer modal for this offer -->
        <div class="modal fade" id="counterModal<?= $offer['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content" style="background:var(--card-bg);border:1px solid var(--card-border);">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title"><i class="fas fa-exchange-alt text-accent me-2"></i>Counter Offer</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Original offer: <?= formatPrice($offer['offered_price']) ?> by <?= e($offer['buyer_name']) ?></p>
                        <div class="mb-3">
                            <label class="form-label">Your Counter Price (₹)</label>
                            <input type="number" id="counterPrice<?= $offer['id'] ?>" class="form-control" step="0.01" min="1" placeholder="Enter your price">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message (optional)</label>
                            <textarea id="counterMsg<?= $offer['id'] ?>" class="form-control" rows="2" placeholder="Add a message..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-accent" onclick="submitCounterOffer(<?= $offer['id'] ?>, document.getElementById('counterPrice<?= $offer['id'] ?>').value, document.getElementById('counterMsg<?= $offer['id'] ?>').value)">
                            <i class="fas fa-paper-plane me-1"></i>Submit Counter
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- Make Offer Modal -->
<?php if ($canOffer): ?>
<div class="modal fade" id="offerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--card-bg);border:1px solid var(--card-border);">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="fas fa-hand-holding-usd text-accent me-2"></i>Make an Offer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Asking price: <strong class="text-accent"><?= formatPrice($product['price']) ?></strong></p>
                <div class="mb-3">
                    <label class="form-label">Your Offer Price (₹) <span class="text-danger">*</span></label>
                    <input type="number" id="offerPrice" class="form-control" step="0.01" min="1" placeholder="Enter your price" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message (optional)</label>
                    <textarea id="offerMessage" class="form-control" rows="3" placeholder="Introduce yourself or explain your offer..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-accent" onclick="submitOffer(<?= $productId ?>, document.getElementById('offerPrice').value, document.getElementById('offerMessage').value)">
                    <i class="fas fa-paper-plane me-1"></i>Submit Offer
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
