<?php
/**
 * Logout API
 * Clears user session and redirects to home
 */

header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Return JSON response
echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully',
    'redirect' => '/smarthealth_nepal/frontend/views/home/'
]);
?>
