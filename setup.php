<?php
/**
 * SmartHealth Nepal - Database Setup Script
 * Run this script once to initialize the database and insert sample data
 */

// Show all errors during setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/backend/config/database.php';

$output = [];

try {
    // Read SQL file
    $sql_file = __DIR__ . '/database/smarthealth_nepal.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: " . $sql_file);
    }
    
    $sql_content = file_get_contents($sql_file);
    $output[] = "✓ SQL file loaded";
    
    // Execute SQL statements
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    $executed = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement) && strpos($statement, '--') !== 0) {
            if ($connection->multi_query($statement)) {
                $executed++;
                // Clear result set for multi_query
                while ($connection->next_result()) {
                    if ($connection->more_results()) {
                        continue;
                    }
                }
            } else {
                // Some statements may fail if tables already exist - that's ok
                $output[] = "⚠ Statement skipped (likely already exists): " . substr($statement, 0, 50) . "...";
            }
        }
    }
    
    $output[] = "✓ Database schema created/verified ({$executed} statements executed)";
    
    // Verify admin user exists
    $result = $connection->query("SELECT COUNT(*) as count FROM admins");
    $admin_count = $result->fetch_assoc()['count'];
    
    if ($admin_count == 0) {
        // Create admin user
        $email = 'admin@smarthealth.local';
        $password = 'admin123';
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO admins (email, password_hash, role, created_at) VALUES (?, ?, 'Super Admin', NOW())";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('ss', $email, $password_hash);
        
        if ($stmt->execute()) {
            $output[] = "✓ Sample admin user created: admin@smarthealth.local / admin123";
        } else {
            $output[] = "✗ Failed to create admin user: " . $connection->error;
        }
    } else {
        $output[] = "✓ Admin user already exists ({$admin_count} user(s))";
    }
    
    // Verify sample users exist
    $result = $connection->query("SELECT COUNT(*) as count FROM users");
    $user_count = $result->fetch_assoc()['count'];
    $output[] = "✓ Sample users: {$user_count}";
    
    // Verify departments exist
    $result = $connection->query("SELECT COUNT(*) as count FROM departments");
    $dept_count = $result->fetch_assoc()['count'];
    $output[] = "✓ Departments: {$dept_count}";
    
    $output[] = "";
    $output[] = "========== DATABASE SETUP COMPLETE ==========";
    $output[] = "✓ Database: " . DB_NAME;
    $output[] = "✓ Host: " . DB_HOST;
    $output[] = "";
    $output[] = "Admin Login Credentials:";
    $output[] = "  Email: admin@smarthealth.local";
    $output[] = "  Password: admin123";
    $output[] = "";
    $output[] = "You can now access the admin panel at:";
    $output[] = "  http://localhost/smarthealth_nepal/admin/public/";
    
} catch (Exception $e) {
    $output[] = "✗ ERROR: " . $e->getMessage();
    $output[] = "Database error: " . $connection->error;
}

$connection->close();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SmartHealth Nepal - Database Setup</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .setup-container h1 {
            color: #0056b3;
            margin-bottom: 30px;
            text-align: center;
        }
        .output {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.6;
            max-height: 400px;
            overflow-y: auto;
        }
        .success-icon { color: #28a745; }
        .warning-icon { color: #ffc107; }
        .error-icon { color: #dc3545; }
        .next-steps {
            margin-top: 30px;
            padding: 20px;
            background-color: #d1ecf1;
            border-left: 4px solid #0c5460;
            border-radius: 4px;
            color: #0c5460;
        }
        .next-steps h5 {
            color: #0c5460;
            margin-bottom: 10px;
        }
        .next-steps a {
            color: #0c5460;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="setup-container">
    <h1><i class="fas fa-hospital"></i> SmartHealth Nepal</h1>
    <h3 style="text-align: center; color: #666; margin-bottom: 30px;">Database Setup</h3>
    
    <div class="output">
<?php
foreach ($output as $line) {
    // Color coding for different types of messages
    if (strpos($line, '✓') === 0) {
        echo '<span class="success-icon">' . htmlspecialchars($line) . '</span>' . "\n";
    } elseif (strpos($line, '⚠') === 0) {
        echo '<span class="warning-icon">' . htmlspecialchars($line) . '</span>' . "\n";
    } elseif (strpos($line, '✗') === 0) {
        echo '<span class="error-icon">' . htmlspecialchars($line) . '</span>' . "\n";
    } else {
        echo htmlspecialchars($line) . "\n";
    }
}
?>
    </div>
    
    <div class="next-steps">
        <h5>Next Steps:</h5>
        <ul style="margin-bottom: 0;">
            <li>Log in to the <a href="/smarthealth_nepal/admin/public/">Admin Panel</a></li>
            <li>Configure hospital settings and departments</li>
            <li>Create staff accounts</li>
            <li>Start managing patient queue</li>
        </ul>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
