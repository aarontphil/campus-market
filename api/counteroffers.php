<?php
/**
 * Counter-Offers API Endpoint — Campus Marketplace
 * Handles creating a counter-offer in response to an existing offer.
 * When counter-offered:
 *   - The parent offer's status becomes 'Countered'
 *   - A new offer row is created with parent_offer_id linked
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to make a counter-offer.']);
    exit;
}

$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCsrfToken($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired CSRF token. Please refresh the page.']);
    exit;
}

$parentOfferId = (int) ($_POST['parent_offer_id'] ?? 0);
$offeredPrice  = $_POST['offered_price'] ?? '';
$message       = trim($_POST['message'] ?? '');
$uid           = currentUserId();
$db            = getDB();

if ($parentOfferId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parent offer specified.']);
    exit;
}

if (!is_numeric($offeredPrice) || (float) $offeredPrice <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid counter-offer price greater than 0.']);
    exit;
}

$offeredPrice = round((float) $offeredPrice, 2);

// Fetch parent offer and product info
$stmt = $db->prepare(
    'SELECT o.*, p.status AS product_status, p.title AS product_title
     FROM offers o
     JOIN products p ON p.id = o.product_id
     WHERE o.id = ?'
);
$stmt->execute([$parentOfferId]);
$parentOffer = $stmt->fetch();

if (!$parentOffer) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Original offer not found.']);
    exit;
}

if ($parentOffer['product_status'] !== 'Available') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'This product is no longer available.']);
    exit;
}

// Check authorization: must be either the seller or the buyer of the original offer
$isSeller = ((int) $parentOffer['seller_id'] === $uid);
$isBuyer  = ((int) $parentOffer['buyer_id'] === $uid);

if (!$isSeller && !$isBuyer) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You are not authorized to counter this offer.']);
    exit;
}

// In counter-offering:
// If the current user is the seller counter-offering the buyer, buyer remains buyer_id, seller remains seller_id
// The parent offer moves to 'Countered'
$db->beginTransaction();
try {
    // 1. Mark parent offer as Countered
    $updateStmt = $db->prepare('UPDATE offers SET status = "Countered", updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $updateStmt->execute([$parentOfferId]);

    // 2. Insert new offer with parent_offer_id link
    $insertStmt = $db->prepare(
        'INSERT INTO offers (product_id, buyer_id, seller_id, parent_offer_id, offered_price, status, message, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, "Pending", ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
    );
    $insertStmt->execute([
        $parentOffer['product_id'],
        $parentOffer['buyer_id'],
        $parentOffer['seller_id'],
        $parentOfferId,
        $offeredPrice,
        $message !== '' ? $message : ('Counter-offer proposed: ' . formatCurrency($offeredPrice))
    ]);

    $newOfferId = (int) $db->lastInsertId();

    $db->commit();

    echo json_encode([
        'success'        => true,
        'message'        => 'Counter-offer of ' . formatCurrency($offeredPrice) . ' sent successfully!',
        'offer_id'       => $newOfferId,
        'parent_offer_id'=> $parentOfferId
    ]);
    exit;
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error submitting counter-offer: ' . $e->getMessage()]);
    exit;
}
