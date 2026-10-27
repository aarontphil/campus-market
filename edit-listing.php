<?php
/**
 * Edit Listing — Campus Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db = getDB();
$productId = (int) ($_GET['id'] ?? 0);

// Fetch product & verify ownership
$stmt = $db->prepare('SELECT * FROM products WHERE id = ? AND seller_id = ?');
$stmt->execute([$productId, currentUserId()]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Listing not found or you do not have permission to edit it.');
    header('Location: /campus-marketplace/my-listings.php');
    exit;
}

$productData = json_decode($product['product_data'], true) ?: [];
$specs = $productData['specifications'] ?? [];
$specKeys = array_keys($specs);
$specVals = array_values($specs);

$errors = [];
$old = [
    'title'       => $product['title'],
    'description' => $product['description'],
    'price'       => $product['price'],
    'category'    => $productData['category'] ?? '',
    'condition'   => $productData['condition'] ?? '',
    'status'      => $product['status'],
    'spec_key_1'  => $specKeys[0] ?? '',
    'spec_val_1'  => $specVals[0] ?? '',
    'spec_key_2'  => $specKeys[1] ?? '',
    'spec_val_2'  => $specVals[1] ?? '',
    'spec_key_3'  => $specKeys[2] ?? '',
    'spec_val_3'  => $specVals[2] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price'] ?? '';
    $category    = $_POST['category'] ?? '';
    $condition   = $_POST['condition'] ?? '';
    $status      = $_POST['status'] ?? $product['status'];

    $newSpecs = [];
    for ($i = 1; $i <= 3; $i++) {
        $k = trim($_POST["spec_key_$i"] ?? '');
        $v = trim($_POST["spec_val_$i"] ?? '');
        if ($k !== '' && $v !== '') $newSpecs[$k] = $v;
    }

    $old = array_merge($old, compact('title', 'description', 'price', 'category', 'condition', 'status'));
    for ($i = 1; $i <= 3; $i++) {
        $old["spec_key_$i"] = trim($_POST["spec_key_$i"] ?? '');
        $old["spec_val_$i"] = trim($_POST["spec_val_$i"] ?? '');
    }

    // Validate
    if ($title === '')          $errors[] = 'Product title is required.';
    if (strlen($title) > 200)  $errors[] = 'Title cannot exceed 200 characters.';
    if ($description === '')    $errors[] = 'Description is required.';
    if (!isPositiveDecimal($price)) $errors[] = 'Price must be a positive number.';
    if (!in_array($category, validCategories(), true)) $errors[] = 'Invalid category.';
    if (!in_array($condition, validConditions(), true)) $errors[] = 'Invalid condition.';
    if (!in_array($status, validStatuses(), true))      $errors[] = 'Invalid listing status.';

    $newProductData = buildProductData($category, $condition, $newSpecs);
    if ($newProductData === null) $errors[] = 'Invalid product data.';

    // Handle optional new image
    $imagePath = $product['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploaded = handleImageUpload($_FILES['image'], $errors);
        if ($uploaded) $imagePath = $uploaded;
    }

    // Archive timestamp
    $archivedAt = null;
    if ($status === 'Archived' && $product['status'] !== 'Archived') {
        $archivedAt = date('Y-m-d H:i:s');
    } elseif ($status === 'Archived') {
        $archivedAt = $product['archived_at'];
    }

    if (empty($errors)) {
        $stmt = $db->prepare(
            'UPDATE products SET title=?, description=?, price=?, product_data=?, image_path=?, status=?, archived_at=?, updated_at=CURRENT_TIMESTAMP
             WHERE id=? AND seller_id=?'
        );
        $stmt->execute([
            $title, $description,
            number_format((float) $price, 2, '.', ''),
            $newProductData, $imagePath, $status, $archivedAt,
            $productId, currentUserId()
        ]);

        setFlash('success', 'Listing updated successfully!');
        header('Location: /campus-marketplace/my-listings.php');
        exit;
    }
}

$pageTitle = 'Edit Listing';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <h1 class="section-title"><i class="fas fa-edit text-accent me-2"></i>Edit Listing</h1>
                <p class="section-subtitle">Update the details for &ldquo;<?= e($product['title']) ?>&rdquo;</p>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 small">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="bg-card border-card rounded-3 p-4">
                <?= csrfField() ?>

                <div class="mb-3">
                    <label class="form-label" for="title">Product Title <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" maxlength="200" value="<?= e($old['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Description <span class="text-danger">*</span></label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?= e($old['description']) ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label" for="price">Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="1" value="<?= e($old['price']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach (validCategories() as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= $old['category'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="condition">Condition <span class="text-danger">*</span></label>
                        <select id="condition" name="condition" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach (validConditions() as $cond): ?>
                                <option value="<?= e($cond) ?>" <?= $old['condition'] === $cond ? 'selected' : '' ?>><?= e($cond) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="status">Status</label>
                        <select id="status" name="status" class="form-select">
                            <?php foreach (validStatuses() as $s): ?>
                                <option value="<?= e($s) ?>" <?= $old['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="image">Product Image</label>
                    <?php if ($product['image_path']): ?>
                        <div class="mb-2">
                            <img src="/campus-marketplace/<?= e($product['image_path']) ?>" alt="" class="rounded" style="max-height:120px;">
                            <span class="small text-muted ms-2">Current image</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-text">Upload a new image to replace the current one. Max 5 MB.</div>
                </div>

                <hr class="border-secondary my-4">
                <h6 class="mb-3 text-muted"><i class="fas fa-cogs me-1"></i> Specifications (JSON data)</h6>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="row mb-2">
                    <div class="col-5">
                        <input type="text" name="spec_key_<?= $i ?>" class="form-control form-control-sm" placeholder="Key" value="<?= e($old["spec_key_$i"]) ?>">
                    </div>
                    <div class="col-7">
                        <input type="text" name="spec_val_<?= $i ?>" class="form-control form-control-sm" placeholder="Value" value="<?= e($old["spec_val_$i"]) ?>">
                    </div>
                </div>
                <?php endfor; ?>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary-custom" style="width:auto;padding:.6rem 2rem;">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                    <a href="/campus-marketplace/my-listings.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
