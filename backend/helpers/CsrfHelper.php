<?php
/**
 * SmartHealth Nepal - CSRF Protection Helper
 * ------------------------------------------
 * Small, dependency-free helper for state-changing admin/hospital forms.
 *
 * Usage in a form:
 *   <?php echo csrf_field(); ?>
 *
 * Usage when handling a POST:
 *   if (!csrf_verify()) { ...reject... }
 */

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * Verify the CSRF token on the current request.
     * Returns true when valid, false otherwise.
     */
    function csrf_verify(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return !empty($_SESSION['csrf_token'])
            && is_string($token)
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
