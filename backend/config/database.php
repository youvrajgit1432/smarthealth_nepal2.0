<?php
/**
 * SmartHealth Nepal - Database Configuration
 * Backend Database Connection
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'smarthealth');
define('DB_PORT', 3306);

// MySQLi connection
$connection = new mysqli(
    DB_HOST,
    DB_USER,
    DB_PASSWORD,
    DB_NAME,
    DB_PORT
);

// Check connection
if ($connection->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $connection->connect_error
    ]));
}

// Set charset to UTF-8 for Nepali language support
$connection->set_charset("utf8mb4");

// Global database connection variable
global $db;
$db = $connection;

?>
