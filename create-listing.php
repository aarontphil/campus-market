<?php
/**
 * Create Listing — Campus Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$errors = [];
$old = [
    'title' => '', 'description' => '', 'price' => '',
    'category' => '', 'condition' => '',
    'spec_key_1' => '', 'spec_val_1' => '',
    'spec_key_2' => '', 'spec_val_2' => '',
    'spec_key_3' => '', 'spec_val_3' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price'] ?? '';
    $category    = $_POST['category'] ?? '';
    $condition   = $_POST['condition'] ?? '';

    // Specification key-value pairs
    $specs = [];
    for ($i = 1; $i <= 3; $i++) {
        $k = trim($_POST["spec_key_$i"] ?? '');
        $v = trim($_POST["spec_val_$i"] ?? '');
        if ($k !== '' && $v !== '') $specs[$k] = $v;
    }

    $old = array_merge($old, compact('title', 'description', 'price', 'category', 'condition'));
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

    // Build JSON product_data
    $productData = buildProductData($category, $condition, $specs);
    if ($productData === null) $errors[] = 'Invalid product data.';

    // Handle image
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imagePath = handleImageUpload($_FILES['image'], $errors);
    }

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO products (seller_id, title, description, price, product_data, image_path, status)
             VALUES (?, ?, ?, ?, ?, ?, "Available")'
        );
        $stmt->execute([
            currentUserId(), $title, $description,
            number_format((float) $price, 2, '.', ''),
            $productData, $imagePath
        ]);

        setFlash('success', 'Listing created successfully!');
        header('Location: /campus-marketplace/my-listings.php');
        exit;
    }
}

$pageTitle = 'Create Listing';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <h1 class="section-title"><i class="fas fa-plus-circle text-accent me-2"></i>Create New Listing</h1>
                <p class="section-subtitle">Fill in the details to list your item on the marketplace.</p>
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
                    <input type="text" id="title" name="title" class="form-control" maxlength="200" value="<?= e($old['title']) ?>" placeholder="e.g. DBMS Textbook — Navathe 4th Ed." required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Description <span class="text-danger">*</span></label>
                    <textarea id="description" name="description" class="form-control" rows="4" placeholder="Describe the item, its condition, and any extras included." required><?= e($old['description']) ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label" for="price">Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="1" value="<?= e($old['price']) ?>" placeholder="450.00" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Select category</option>
                            <?php foreach (validCategories() as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= $old['category'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="condition">Condition <span class="text-danger">*</span></label>
                        <select id="condition" name="condition" class="form-select" required>
                            <option value="">Select condition</option>
                            <?php foreach (validConditions() as $cond): ?>
                                <option value="<?= e($cond) ?>" <?= $old['condition'] === $cond ? 'selected' : '' ?>><?= e($cond) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="image">Product Image</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-text">Max 5 MB. Accepted: JPG, PNG, GIF, WEBP.</div>
                </div>

                <hr class="border-secondary my-4">
                <h6 class="mb-3 text-muted"><i class="fas fa-cogs me-1"></i> Optional Specifications (JSON data)</h6>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="row mb-2">
                    <div class="col-5">
                        <input type="text" name="spec_key_<?= $i ?>" class="form-control form-control-sm" placeholder="Key (e.g. Author)" value="<?= e($old["spec_key_$i"]) ?>">
                    </div>
                    <div class="col-7">
                        <input type="text" name="spec_val_<?= $i ?>" class="form-control form-control-sm" placeholder="Value (e.g. Navathe)" value="<?= e($old["spec_val_$i"]) ?>">
                    </div>
                </div>
                <?php endfor; ?>
                <div class="form-text mb-4">Add up to 3 key-value specifications stored as JSON.</div>

                <button type="submit" class="btn btn-primary-custom">
                    <i class="fas fa-upload me-2"></i>Publish Listing
                </button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
