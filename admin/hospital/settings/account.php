<?php
/**
 * Account Settings
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set page variables
$pageTitle = 'Account Settings';
$activePage = '';

// Include header
include '../layouts/header.php';
?>

<div class="section">
    <h2>Account Settings</h2>

    <div style="margin-top: 30px;">
        <!-- Email Settings -->
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4 style="color: #2c3e50; margin-bottom: 5px;">Email Notifications</h4>
                    <p style="color: #8fa8ba; font-size: 13px;">Receive email updates about your account</p>
                </div>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" checked style="width: 18px; height: 18px; cursor: pointer;">
                    <span style="color: #2c3e50; font-weight: 500;">Enabled</span>
                </label>
            </div>
        </div>

        <!-- Two-Factor Authentication -->
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4 style="color: #2c3e50; margin-bottom: 5px;">Two-Factor Authentication (2FA)</h4>
                    <p style="color: #8fa8ba; font-size: 13px;">Add extra security to your account</p>
                </div>
                <button class="btn btn-primary btn-small">Enable 2FA</button>
            </div>
        </div>

        <!-- Session Management -->
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <h4 style="color: #2c3e50; margin-bottom: 15px;">Active Sessions</h4>
            <table style="width: 100%; font-size: 13px;">
                <thead style="display: none;"></thead>
                <tbody>
                    <tr style="display: grid; grid-template-columns: 1fr 1fr 1fr 100px; gap: 15px; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                        <td><strong>Device</strong><br><span style="color: #8fa8ba;">Windows - Chrome</span></td>
                        <td><strong>IP Address</strong><br><span style="color: #8fa8ba;">192.168.1.100</span></td>
                        <td><strong>Last Active</strong><br><span style="color: #8fa8ba;">Today, 10:30 AM</span></td>
                        <td><button class="btn btn-danger btn-small" style="padding: 6px 10px;">Logout</button></td>
                    </tr>
                    <tr style="display: grid; grid-template-columns: 1fr 1fr 1fr 100px; gap: 15px; padding: 12px 0;">
                        <td><strong>Device</strong><br><span style="color: #8fa8ba;">iPhone - Safari</span></td>
                        <td><strong>IP Address</strong><br><span style="color: #8fa8ba;">203.45.67.89</span></td>
                        <td><strong>Last Active</strong><br><span style="color: #8fa8ba;">Yesterday, 5:20 PM</span></td>
                        <td><button class="btn btn-danger btn-small" style="padding: 6px 10px;">Logout</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Account Preferences -->
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <h4 style="color: #2c3e50; margin-bottom: 15px;">Preferences</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label>Language</label>
                    <select>
                        <option selected>English</option>
                        <option>नेपाली (Nepali)</option>
                        <option>हिन्दी (Hindi)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Timezone</label>
                    <select>
                        <option selected>Asia/Kathmandu (UTC+5:45)</option>
                        <option>Asia/Kolkata (UTC+5:30)</option>
                        <option>UTC (UTC+0)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Theme</label>
                    <select>
                        <option selected>Light</option>
                        <option>Dark</option>
                        <option>Auto</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Items Per Page</label>
                    <select>
                        <option>10</option>
                        <option selected>25</option>
                        <option>50</option>
                        <option>100</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button class="btn btn-primary">💾 Save Settings</button>
            <button type="reset" class="btn btn-secondary">↻ Reset</button>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>
