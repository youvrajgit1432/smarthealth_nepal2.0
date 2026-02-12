<?php
/**
 * Admin Login Debug Page
 * Test login with visible debug information
 */
session_start();

require_once __DIR__ . '/../../../../backend/config/database.php';
require_once __DIR__ . '/../../../../backend/config/language.php';

global $db;

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Admin Login Debug</title>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; background-color: #f5f5f5; }
        .debug-box { background-color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .success { color: green; background-color: #e8f5e9; padding: 10px; }
        .error { color: red; background-color: #ffebee; padding: 10px; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>Admin Login Debug</h1>";

// Test 1: Database Connection
echo "<div class='debug-box'>
    <h3>1. Database Connection Test</h3>";
    
if (!$db) {
    echo "<div class='error'>✗ Database connection FAILED</div>";
} else {
    echo "<div class='success'>✓ Database connection OK</div>";
    echo "<p>DB Status: Connected</p>";
}
echo "</div>";

// Test 2: Check admin in database
echo "<div class='debug-box'>
    <h3>2. Admin Users in Database</h3>";
    
if ($db) {
    $sql = "SELECT id, username, email, role, is_active FROM admins";
    $result = $db->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<table class='table table-bordered'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Active</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['username']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>{$row['role']}</td>";
            echo "<td>" . ($row['is_active'] ? '✓ Yes' : '✗ No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>✗ No admin users found in database</div>";
    }
}
echo "</div>";

// Test 3: Test login form
echo "<div class='debug-box'>
    <h3>3. Test Login</h3>
    <form method='POST'>
        <div class='mb-3'>
            <label class='form-label'>Email:</label>
            <input type='Email' name='email' class='form-control' value='admin@smarthealth.local'>
        </div>
        <div class='mb-3'>
            <label class='form-label'>Password:</label>
            <input type='text' name='password' class='form-control' value='admin123'>
        </div>
        <button type='submit' name='test_login' class='btn btn-primary'>Test Login</button>
    </form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<hr><h4>Login Test Result:</h4>";
    echo "<p><strong>Testing with:</strong></p>";
    echo "<ul>";
    echo "<li>Email: <code>$email</code></li>";
    echo "<li>Password: <code>$password</code></li>";
    echo "</ul>";
    
    if ($db) {
        $sql = "SELECT id, username, email, password_hash, role FROM admins WHERE email = ? OR username = ?";
        $stmt = $db->prepare($sql);
        
        if (!$stmt) {
            echo "<div class='error'>✗ Database prepare error: " . htmlspecialchars($db->error) . "</div>";
        } else {
            echo "<div class='success'>✓ Database query prepared</div>";
            
            $stmt->bind_param('ss', $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo "<div class='success'>✓ User found in database</div>";
                $admin = $result->fetch_assoc();
                
                echo "<p><strong>User Data:</strong></p>";
                echo "<ul>";
                echo "<li>ID: {$admin['id']}</li>";
                echo "<li>Username: {$admin['username']}</li>";
                echo "<li>Email: {$admin['email']}</li>";
                echo "<li>Role: {$admin['role']}</li>";
                echo "<li>Hash: <code>" . substr($admin['password_hash'], 0, 20) . "...</code></li>";
                echo "</ul>";
                
                // Test password verification
                echo "<p><strong>Password Verification:</strong></p>";
                $verify = password_verify($password, $admin['password_hash']);
                
                if ($verify) {
                    echo "<div class='success'>✓ Password is VALID - Login should work!</div>";
                } else {
                    echo "<div class='error'>✗ Password verification FAILED</div>";
                    echo "<p>The hash in database may be corrupted.</p>";
                }
            } else {
                echo "<div class='error'>✗ User NOT found in database</div>";
            }
            
            $stmt->close();
        }
    }
}

echo "</div>";

echo "</div>
</body>
</html>";
?>
