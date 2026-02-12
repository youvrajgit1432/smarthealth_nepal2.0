<?php
/**
 * Admin Backend Initialization
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
        header('Location: /smarthealth_nepal/admin/frontend/views/login.php');
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
