<?php
/**
 * Hospital Admin Login Page
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['hospital_admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/hospital/dashboard.php');
    exit;
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password';
    } else {
        require_once __DIR__ . '/../../../backend/controllers/HospitalAuthController.php';
        $auth = new \App\Controllers\HospitalAuthController();
        $result = $auth->login($username, $password);

        if ($result['success']) {
            $_SESSION['hospital_admin_id'] = $result['admin']['id'];
            $_SESSION['hospital_id'] = $result['admin']['hospital_id'];
            $_SESSION['admin_name'] = $result['admin']['full_name'];
            $_SESSION['hospital_name'] = $result['admin']['hospital_name'];
            $_SESSION['admin_role'] = $result['admin']['role'];
            $_SESSION['admin_email'] = $result['admin']['email'];
            
            header('Location: /smarthealth_nepal/admin/frontend/views/hospital/dashboard.php');
            exit;
        } else {
            $error_message = $result['message'];
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            max-width: 900px;
            width: 90%;
        }

        .login-form {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .login-form p {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input::placeholder {
            color: #bdc3c7;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            margin: 0;
            cursor: pointer;
        }

        .remember-forgot input[type="checkbox"] {
            margin-right: 8px;
        }

        .remember-forgot a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .remember-forgot a:hover {
            color: #5568d3;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .login-info {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-info h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }

        .login-info p {
            margin-bottom: 15px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
        }

        .demo-credentials {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 6px;
            margin-top: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .demo-credentials h3 {
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .demo-credentials p {
            font-size: 13px;
            margin: 5px 0;
        }

        .bottom-links {
            margin-top: 20px;
            text-align: center;
        }

        .bottom-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .bottom-links a:hover {
            color: #764ba2;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-info {
                padding: 30px;
            }

            .login-form {
                padding: 30px;
            }

            .login-info h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1>🏥 Hospital Admin</h1>
            <p>Manage your hospital operations</p>

            <?php if (!empty($error_message)): ?>
                <div class="alert error">
                    ⚠️ <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert success">
                    ✓ <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username"
                        required
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="/smarthealth_nepal/admin/frontend/views/auth/forgot_password.php">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="bottom-links">
                <p>Don't have access? <a href="/smarthealth_nepal/index.php">Back to Home</a></p>
            </div>
        </div>

        <div class="login-info">
            <h2>Welcome to Hospital Administration</h2>
            <p>Manage your hospital's:</p>
            <p>✓ Patient tokens and queue management</p>
            <p>✓ Assisted booking registrations</p>
            <p>✓ Department and staff management</p>
            <p>✓ Real-time reports and analytics</p>
            <p>✓ Hospital settings and configuration</p>

            <div class="demo-credentials">
                <h3>Demo Credentials</h3>
                <p><strong>Hospital:</strong> Bir Hospital</p>
                <p><strong>Username:</strong> bir_admin</p>
                <p><strong>Password:</strong> password (default)</p>
                <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.2); margin: 10px 0;">
                <p style="font-size: 12px; opacity: 0.8;">For security, change password after first login</p>
            </div>
        </div>
    </div>
</body>
</html>
