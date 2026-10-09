<?php
/**
 * Authentication & Role-Based Access Control Helpers
 * Semester V Web Development Course
 */

// Start session automatically if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the client currently holds an authenticated session.
 *
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Retrieve active session user details.
 *
 * @return array|null
 */
function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'role'     => $_SESSION['role'] ?? ''
    ];
}

/**
 * Protect a page against unauthenticated guest visitors.
 * Redirects guests to the login page.
 *
 * @param string $login_path Path to login.php (default: '../login.php' or 'login.php')
 */
function require_login($login_path = '../login.php') {
    if (!is_logged_in()) {
        header("Location: " . $login_path);
        exit;
    }
}

/**
 * Restrict page access strictly to Administrators.
 * If not logged in -> redirect to login.
 * If logged in with non-admin role -> return HTTP 403 Forbidden.
 *
 * @param string $login_path
 */
function require_admin($login_path = '../login.php') {
    require_login($login_path);

    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        include __DIR__ . '/403.php';
        exit;
    }
}

// Aliases matching course guidelines
function requireLogin($login_path = '../login.php') {
    require_login($login_path);
}

function requireAdmin($login_path = '../login.php') {
    require_admin($login_path);
}

/**
 * Restrict page access strictly to Students.
 * If not logged in -> redirect to login.
 * If logged in with non-student role -> return HTTP 403 Forbidden.
 *
 * @param string $login_path
 */
function require_student($login_path = '../login.php') {
    require_login($login_path);

    if ($_SESSION['role'] !== 'student') {
        http_response_code(403);
        include __DIR__ . '/403.php';
        exit;
    }
}

/**
 * Generate or retrieve the session-based CSRF token.
 *
 * @return string
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token against the session token.
 *
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
