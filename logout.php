<?php
/**
 * Logout — Campus Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logoutUser();
// Start new session for flash
session_start();
setFlash('success', 'You have been logged out.');
header('Location: /campus-marketplace/login.php');
exit;
