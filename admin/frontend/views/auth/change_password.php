<?php
/**
 * Admin - Change Password
 */

require_once __DIR__ . '/../../../backend/init.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['admin_language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

$message = '';
$error = '';

// Handle form submission
if ($_POST) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Get current password hash
        $admin_id = $_SESSION['admin_id'];
        $result = $db->query("SELECT password_hash FROM admins WHERE id = $admin_id");
        $admin = $result->fetch_assoc();
        
        // Verify current password
        if (!password_verify($current_password, $admin['password_hash'])) {
            $error = 'Current password is incorrect';
        } else {
            // Update password
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $update_result = $db->query("UPDATE admins SET password_hash = '$new_hash' WHERE id = $admin_id");
            
            if ($update_result) {
                $message = 'Password changed successfully!';
            } else {
                $error = 'Failed to update password';
            }
        }
    }
}

$pageTitle = $lang['change_password'] ?? 'Change Password';
$activePage = 'profile';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">
        <i class="fas fa-key"></i> <?php echo $lang['change_password'] ?? 'Change Password'; ?>
    </h2>
    
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">Update Your Password</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                            <small class="text-muted">Enter your current password for verification</small>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                            <small class="text-muted">Minimum 6 characters required</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                            <small class="text-muted">Re-enter your new password</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Update Password
                            </button>
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Profile
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i> <strong>Password Security Tips:</strong>
                <ul class="mb-0 mt-2">
                    <li>Use a combination of uppercase and lowercase letters</li>
                    <li>Include numbers and special characters</li>
                    <li>Use a password you haven't used before</li>
                    <li>Never share your password with anyone</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
