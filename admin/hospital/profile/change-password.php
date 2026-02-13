<?php
/**
 * Change Password
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set page variables
$pageTitle = 'Change Password';
$activePage = '';

// Include header
include '../layouts/header.php';
?>

<div class="section">
    <h2>Change Password</h2>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 6px;">
            <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Hospital</label>
            <div style="font-size: 16px; font-weight: 600; margin-top: 8px;">Bhaktapur Hospital</div>
            <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">HOS-2025-0001</div>
        </div>
        <div style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%); color: white; padding: 20px; border-radius: 6px;">
            <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Your Role</label>
            <div style="font-size: 16px; font-weight: 600; margin-top: 8px;">Hospital Administrator</div>
            <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;"><span style="background: rgba(255,255,255,0.3); padding: 4px 8px; border-radius: 4px;">Active</span></div>
        </div>
    </div>
    
    <div style="max-width: 600px; margin-top: 30px;">
        <div style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); padding: 16px; border-radius: 6px; border-left: 4px solid #17a2b8; margin-bottom: 25px;">
            <p style="color: #0c5460; font-size: 13px; margin: 0;">
                <strong>Password Security Tip:</strong> Use a strong password with uppercase, lowercase, numbers and special characters. Never share your password with anyone.
            </p>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" placeholder="Enter your current password" required>
            </div>

            <hr style="margin: 25px 0; border: none; border-top: 1px solid #e5e7eb;">

            <div class="form-group">
                <label>New Password</label>
                <input type="password" placeholder="Enter new password" id="newPassword" required>
                <small style="color: #8fa8ba; margin-top: 8px; display: block;">
                    Password must be at least 8 characters long
                </small>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" placeholder="Confirm new password" id="confirmPassword" required>
            </div>

            <!-- Password Strength Indicator -->
            <div style="margin-top: 20px;">
                <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Password Strength</label>
                <div style="display: flex; gap: 6px; margin-top: 8px;">
                    <div style="flex: 1; height: 4px; background: #d1d5db; border-radius: 2px;"></div>
                    <div style="flex: 1; height: 4px; background: #d1d5db; border-radius: 2px;"></div>
                    <div style="flex: 1; height: 4px; background: #d1d5db; border-radius: 2px;"></div>
                    <div style="flex: 1; height: 4px; background: #d1d5db; border-radius: 2px;"></div>
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">🔐 Update Password</button>
                <a href="view.php" class="btn btn-secondary">❌ Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Password Change History -->
<div class="section">
    <h3>Password Change History</h3>
    <table>
        <thead>
            <tr>
                <th>Date Changed</th>
                <th>Changed From</th>
                <th>Device</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>February 13, 2026 - 10:30 AM</td>
                <td>192.168.1.100</td>
                <td>Windows - Chrome</td>
                <td><span class="status-badge completed">Success</span></td>
            </tr>
            <tr>
                <td>January 20, 2026 - 3:45 PM</td>
                <td>192.168.1.105</td>
                <td>Windows - Firefox</td>
                <td><span class="status-badge completed">Success</span></td>
            </tr>
            <tr>
                <td>December 15, 2025 - 11:20 AM</td>
                <td>192.168.1.100</td>
                <td>Windows - Chrome</td>
                <td><span class="status-badge completed">Success</span></td>
            </tr>
        </tbody>
    </table>
</div>

<?php include '../layouts/footer.php'; ?>
