<?php
/**
 * Shared utility functions — Campus Marketplace
 */

require_once __DIR__ . '/../config/database.php';

/* ------------------------------------------------------------------
 * Output helpers
 * ----------------------------------------------------------------*/

/**
 * Escape a string for safe HTML output.
 */
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Format a price as Indian Rupees.
 */
function formatPrice($price): string {
    return '₹' . number_format((float) $price, 2);
}

/**
 * Format currency alias for formatPrice.
 */
function formatCurrency($price): string {
    return formatPrice($price);
}

/**
 * Return a human-friendly time-ago string.
 */
function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Return a real photo image URL for an item using Unsplash Source API or uploaded image.
 */
function getProductImageUrl(?string $imagePath, string $title, ?string $category = null): string {
    if (!empty($imagePath)) {
        // If it starts with http, return directly
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return $imagePath;
        }
        return '/campus-marketplace/' . ltrim($imagePath, '/');
    }

    $lower = strtolower($title . ' ' . ($category ?? ''));

    // High quality real Unsplash photos for campus items
    if (str_contains($lower, 'database') || str_contains($lower, 'data structure') || str_contains($lower, 'textbook') || str_contains($lower, 'book') || str_contains($lower, 'navathe') || str_contains($lower, 'karumanchi')) {
        return 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=700&q=80'; // Books on table
    }
    if (str_contains($lower, 'calculator') || str_contains($lower, 'casio')) {
        return 'https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?auto=format&fit=crop&w=700&q=80'; // Scientific calculator
    }
    if (str_contains($lower, 'headphone') || str_contains($lower, 'jbl') || str_contains($lower, 'audio') || str_contains($lower, 'earphone')) {
        return 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=700&q=80'; // Wireless headphones
    }
    if (str_contains($lower, 'table') || str_contains($lower, 'desk') || str_contains($lower, 'chair') || str_contains($lower, 'furniture')) {
        return 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=700&q=80'; // Wooden study table
    }
    if (str_contains($lower, 'drawing') || str_contains($lower, 'drafter') || str_contains($lower, 'compass') || str_contains($lower, 'kit')) {
        return 'https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?auto=format&fit=crop&w=700&q=80'; // Architectural drawing tools
    }
    if (str_contains($lower, 'mouse') || str_contains($lower, 'logitech') || str_contains($lower, 'keyboard')) {
        return 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=700&q=80'; // Wireless mouse
    }
    if (str_contains($lower, 'badminton') || str_contains($lower, 'racquet') || str_contains($lower, 'yonex') || str_contains($lower, 'sports')) {
        return 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=700&q=80'; // Badminton racquet
    }
    if (str_contains($lower, 'kettle') || str_contains($lower, 'electric')) {
        return 'https://images.unsplash.com/photo-1594213114663-ddf3f2a08126?auto=format&fit=crop&w=700&q=80'; // Electric kettle
    }
    if (str_contains($lower, 'shirt') || str_contains($lower, 'clothing') || str_contains($lower, 'hoodie') || str_contains($lower, 'jacket')) {
        return 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=700&q=80'; // Crisp white shirt
    }
    if (str_contains($lower, 'hub') || str_contains($lower, 'adapter') || str_contains($lower, 'usb')) {
        return 'https://images.unsplash.com/photo-1625842268584-8f3296236761?auto=format&fit=crop&w=700&q=80'; // USB-C hub adapter
    }
    if (str_contains($lower, 'lamp') || str_contains($lower, 'light') || str_contains($lower, 'led')) {
        return 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=700&q=80'; // Desk lamp
    }

    // Keyword fallback using Unsplash curated search query
    $cleanQuery = urlencode(preg_replace('/[^a-zA-Z0-9 ]/', '', $title));
    return "https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=700&q=80";
}

/* ------------------------------------------------------------------
 * Flash messages
 * ----------------------------------------------------------------*/

function setFlash(string $key, string $message): void {
    $_SESSION['flash_' . $key] = $message;
}

function getFlash(string $key): string {
    $msg = $_SESSION['flash_' . $key] ?? '';
    unset($_SESSION['flash_' . $key]);
    return $msg;
}

/* ------------------------------------------------------------------
 * Validation helpers
 * ----------------------------------------------------------------*/

function isValidEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isPositiveDecimal($value): bool {
    return is_numeric($value) && (float) $value > 0;
}

/* ------------------------------------------------------------------
 * Image upload helper
 * ----------------------------------------------------------------*/

/**
 * Handle a product image upload.
 *
 * @return string|null  The stored path relative to the project root, or null on failure.
 */
function handleImageUpload(array $file, array &$errors): ?string {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_ext   = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_size      = 5 * 1024 * 1024; // 5 MB

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no file uploaded — that's fine
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload failed (error code ' . $file['error'] . ').';
        return null;
    }
    // Check MIME
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed_types, true)) {
        $errors[] = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP.';
        return null;
    }
    // Check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext, true)) {
        $errors[] = 'Invalid file extension.';
        return null;
    }
    // Check size
    if ($file['size'] > $max_size) {
        $errors[] = 'Image exceeds the maximum size of 5 MB.';
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
    $destPath = $uploadDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $errors[] = 'Failed to save the uploaded image.';
        return null;
    }

    return 'uploads/products/' . $safeName;
}

/* ------------------------------------------------------------------
 * Product‐data JSON helpers
 * ----------------------------------------------------------------*/

function validCategories(): array {
    return [
        'Textbooks and Academic Materials',
        'Electronics and Gadgets',
        'Hostel Essentials',
        'Furniture',
        'Clothing and Accessories',
        'Sports Equipment',
        'Other',
    ];
}

function validConditions(): array {
    return ['Like New', 'Good', 'Fair', 'Needs Repair'];
}

function validStatuses(): array {
    return ['Available', 'Reserved', 'Sold', 'Archived'];
}

/**
 * Build and validate the product_data JSON structure.
 */
function buildProductData(string $category, string $condition, array $specs = []): ?string {
    if (!in_array($category, validCategories(), true)) return null;
    if (!in_array($condition, validConditions(), true)) return null;

    $data = [
        'category'  => $category,
        'condition'  => $condition,
    ];
    // Only add specifications that are non-empty
    $cleanSpecs = array_filter($specs, fn($v) => is_string($v) && trim($v) !== '');
    if (!empty($cleanSpecs)) {
        $data['specifications'] = $cleanSpecs;
    }

    return json_encode($data, JSON_UNESCAPED_UNICODE);
}

/* ------------------------------------------------------------------
 * Pagination helper
 * ----------------------------------------------------------------*/

function paginationInfo(int $totalRows, int $perPage, int $currentPage): array {
    $totalPages  = max(1, (int) ceil($totalRows / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset      = ($currentPage - 1) * $perPage;
    return compact('totalRows', 'perPage', 'currentPage', 'totalPages', 'offset');
}

/* ------------------------------------------------------------------
 * JSON API response
 * ----------------------------------------------------------------*/

function jsonResponse(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
