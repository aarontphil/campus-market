<?php
/**
 * My Listings — Campus Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db = getDB();
$filter = $_GET['status'] ?? 'all';

$sql = 'SELECT * FROM products WHERE seller_id = ?';
$params = [currentUserId()];

if ($filter !== 'all' && in_array($filter, validStatuses(), true)) {
    $sql .= ' AND status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();

$pageTitle = 'My Listings';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="section-title"><i class="fas fa-list text-accent me-2"></i>My Listings</h1>
            <p class="section-subtitle mb-0"><?= count($listings) ?> listing<?= count($listings) !== 1 ? 's' : '' ?> found</p>
        </div>
        <a href="/campus-marketplace/create-listing.php" class="btn btn-accent"><i class="fas fa-plus me-1"></i>New Listing</a>
    </div>

    <!-- Status filter tabs -->
    <ul class="nav nav-pills mb-4 gap-2 flex-wrap">
        <?php
        $tabs = ['all' => 'All', 'Available' => 'Available', 'Reserved' => 'Reserved', 'Sold' => 'Sold', 'Archived' => 'Archived'];
        foreach ($tabs as $val => $label): ?>
        <li class="nav-item">
            <a class="nav-link <?= $filter === $val ? 'active' : '' ?>"
               href="?status=<?= $val ?>"
               style="<?= $filter === $val ? 'background:var(--accent);color:var(--navy);' : 'background:var(--navy-mid);color:var(--text-muted);border:1px solid var(--card-border);' ?>"
            ><?= $label ?></a>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php if (empty($listings)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-box-open"></i></div>
            <h4>No Listings Found</h4>
            <p>You haven't created any listings<?= $filter !== 'all' ? ' with status "' . e($filter) . '"' : '' ?> yet.</p>
            <a href="/campus-marketplace/create-listing.php" class="btn btn-accent mt-3"><i class="fas fa-plus me-1"></i>Create Your First Listing</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($listings as $item):
                $pData = json_decode($item['product_data'], true) ?: [];
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="product-card">
                    <?php $imgUrl = getProductImageUrl($item['image_path'] ?? null, $item['title'], $pData['category'] ?? null); ?>
                    <div class="card-img-wrap">
                        <img src="<?= e($imgUrl) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= e($item['title']) ?></h5>
                        <div class="card-price"><?= formatPrice($item['price']) ?></div>
                        <div class="card-meta">
                            <span class="badge-category"><?= e($pData['category'] ?? 'Other') ?></span>
                            <span class="badge-condition"><?= e($pData['condition'] ?? 'N/A') ?></span>
                            <span class="badge-status <?= strtolower($item['status']) ?>"><?= e($item['status']) ?></span>
                        </div>
                        <div class="card-footer-custom">
                            <span class="seller-info"><i class="fas fa-clock"></i> <?= timeAgo($item['created_at']) ?></span>
                            <div class="d-flex gap-1">
                                <a href="/campus-marketplace/product-details.php?id=<?= $item['id'] ?>" class="btn-view"><i class="fas fa-eye me-1"></i>View</a>
                                <a href="/campus-marketplace/edit-listing.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-light" style="border-radius:60px;font-size:.78rem;"><i class="fas fa-edit"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
