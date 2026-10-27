<?php
/**
 * CSRF Protection Helpers
 *
 * Generates and validates one-time tokens to protect state-changing requests.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate (or retrieve) a CSRF token for the current session.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Return a hidden <input> containing the current CSRF token.
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

/**
 * Validate a submitted CSRF token.
 */
function validateCsrf(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals(csrfToken(), $token);
}

/**
 * Validate an explicit CSRF token.
 */
function validateCsrfToken(?string $token = null): bool {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }
    return is_string($token) && hash_equals(csrfToken(), $token);
}

/**
 * Abort with 403 if the CSRF token is invalid.
 */
function requireCsrf(): void {
    if (!validateCsrf()) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Invalid or missing CSRF token.']));
    }
}
