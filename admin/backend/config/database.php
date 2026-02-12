<?php
/**
 * Admin Backend Database Configuration
 */

require_once __DIR__ . '/../../../backend/config/database.php';

// Database is already configured in main config
global $db;

// Verify database connection is available
if (!isset($db) || !$db) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection not established'
    ]));
}

?>
