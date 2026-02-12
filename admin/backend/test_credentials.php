<?php
/**
 * Database Credential Test Script
 * Check if admin user exists and verify password hash
 */

require_once __DIR__ . '/../backend/config/database.php';

$email = 'admin@smarthealth.local';
$password = 'admin123';

echo "=== Admin Login Test ===\n";
echo "Email: $email\n";
echo "Password: $password\n\n";

global $db;

// Check if connection is successful
if (!$db) {
    die("Database connection failed\n");
}

echo "✓ Database connected\n\n";

// Check if admin user exists
$sql = "SELECT id, username, email, password_hash, role, is_active FROM admins WHERE email = ? OR username = ?";
$stmt = $db->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $db->error . "\n");
}

$stmt->bind_param('ss', $email, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "✗ No admin user found with email: $email\n";
    echo "\nListing all admins in database:\n";
    $list_sql = "SELECT id, username, email, role, is_active FROM admins";
    $list_result = $db->query($list_sql);
    while ($row = $list_result->fetch_assoc()) {
        echo "  - ID: {$row['id']}, Username: {$row['username']}, Email: {$row['email']}, Role: {$row['role']}, Active: {$row['is_active']}\n";
    }
} else {
    $admin = $result->fetch_assoc();
    echo "✓ Admin found:\n";
    echo "  - ID: {$admin['id']}\n";
    echo "  - Username: {$admin['username']}\n";
    echo "  - Email: {$admin['email']}\n";
    echo "  - Role: {$admin['role']}\n";
    echo "  - Active: " . ($admin['is_active'] ? 'Yes' : 'No') . "\n";
    echo "  - Password Hash: {$admin['password_hash']}\n\n";
    
    // Test password verification
    echo "Testing password verification:\n";
    echo "  - Password to verify: '$password'\n";
    
    $verify_result = password_verify($password, $admin['password_hash']);
    
    if ($verify_result) {
        echo "  ✓ Password verification: SUCCESS\n";
    } else {
        echo "  ✗ Password verification: FAILED\n";
        echo "\nTesting with the hash from insert script:\n";
        $test_hash = '$2y$10$aF9ZpM0Qv8vHkNV5a2ZaDeqPyF3L.ZKJ.QmVp1R0E5c5eZ2JZ6bHm';
        $test_result = password_verify($password, $test_hash);
        echo "  - Hash: $test_hash\n";
        echo "  - Result: " . ($test_result ? 'SUCCESS' : 'FAILED') . "\n";
    }
}

$stmt->close();
$db->close();

?>
