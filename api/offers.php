<?php
/**
 * Offers API Endpoint — Campus Marketplace
 * Handles:
 *  - action=create: Buyer creates a new offer
 *  - action=update_status: Seller accepts/rejects offer
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to perform this action.']);
    exit;
}

// CSRF validation via POST body or header
$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCsrfToken($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired CSRF token. Please refresh the page.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$db = getDB();
$uid = currentUserId();

if ($action === 'create') {
    $productId    = (int) ($_POST['product_id'] ?? 0);
    $offeredPrice = $_POST['offered_price'] ?? '';
    $message      = trim($_POST['message'] ?? '');

    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product specified.']);
        exit;
    }

    if (!is_numeric($offeredPrice) || (float) $offeredPrice <= 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Please enter a valid offer price greater than 0.']);
        exit;
    }

    $offeredPrice = round((float) $offeredPrice, 2);

    // Verify product exists and is Available
    $stmt = $db->prepare('SELECT id, seller_id, title, price, status FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }

    if ($product['status'] !== 'Available') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'This product is no longer open for offers (' . e($product['status']) . ').']);
        exit;
    }

    if ((int) $product['seller_id'] === $uid) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You cannot make an offer on your own listing.']);
        exit;
    }

    // Insert offer
    try {
        $stmt = $db->prepare(
            'INSERT INTO offers (product_id, buyer_id, seller_id, offered_price, status, message, created_at, updated_at)
             VALUES (?, ?, ?, ?, "Pending", ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
        );
        $stmt->execute([$productId, $uid, $product['seller_id'], $offeredPrice, $message]);
        $offerId = (int) $db->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Your offer of ' . formatCurrency($offeredPrice) . ' has been submitted!',
            'offer_id' => $offerId
        ]);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error while saving offer.']);
        exit;
    }
}

if ($action === 'update_status') {
    $offerId    = (int) ($_POST['offer_id'] ?? 0);
    $statusVerb = trim($_POST['action'] ?? ''); // 'accept' or 'reject'

    if ($offerId <= 0 || !in_array($statusVerb, ['accept', 'reject', 'cancel'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid offer status update action.']);
        exit;
    }

    // Fetch offer
    $stmt = $db->prepare('SELECT o.*, p.title AS product_title FROM offers o JOIN products p ON p.id = o.product_id WHERE o.id = ?');
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch();

    if (!$offer) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Offer not found.']);
        exit;
    }

    if ($statusVerb === 'cancel') {
        // Only buyer can cancel their own pending offer
        if ((int) $offer['buyer_id'] !== $uid) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Only the buyer can cancel this offer.']);
            exit;
        }
        if ($offer['status'] !== 'Pending') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Only pending offers can be cancelled.']);
            exit;
        }
        $targetStatus = 'Cancelled';
    } else {
        // Seller actions: accept or reject
        if ((int) $offer['seller_id'] !== $uid) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Only the seller can accept or reject this offer.']);
            exit;
        }
        $targetStatus = ($statusVerb === 'accept') ? 'Accepted' : 'Rejected';
    }

    $db->beginTransaction();
    try {
        $updateStmt = $db->prepare('UPDATE offers SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $updateStmt->execute([$targetStatus, $offerId]);

        // If offer is accepted, mark product as Reserved (or Sold)
        if ($targetStatus === 'Accepted') {
            $prodUpdate = $db->prepare('UPDATE products SET status = "Reserved", updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $prodUpdate->execute([$offer['product_id']]);

            // Reject other pending offers for this product
            $rejectOthers = $db->prepare(
                'UPDATE offers SET status = "Rejected", updated_at = CURRENT_TIMESTAMP
                 WHERE product_id = ? AND id != ? AND status = "Pending"'
            );
            $rejectOthers->execute([$offer['product_id'], $offerId]);
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Offer has been ' . strtolower($targetStatus) . ' successfully.',
            'status'  => $targetStatus
        ]);
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update offer status: ' . $e->getMessage()]);
        exit;
    }
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unsupported action requested.']);
