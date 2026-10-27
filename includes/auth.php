<?php
/**
 * Authentication Helpers — Campus Marketplace
 */

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check whether a user is currently logged in.
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Get the current user's ID (or null).
 */
function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the current user's full name (or 'Guest').
 */
function currentUserName(): string {
    return $_SESSION['user_name'] ?? 'Guest';
}

/**
 * Get the current user's email (or empty string).
 */
function currentUserEmail(): string {
    return $_SESSION['user_email'] ?? '';
}

/**
 * Redirect unauthenticated visitors to the login page.
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Please log in to access that page.';
        header('Location: /campus-marketplace/login.php');
        exit;
    }
}

/**
 * Set session variables after successful authentication.
 */
function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['user_name']  = $user['full_name'];
    $_SESSION['user_email'] = $user['college_email'];
    $_SESSION['student_id'] = $user['student_id'];
}

/**
 * Destroy session and log out.
 */
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}
