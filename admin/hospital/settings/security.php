<?php
/**
 * Security Settings
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set page variables
$pageTitle = 'Security Settings';
$activePage = '';

// Include header
include '../layouts/header.php';
?>

<div class="section">
    <h2>Security & Privacy</h2>

    <!-- Password Security -->
    <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-top: 30px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h4 style="color: #2c3e50; margin: 0;">🔐 Password Security</h4>
            <span class="status-badge active">Strong</span>
        </div>
        <p style="color: #8fa8ba; font-size: 13px; margin-bottom: 15px;">Your password is secure and meets all requirements</p>
        <button class="btn btn-primary btn-small">Change Password</button>
    </div>

    <!-- Two-Factor Authentication -->
    <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h4 style="color: #2c3e50; margin: 0;">🛡️ Two-Factor Authentication</h4>
            <span class="status-badge inactive">Not Enabled</span>
        </div>
        <p style="color: #8fa8ba; font-size: 13px; margin-bottom: 15px;">Add an extra layer of security to your account by requiring a verification code on login</p>
        <button class="btn btn-primary btn-small">Enable 2FA</button>
    </div>

    <!-- Login Activity -->
    <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
        <h4 style="color: #2c3e50; margin-bottom: 15px;">📊 Recent Login Activity</h4>
        <table style="width: 100%; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid #e5e7eb;">
                    <th style="text-align: left; padding: 10px 0; color: #2c3e50; font-weight: 600;">Date & Time</th>
                    <th style="text-align: left; padding: 10px 0; color: #2c3e50; font-weight: 600;">Device</th>
                    <th style="text-align: left; padding: 10px 0; color: #2c3e50; font-weight: 600;">IP Address</th>
                    <th style="text-align: left; padding: 10px 0; color: #2c3e50; font-weight: 600;">Location</th>
                    <th style="text-align: left; padding: 10px 0; color: #2c3e50; font-weight: 600;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 12px 0;">Feb 13, 2026 - 10:30 AM</td>
                    <td>Windows (Chrome)</td>
                    <td>192.168.1.100</td>
                    <td>Kathmandu, Nepal</td>
                    <td><span class="status-badge active">Success</span></td>
                </tr>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 12px 0;">Feb 12, 2026 - 3:45 PM</td>
                    <td>iPhone (Safari)</td>
                    <td>203.45.67.89</td>
                    <td>Kathmandu, Nepal</td>
                    <td><span class="status-badge active">Success</span></td>
                </tr>
                <tr>
                    <td style="padding: 12px 0;">Feb 11, 2026 - 9:20 AM</td>
                    <td>Windows (Firefox)</td>
                    <td>192.168.1.100</td>
                    <td>Kathmandu, Nepal</td>
                    <td><span class="status-badge active">Success</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Connected Apps -->
    <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
        <h4 style="color: #2c3e50; margin-bottom: 15px;">🔗 Connected Applications</h4>
        <div style="display: grid; gap: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: white; border-radius: 4px;">
                <div>
                    <strong style="color: #2c3e50;">SmartHealth Mobile App</strong>
                    <p style="color: #8fa8ba; font-size: 12px; margin: 4px 0;">Last used: Today, 10:30 AM</p>
                </div>
                <button class="btn btn-danger btn-small">Disconnect</button>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: white; border-radius: 4px;">
                <div>
                    <strong style="color: #2c3e50;">Hospital Dashboard</strong>
                    <p style="color: #8fa8ba; font-size: 12px; margin: 4px 0;">Last used: Yesterday, 5:20 PM</p>
                </div>
                <button class="btn btn-danger btn-small">Disconnect</button>
            </div>
        </div>
    </div>

    <!-- Blocked Users -->
    <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
        <h4 style="color: #2c3e50; margin-bottom: 15px;">🚫 Blocked Users</h4>
        <p style="color: #8fa8ba; font-size: 13px; margin-bottom: 15px;">You have not blocked any users</p>
    </div>

    <!-- Privacy Settings -->
    <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
        <h4 style="color: #2c3e50; margin-bottom: 15px;">👁️ Privacy Settings</h4>
        <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
            <input type="checkbox" checked id="profileVisibility">
            <label for="profileVisibility" style="margin: 0; font-weight: 500;">Make my profile visible to staff members</label>
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
            <input type="checkbox" id="activityTracking">
            <label for="activityTracking" style="margin: 0; font-weight: 500;">Allow activity tracking for performance analytics</label>
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 15px;">
            <input type="checkbox" checked id="dataRecovery">
            <label for="dataRecovery" style="margin: 0; font-weight: 500;">Allow data recovery in case of account issues</label>
        </div>
    </div>

    <div style="display: flex; gap: 15px; margin-top: 30px;">
        <button class="btn btn-primary">💾 Save Security Settings</button>
        <a href="../profile/view.php" class="btn btn-secondary">Cancel</a>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>
