<?php
/**
 * Admin Backend Initialization
 */

// Load application configuration (APP_ENV / DEBUG_MODE + error display)
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../../backend/config/sms.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load core database
require_once __DIR__ . '/config/database.php';

// Load language config
require_once __DIR__ . '/config/language.php';

// Set default language for admin
if (!isset($_SESSION['admin_language'])) {
    $_SESSION['admin_language'] = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'en';
}

// Load language file
$langFile = __DIR__ . '/lang/' . $_SESSION['admin_language'] . '.php';
if (file_exists($langFile)) {
    require_once $langFile;
} else {
    require_once __DIR__ . '/lang/en.php';
}

global $db;
global $lang;

// Ensure database connection
if (!isset($db) || $db->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection not established'
    ]));
}

// Admin session validation function
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
        exit;
    }
}

function redirectIfLoggedIn() {
    if (isAdminLoggedIn()) {
        header('Location: /smarthealth_nepal/admin/frontend/views/dashboard/');
        exit;
    }
}

?>
