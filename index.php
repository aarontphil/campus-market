<?php
/**
 * Home Page — Campus Second-Hand Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// Stats
$totalProducts = (int) $db->query("SELECT COUNT(*) FROM products WHERE status='Available'")->fetchColumn();
$totalUsers    = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalSold     = (int) $db->query("SELECT COUNT(*) FROM products WHERE status='Sold'")->fetchColumn();

// Recent listings
$recentStmt = $db->query(
    "SELECT p.*, u.full_name AS seller_name
     FROM products p
     JOIN users u ON u.id = p.seller_id
     WHERE p.status = 'Available'
     ORDER BY p.created_at DESC
     LIMIT 8"
);
$recentProducts = $recentStmt->fetchAll();

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1>Buy &amp; Sell<br><span class="highlight">Campus Essentials</span><br>Hassle-Free</h1>
                <p class="lead mt-3 mb-4">A dedicated marketplace for students to trade second-hand textbooks, gadgets, hostel supplies, and more — right on campus.</p>

                <div class="hero-search mb-4">
                    <i class="fas fa-search search-icon"></i>
                    <form action="/campus-marketplace/browse.php" method="GET">
                        <input type="text" name="q" placeholder="Search for textbooks, gadgets, furniture..." autocomplete="off">
                    </form>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    <div class="stat-chip"><i class="fas fa-box"></i> <?= $totalProducts ?> Items Listed</div>
                    <div class="stat-chip"><i class="fas fa-users"></i> <?= $totalUsers ?> Students</div>
                    <div class="stat-chip"><i class="fas fa-handshake"></i> <?= $totalSold ?> Items Sold</div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center" style="position:relative;z-index:1;">
                <div style="font-size:12rem;opacity:.12;color:var(--accent);line-height:1;">
                    <i class="fas fa-store"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ CATEGORIES ============ -->
<section class="container py-5">
    <div class="text-center mb-4">
        <h2 class="section-title">Browse by Category</h2>
        <p class="section-subtitle">Find exactly what you need.</p>
    </div>
    <div class="row g-3 justify-content-center">
        <?php
        $cats = [
            ['Textbooks and Academic Materials', 'fas fa-book', 'Textbooks'],
            ['Electronics and Gadgets',          'fas fa-laptop', 'Electronics'],
            ['Hostel Essentials',                'fas fa-bed',   'Hostel'],
            ['Furniture',                        'fas fa-couch', 'Furniture'],
            ['Clothing and Accessories',         'fas fa-tshirt','Clothing'],
            ['Sports Equipment',                 'fas fa-futbol','Sports'],
            ['Other',                            'fas fa-ellipsis-h','Other'],
        ];
        foreach ($cats as $c): ?>
        <div class="col-6 col-sm-4 col-md-3 col-lg">
            <a href="/campus-marketplace/browse.php?category=<?= urlencode($c[0]) ?>" class="category-pill">
                <div class="cat-icon"><i class="<?= $c[1] ?>"></i></div>
                <span class="cat-label"><?= $c[2] ?></span>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ RECENTLY LISTED ============ -->
<section class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="section-title">Recently Listed</h2>
            <p class="section-subtitle mb-0">Fresh items from fellow students.</p>
        </div>
        <a href="/campus-marketplace/browse.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-right me-1"></i>View All</a>
    </div>

    <?php if (empty($recentProducts)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-box-open"></i></div>
            <h4>No Items Yet</h4>
            <p>Be the first to list an item on the marketplace!</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($recentProducts as $item):
                $pData = json_decode($item['product_data'], true) ?: [];
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
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
                        </div>
                        <div class="card-footer-custom">
                            <span class="seller-info"><i class="fas fa-user"></i> <?= e($item['seller_name']) ?></span>
                            <a href="/campus-marketplace/product-details.php?id=<?= $item['id'] ?>" class="btn-view">View</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ============ CTA ============ -->
<section class="container pb-5">
    <div class="bg-card border-card rounded-3 p-5 text-center" style="background: linear-gradient(135deg, var(--navy-mid), var(--card-bg)) !important;">
        <h3 class="fw-bold mb-2">Ready to Declutter?</h3>
        <p class="text-muted mb-4">Turn your unused items into cash. List them on the marketplace in under a minute.</p>
        <?php if (isLoggedIn()): ?>
            <a href="/campus-marketplace/create-listing.php" class="btn btn-accent px-4 py-2"><i class="fas fa-plus-circle me-2"></i>Create a Listing</a>
        <?php else: ?>
            <a href="/campus-marketplace/register.php" class="btn btn-accent px-4 py-2"><i class="fas fa-user-plus me-2"></i>Get Started — It's Free</a>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
