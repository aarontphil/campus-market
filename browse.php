<?php
/**
 * Browse Marketplace — Campus Marketplace
 *
 * Full search, filter, sort, and pagination support.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$perPage = 12;

// Gather filter params
$search    = trim($_GET['q'] ?? '');
$category  = $_GET['category'] ?? '';
$condition = $_GET['condition'] ?? '';
$minPrice  = $_GET['min_price'] ?? '';
$maxPrice  = $_GET['max_price'] ?? '';
$sort      = $_GET['sort'] ?? 'newest';
$page      = max(1, (int) ($_GET['page'] ?? 1));

// Build query
$where  = ['p.status = ?'];
$params = ['Available'];

if ($search !== '') {
    $where[]  = '(p.title LIKE ? OR p.description LIKE ? OR u.full_name LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$isSqlite = ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');

if ($category !== '' && in_array($category, validCategories(), true)) {
    if ($isSqlite) {
        $where[]  = 'JSON_EXTRACT(p.product_data, "$.category") = ?';
    } else {
        $where[]  = 'JSON_UNQUOTE(JSON_EXTRACT(p.product_data, "$.category")) = ?';
    }
    $params[] = $category;
}
if ($condition !== '' && in_array($condition, validConditions(), true)) {
    if ($isSqlite) {
        $where[]  = 'JSON_EXTRACT(p.product_data, "$.condition") = ?';
    } else {
        $where[]  = 'JSON_UNQUOTE(JSON_EXTRACT(p.product_data, "$.condition")) = ?';
    }
    $params[] = $condition;
}
if (is_numeric($minPrice) && (float) $minPrice > 0) {
    $where[]  = 'p.price >= ?';
    $params[] = (float) $minPrice;
}
if (is_numeric($maxPrice) && (float) $maxPrice > 0) {
    $where[]  = 'p.price <= ?';
    $params[] = (float) $maxPrice;
}

$whereSQL = implode(' AND ', $where);

$orderMap = [
    'newest'     => 'p.created_at DESC',
    'oldest'     => 'p.created_at ASC',
    'price_low'  => 'p.price ASC',
    'price_high' => 'p.price DESC',
];
$orderSQL = $orderMap[$sort] ?? 'p.created_at DESC';

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) FROM products p JOIN users u ON u.id=p.seller_id WHERE $whereSQL");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$pag = paginationInfo($totalRows, $perPage, $page);

// Fetch page
$dataStmt = $db->prepare(
    "SELECT p.*, u.full_name AS seller_name
     FROM products p
     JOIN users u ON u.id = p.seller_id
     WHERE $whereSQL
     ORDER BY $orderSQL
     LIMIT {$pag['perPage']} OFFSET {$pag['offset']}"
);
$dataStmt->execute($params);
$products = $dataStmt->fetchAll();

// Build query string helper
function filterUrl(array $overrides = []): string {
    global $search, $category, $condition, $minPrice, $maxPrice, $sort;
    $base = [
        'q' => $search, 'category' => $category, 'condition' => $condition,
        'min_price' => $minPrice, 'max_price' => $maxPrice, 'sort' => $sort, 'page' => 1,
    ];
    return '?' . http_build_query(array_merge($base, $overrides));
}

$pageTitle = 'Browse Marketplace';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <form method="GET" id="filterForm" class="filter-section mb-3">
                <h6><i class="fas fa-filter me-1"></i> Filters</h6>

                <div class="mb-3">
                    <label class="form-label small">Search</label>
                    <input type="text" name="q" id="searchInput" class="form-control form-control-sm" placeholder="Search products..." value="<?= e($search) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label small">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach (validCategories() as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Condition</label>
                    <select name="condition" class="form-select form-select-sm">
                        <option value="">All Conditions</option>
                        <?php foreach (validConditions() as $cond): ?>
                            <option value="<?= e($cond) ?>" <?= $condition === $cond ? 'selected' : '' ?>><?= e($cond) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small">Min ₹</label>
                        <input type="number" name="min_price" class="form-control form-control-sm" min="0" step="1" value="<?= e($minPrice) ?>" placeholder="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Max ₹</label>
                        <input type="number" name="max_price" class="form-control form-control-sm" min="0" step="1" value="<?= e($maxPrice) ?>" placeholder="Any">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest"     <?= $sort === 'oldest'     ? 'selected' : '' ?>>Oldest First</option>
                        <option value="price_low"  <?= $sort === 'price_low'  ? 'selected' : '' ?>>Price: Low → High</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High → Low</option>
                    </select>
                </div>

                <input type="hidden" name="page" value="1">
                <button type="submit" class="btn btn-accent btn-sm w-100"><i class="fas fa-search me-1"></i>Apply Filters</button>
                <a href="/campus-marketplace/browse.php" class="btn btn-outline-secondary btn-sm w-100 mt-2">Clear All</a>
            </form>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2 class="section-title fs-5 mb-0">
                    <?php if ($search): ?>
                        Results for &ldquo;<?= e($search) ?>&rdquo;
                    <?php else: ?>
                        Browse Marketplace
                    <?php endif; ?>
                </h2>
                <span class="text-muted small"><?= $totalRows ?> item<?= $totalRows !== 1 ? 's' : '' ?> found</span>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-search"></i></div>
                    <h4>No Products Found</h4>
                    <p>Try adjusting your filters or search terms.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($products as $item):
                        $pData = json_decode($item['product_data'], true) ?: [];
                    ?>
                    <div class="col-md-6 col-xl-4">
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
                                    <span class="seller-info"><i class="fas fa-user"></i> <?= e($item['seller_name']) ?> &middot; <?= timeAgo($item['created_at']) ?></span>
                                    <a href="/campus-marketplace/product-details.php?id=<?= $item['id'] ?>" class="btn-view">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pag['totalPages'] > 1): ?>
                <nav class="mt-4 d-flex justify-content-center">
                    <ul class="pagination mb-0">
                        <li class="page-item <?= $pag['currentPage'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= filterUrl(['page' => $pag['currentPage'] - 1]) ?>">&laquo;</a>
                        </li>
                        <?php for ($i = 1; $i <= $pag['totalPages']; $i++): ?>
                        <li class="page-item <?= $i === $pag['currentPage'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= filterUrl(['page' => $i]) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $pag['currentPage'] >= $pag['totalPages'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= filterUrl(['page' => $pag['currentPage'] + 1]) ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
