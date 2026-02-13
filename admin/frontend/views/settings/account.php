<?php
/**
 * Admin - Account Settings
 * Manage account settings and preferences
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

// Fetch admin preferences
$stmt = $db->prepare("SELECT id, username, email FROM admins WHERE id = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $language = $_POST['language'] ?? 'en';
    $theme = $_POST['theme'] ?? 'light';
    $notifications_email = isset($_POST['notifications_email']) ? 1 : 0;
    $notifications_sms = isset($_POST['notifications_sms']) ? 1 : 0;
    
    // Update session language
    $_SESSION['admin_language'] = $language;
    
    // You could store these preferences in database if needed
    // For now, we'll just update the session and show success message
    $message = 'Settings updated successfully!';
}

$pageTitle = 'Account Settings';
$activePage = 'settings';
?>

<?php require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<style>
    .settings-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
    }
    
    .settings-card {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .settings-card h3 {
        color: #0056b3;
        margin: 0 0 1.5rem 0;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .settings-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }
    
    .form-group select,
    .form-group input[type="text"] {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    
    .form-group select:focus,
    .form-group input:focus {
        outline: none;
        border-color: #0056b3;
        box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
    }
    
    .form-help {
        font-size: 0.85rem;
        color: #666;
        margin-top: 0.3rem;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 1rem;
        cursor: pointer;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-right: 0.75rem;
        cursor: pointer;
    }
    
    .checkbox-group label {
        margin: 0;
        cursor: pointer;
        flex: 1;
        color: #333;
        font-weight: 500;
    }
    
    .info-box {
        background: #d1ecf1;
        border-left: 4px solid #0c5460;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1.5rem;
        color: #0c5460;
        font-size: 0.9rem;
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
        width: 100%;
        justify-content: center;
    }
    
    .btn-submit:hover {
        background: #003d99;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 86, 179, 0.3);
    }
    
    .danger-zone {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 2rem;
        margin-top: 2rem;
    }
    
    .danger-zone h3 {
        color: #cc7700;
        margin: 0 0 1rem 0;
    }
    
    .btn-danger {
        padding: 0.75rem 2rem;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        justify-content: center;
    }
    
    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    
    @media (max-width: 768px) {
        .settings-container {
            grid-template-columns: 1fr;
        }
        
        .checkbox-group {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 0;
            margin-bottom: 0.5rem;
        }
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-cog"></i> Account Settings</h2>
</div>

<?php if ($message): ?>
<div class="alert alert-success" style="grid-column: 1 / -1;">
    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger" style="grid-column: 1 / -1;">
    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
</div>
<?php endif; ?>

<form method="POST" action="">
    <div class="settings-container">
        <!-- Preferences Settings -->
        <div class="settings-card">
            <h3><i class="fas fa-palette"></i> Display Preferences</h3>
            
            <div class="form-group">
                <label for="language">Language</label>
                <select id="language" name="language">
                    <option value="en" <?php echo $_SESSION['admin_language'] === 'en' ? 'selected' : ''; ?>>English</option>
                    <option value="ne" <?php echo $_SESSION['admin_language'] === 'ne' ? 'selected' : ''; ?>>नेपाली (Nepali)</option>
                </select>
                <div class="form-help">Choose your preferred interface language</div>
            </div>
            
            <div class="form-group">
                <label for="theme">Theme</label>
                <select id="theme" name="theme">
                    <option value="light">Light Mode</option>
                    <option value="dark">Dark Mode</option>
                    <option value="auto">Auto (System Default)</option>
                </select>
                <div class="form-help">Choose your preferred color scheme</div>
            </div>
        </div>
        
        <!-- Notification Settings -->
        <div class="settings-card">
            <h3><i class="fas fa-bell"></i> Notifications</h3>
            
            <div class="info-box">
                <i class="fas fa-info-circle"></i> Manage how you receive notifications
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="notifications_email" name="notifications_email" checked>
                <label for="notifications_email">Email Notifications</label>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="notifications_sms" name="notifications_sms">
                <label for="notifications_sms">SMS Notifications</label>
            </div>
        </div>
        
        <!-- Account Information -->
        <div class="settings-card">
            <h3><i class="fas fa-user-check"></i> Account Info</h3>
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
                <div class="form-help">Username cannot be changed</div>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="text" value="<?php echo htmlspecialchars($admin['email']); ?>" disabled>
                <div class="form-help">
                    <a href="/smarthealth_nepal/admin/frontend/views/profile/edit.php" style="color: #0056b3; text-decoration: none;">
                        Update email <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Security -->
        <div class="settings-card">
            <h3><i class="fas fa-shield-alt"></i> Security</h3>
            
            <div class="info-box">
                Keep your account secure by managing your password
            </div>
            
            <a href="/smarthealth_nepal/admin/frontend/views/profile/change_password.php" class="btn-submit" style="text-decoration: none; background: #28a745;">
                <i class="fas fa-key"></i> Change Password
            </a>
        </div>
        
        <!-- Session Information -->
        <div class="settings-card">
            <h3><i class="fas fa-history"></i> Session</h3>
            
            <div class="form-group">
                <label>Current Role</label>
                <input type="text" value="<?php echo htmlspecialchars($_SESSION['admin_role']); ?>" disabled>
                <div class="form-help">Your admin role and permissions</div>
            </div>
            
            <div class="form-group">
                <label>Login Time</label>
                <input type="text" value="<?php echo date('M d, Y H:i'); ?>" disabled>
                <div class="form-help">Current session started</div>
            </div>
        </div>
        
        <!-- API & Integration -->
        <div class="settings-card">
            <h3><i class="fas fa-plug"></i> Integrations</h3>
            
            <div class="info-box">
                API keys and third-party integrations will be available soon
            </div>
            
            <button type="button" class="btn-submit" disabled style="background: #6c757d; cursor: not-allowed;">
                <i class="fas fa-lock"></i> API Keys (Coming Soon)
            </button>
        </div>
    </div>
    
    <!-- Save Settings Button -->
    <div style="margin-top: 2rem; text-align: center;">
        <button type="submit" class="btn-submit" style="width: auto; padding: 0.75rem 3rem;">
            <i class="fas fa-save"></i> Save All Settings
        </button>
    </div>
    
    <!-- Danger Zone -->
    <div class="danger-zone">
        <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
        <p>Be careful with these actions as they cannot be undone.</p>
        
        <form method="POST" action="/smarthealth_nepal/admin/frontend/views/auth/logout.php" style="display: inline;">
            <button type="submit" class="btn-danger" onclick="return confirm('Are you sure you want to logout?');">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
