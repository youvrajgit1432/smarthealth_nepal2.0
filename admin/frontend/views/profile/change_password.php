<?php
/**
 * Admin - Change Password
 * Change admin password
 */

require_once __DIR__ . '/../../../backend/init.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

global $db;
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Fetch current admin
    $stmt = $db->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    
    // Validation
    if (empty($current_password)) {
        $error = 'Current password is required';
    } elseif (!password_verify($current_password, $admin['password'])) {
        $error = 'Current password is incorrect';
    } elseif (empty($new_password)) {
        $error = 'New password is required';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif ($current_password === $new_password) {
        $error = 'New password must be different from current password';
    }
    
    if (empty($error)) {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $_SESSION['admin_id']);
        
        if ($stmt->execute()) {
            $message = 'Password changed successfully! Please login again.';
            // Clear session and redirect
            session_destroy();
            sleep(2);
            header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
            exit;
        } else {
            $error = 'Failed to change password. Please try again.';
        }
    }
}

$pageTitle = 'Change Password';
$activePage = 'profile';
?>

<?php require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<style>
    .form-section {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        max-width: 600px;
        margin: 0 auto;
    }
    
    .form-section h3 {
        color: #0056b3;
        margin: 0 0 2rem 0;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
        font-weight: 600;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }
    
    .password-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .form-group input[type="password"],
    .form-group input[type="text"] {
        width: 100%;
        padding: 0.75rem 2.5rem 0.75rem 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #0056b3;
        box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
    }
    
    .toggle-password {
        position: absolute;
        right: 0.75rem;
        background: none;
        border: none;
        color: #0056b3;
        cursor: pointer;
        font-size: 1.1rem;
    }
    
    .form-help {
        font-size: 0.85rem;
        color: #666;
        margin-top: 0.3rem;
    }
    
    .password-requirements {
        background: #f8f9fa;
        border-left: 3px solid #0056b3;
        padding: 1rem;
        border-radius: 4px;
        margin: 2rem 0;
        font-size: 0.9rem;
    }
    
    .password-requirements h5 {
        margin: 0 0 0.75rem 0;
        color: #0056b3;
        font-weight: 600;
    }
    
    .password-requirements ul {
        margin: 0;
        padding-left: 1.25rem;
    }
    
    .password-requirements li {
        margin-bottom: 0.25rem;
        color: #555;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .btn-submit {
        padding: 0.75rem 2rem;
        background: #0056b3;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-submit:hover {
        background: #003d99;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 86, 179, 0.3);
    }
    
    .btn-cancel {
        padding: 0.75rem 2rem;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-cancel:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .form-section {
            padding: 1.5rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn-submit, .btn-cancel {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-key"></i> Change Password</h2>
</div>

<div class="form-section">
    <?php if ($message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <h3><i class="fas fa-lock"></i> Update Your Password</h3>
    
    <div class="password-requirements">
        <h5>Password Requirements:</h5>
        <ul>
            <li>Minimum 6 characters long</li>
            <li>Different from your current password</li>
            <li>Keep it secure and memorable</li>
        </ul>
    </div>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="current_password">Current Password <span style="color: #dc3545;">*</span></label>
            <div class="password-input-wrapper">
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    required
                >
                <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="form-help">Enter your current password for security verification</div>
        </div>
        
        <div class="form-group">
            <label for="new_password">New Password <span style="color: #dc3545;">*</span></label>
            <div class="password-input-wrapper">
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    required
                >
                <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="form-help">Choose a strong password (minimum 6 characters)</div>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm New Password <span style="color: #dc3545;">*</span></label>
            <div class="password-input-wrapper">
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                >
                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="form-help">Re-enter your new password to confirm</div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Change Password
            </button>
            <a href="/smarthealth_nepal/admin/frontend/views/profile/view.php" class="btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = event.target.closest('button');
        const icon = button.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
