<?php
/**
 * Hospital Admin Login Page
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    // Redirect to dashboard
    if (isset($_SESSION['hospital_id']) && $_SESSION['hospital_id']) {
        header('Location: /smarthealth_nepal/admin/hospital/dashboard/?hospital_id=' . $_SESSION['hospital_id']);
    } else {
        header('Location: /smarthealth_nepal/admin/hospital/dashboard/');
    }
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            // Include database connection
            require_once __DIR__ . '/../../backend/config/database.php';
            
            // Check connection
            if (!isset($db) || $db->connect_error) {
                throw new Exception('Database connection failed');
            }

            // Query for admin user
            $query = "SELECT a.*, h.hospital_name 
                     FROM admins a 
                     LEFT JOIN hospital_locations h ON a.hospital_id = h.id 
                     WHERE a.username = ? LIMIT 1";
            
            $stmt = $db->prepare($query);
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            $stmt->close();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Successful login
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'] ?? $admin['username'];
                $_SESSION['admin_email'] = $admin['email'] ?? '';
                $_SESSION['admin_role'] = $admin['role'] ?? 'Admin';
                $_SESSION['hospital_id'] = $admin['hospital_id'];
                // Check for SuperAdmin or HospitalAdmin roles (case-insensitive)
                $_SESSION['access_type'] = strtolower($admin['role'] ?? '') === 'superadmin' ? 'super' : 'hospital';

                $success = 'Login successful. Redirecting...';

                // Redirect based on access type and hospital assignment
                if ($_SESSION['access_type'] === 'super' && $_SESSION['hospital_id']) {
                    // SuperAdmin with hospital assigned - redirect to that hospital's dashboard
                    header('Location: /smarthealth_nepal/admin/hospital/dashboard/?hospital_id=' . $_SESSION['hospital_id']);
                } elseif ($_SESSION['access_type'] === 'super') {
                    // SuperAdmin without specific hospital - show hospital selector
                    header('Location: /smarthealth_nepal/admin/hospital/dashboard/');
                } else {
                    // Hospital admin - redirect to their hospital dashboard
                    header('Location: /smarthealth_nepal/admin/hospital/dashboard/?hospital_id=' . $_SESSION['hospital_id']);
                }
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Admin Login - SmartHealth Nepal</title>
    <link rel="stylesheet" href="../../frontend/css/admin.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 600;
        }

        .login-header p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            margin-top: 20px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background-color: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #999;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .demo-credentials {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }

        .demo-credentials strong {
            display: block;
            margin-bottom: 5px;
        }

        .demo-credentials p {
            margin: 3px 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>SmartHealth</h1>
                <p>Hospital Admin Panel</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autofocus
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                    >
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="demo-credentials">
                <strong>Demo Credentials:</strong>
                <p><strong>Hospital Admin:</strong></p>
                <p>Username: bir_admin</p>
                <p>Password: password (or set by superadmin)</p>
                <p style="margin-top: 10px;"><strong>Super Admin:</strong></p>
                <p>Username: superadmin</p>
                <p>Password: password</p>
            </div>

            <div class="login-footer">
                <a href="/smarthealth_nepal/">Back to Home</a> | 
                <a href="/smarthealth_nepal/admin/public/index.php">Super Admin Login</a>
            </div>
        </div>
    </div>
</body>
</html>
