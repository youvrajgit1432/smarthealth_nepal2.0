<?php
/**
 * Admin Login
 */
session_start();

// Load database configuration
require_once __DIR__ . '/../../../../backend/config/database.php';
require_once __DIR__ . '/../../../../backend/config/language.php';

// Make db available globally
global $db;

// Load language
$lang_file = __DIR__ . '/../../../../backend/lang/' . ($_SESSION['admin_language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
} else {
    $lang = [];
}

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/dashboard/');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = $lang['email_password_required'] ?? 'Email and password are required';
    } else {
        // Check admin credentials
        if (!$db) {
            $error = 'Database connection error. Please contact administrator.';
        } else {
            // Query to find admin by email or username
            $sql = "SELECT id, username, password_hash, role, email FROM admins WHERE email = ? OR username = ?";
            $stmt = $db->prepare($sql);
            
            if (!$stmt) {
                $error = 'Database error: ' . htmlspecialchars($db->error);
            } else {
                $stmt->bind_param('ss', $email, $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $admin = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $admin['password_hash'])) {
                        // Set session variables
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_email'] = $admin['email'];
                        $_SESSION['admin_username'] = $admin['username'];
                        $_SESSION['admin_role'] = $admin['role'];
                        $_SESSION['is_admin'] = true;
                        
                        // Update last login
                        $update_sql = "UPDATE admins SET last_login = NOW() WHERE id = ?";
                        $update_stmt = $db->prepare($update_sql);
                        if ($update_stmt) {
                            $update_stmt->bind_param('i', $admin['id']);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }
                        
                        // Redirect to dashboard
                        header('Location: /smarthealth_nepal/admin/frontend/views/dashboard/');
                        exit;
                    } else {
                        $error = $lang['invalid_credentials'] ?? 'Invalid email or password';
                    }
                } else {
                    $error = $lang['user_not_found'] ?? 'Admin user not found';
                }
                
                $stmt->close();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['admin_language'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['admin_login'] ?? 'Admin Login'; ?> - SmartHealth Nepal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        
        .login-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .login-header {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-control:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 0.2rem rgba(0, 86, 179, 0.25);
        }
        
        .btn-login {
            background-color: #0056b3;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
        }
        
        .btn-login:hover {
            background-color: #004085;
            color: white;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="card login-card">
        <div class="login-header">
            <h1><i class="fas fa-hospital"></i> SmartHealth</h1>
            <p class="mb-0"><?php echo $lang['admin_panel'] ?? 'Admin Panel'; ?></p>
        </div>
        
        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label"><?php echo $lang['email'] ?? 'Email'; ?></label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label"><?php echo $lang['password'] ?? 'Password'; ?></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-login w-100 text-white">
                    <?php echo $lang['login'] ?? 'Login'; ?>
                </button>
            </form>
            
            <hr>
            
            <div class="text-center" style="font-size: 0.9em; color: #666;">
                <p><?php echo $lang['demo_credentials'] ?? 'Demo Credentials:'; ?></p>
                <p>
                    <?php echo $lang['email'] ?? 'Email'; ?>: admin@smarthealth.local<br>
                    <?php echo $lang['password'] ?? 'Password'; ?>: admin123
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
