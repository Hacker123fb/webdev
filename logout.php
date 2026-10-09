<?php
/**
 * Logout Handler
 * Securely terminates PHP session, removes cookies, and redirects to login.
 */
session_start();

// 1. Clear all session variables
$_SESSION = [];

// 2. Destroy session cookie in browser if configured
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destroy session record on server
session_destroy();

// 4. Redirect to login with confirmation parameter
header("Location: login.php?msg=logged_out");
exit;
