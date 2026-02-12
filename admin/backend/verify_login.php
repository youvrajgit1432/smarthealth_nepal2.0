<?php
/**
 * Password verification test
 */

echo "=== PASSWORD VERIFICATION TEST ===\n\n";

$password = 'admin123';
$hash = '$2y$10$aF9ZpM0Qv8vHkNV5a2ZaDeqPyF3L.ZKJ.QmVp1R0E5c5eZ2JZ6bHm';

echo "Password to test: $password\n";
echo "Hash: $hash\n";
echo "Result: " . (password_verify($password, $hash) ? "✓ VALID" : "✗ INVALID") . "\n\n";

// Test all passwords
$test_credentials = [
    ['admin123', '$2y$10$aF9ZpM0Qv8vHkNV5a2ZaDeqPyF3L.ZKJ.QmVp1R0E5c5eZ2JZ6bHm', 'admin'],
    ['medicine123', '$2y$10$E9vQ2zT8R3pL1mK4nJ7sOeF6xY9uA0bC5dD2eE3fF4gG5hH6iI7jJ', 'medicine_admin'],
    ['emergency123', '$2y$10$P2sM9nL6kI8jO5hG3fD1eE4cB7aZ0yX9wV6uT5sR4qP3oN2mL1kJ', 'emergency_staff'],
    ['maternal123', '$2y$10$A8bC5dD2eE3fF4gG5hH6iI7jJ0kK1lL2mM3nN4oO5pP6qQ7rR8sS', 'maternal_officer']
];

echo "=== ALL CREDENTIALS TEST ===\n";
foreach ($test_credentials as $cred) {
    $result = password_verify($cred[0], $cred[1]) ? "✓ VALID" : "✗ INVALID";
    echo "{$cred[2]}: {$cred[0]} => $result\n";
}

// Test database connection
echo "\n=== DATABASE CONNECTION TEST ===\n";
require_once __DIR__ . '/../backend/config/database.php';

global $db;

if (!$db) {
    echo "✗ Database connection FAILED\n";
} else {
    echo "✓ Database connection OK\n";
    
    // Check admin in database
    $sql = "SELECT id, username, email, password_hash FROM admins WHERE email = 'admin@smarthealth.local'";
    $result = $db->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "✓ Admin user found in database\n";
        echo "  - ID: {$row['id']}\n";
        echo "  - Username: {$row['username']}\n";
        echo "  - Email: {$row['email']}\n";
        
        $verify = password_verify('admin123', $row['password_hash']);
        echo "  - Password verification: " . ($verify ? "✓ VALID" : "✗ INVALID") . "\n";
    } else {
        echo "✗ Admin user NOT found in database\n";
    }
}

?>
