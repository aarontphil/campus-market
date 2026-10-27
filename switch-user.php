<?php
/**
 * Quick Switch Student Account for easy multi-page testing
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1;
$redirect = $_GET['redirect'] ?? '/campus-marketplace/index.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($user) {
    loginUser($user);
    setFlash('success', 'Logged in as ' . $user['full_name']);
}

header('Location: ' . $redirect);
exit;
