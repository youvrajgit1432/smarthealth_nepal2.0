<?php
/**
 * View User Profile
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set page variables
$pageTitle = 'My Profile';
$activePage = '';

// Include header
include '../layouts/header.php';
?>

<div class="section">
    <h2>My Profile</h2>
    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 30px; margin-top: 30px;">
        <!-- Profile Picture -->
        <div style="text-align: center;">
            <div style="width: 150px; height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 60px; font-weight: 700; margin: 0 auto; margin-bottom: 15px;">
                <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
            </div>
            <button class="btn btn-primary btn-small">Upload Photo</button>
        </div>

        <!-- Profile Details -->
        <div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Full Name</label>
                    <div style="margin-top: 5px; font-size: 16px; color: #2c3e50; font-weight: 600;"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin User'); ?></div>
                </div>

                <div>
                    <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Role</label>
                    <div style="margin-top: 5px; font-size: 16px; color: #2c3e50; font-weight: 600;"><?php echo ucfirst($_SESSION['access_type'] ?? 'Hospital'); ?> Administrator</div>
                </div>

                <div>
                    <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Email</label>
                    <div style="margin-top: 5px; font-size: 16px; color: #2c3e50; font-weight: 600;"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'admin@smarthealth.com'); ?></div>
                </div>

                <div>
                    <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Phone</label>
                    <div style="margin-top: 5px; font-size: 16px; color: #2c3e50; font-weight: 600;">+977 1-4123456</div>
                </div>

                <div>
                    <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Hospital Name</label>
                    <div style="margin-top: 5px; font-size: 16px; color: #2c3e50; font-weight: 600;">Bhaktapur Hospital</div>
                </div>

                <div>
                    <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Administrator Role</label>
                    <div style="margin-top: 5px; font-size: 16px; color: #2c3e50; font-weight: 600;">Hospital Administrator</div>
                </div>

                <div>
                    <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Status</label>
                    <div style="margin-top: 5px;"><span class="status-badge active">Active</span></div>
                </div>

                <div>
                    <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Last Login</label>
                    <div style="margin-top: 5px; font-size: 16px; color: #2c3e50; font-weight: 600;">Today, 10:30 AM</div>
                </div>

                <div>
                    <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Joined Date</label>
                    <div style="margin-top: 5px; font-size: 16px; color: #2c3e50; font-weight: 600;">January 15, 2026</div>
                </div>

                <div>
                    <label style="font-weight: 600; color: #8fa8ba; font-size: 12px; text-transform: uppercase;">Appointment ID</label>
                    <div style="margin-top: 5px; font-size: 16px; color: #2c3e50; font-weight: 600;">ADM-2026-00145</div>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <a href="edit.php" class="btn btn-primary">✏️ Edit Profile</a>
                <a href="change-password.php" class="btn btn-secondary">🔐 Change Password</a>
            </div>
        </div>
    </div>
</div>

<!-- Additional Info -->
<div class="section">
    <h2>Hospital Information</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 8px;">
            <h4 style="color: white; margin-bottom: 15px;">🏥 Hospital Details</h4>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Hospital Name</label>
                    <div style="font-size: 16px; font-weight: 600; margin-top: 4px;">Bhaktapur Hospital</div>
                </div>
                <div>
                    <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Hospital ID</label>
                    <div style="font-size: 14px; font-weight: 500; margin-top: 4px;">HOS-2025-0001</div>
                </div>
                <div>
                    <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Location</label>
                    <div style="font-size: 14px; font-weight: 500; margin-top: 4px;">Bhaktapur, Kathmandu Valley</div>
                </div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%); color: white; padding: 25px; border-radius: 8px;">
            <h4 style="color: white; margin-bottom: 15px;">👤 Your Role & Access</h4>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Your Position</label>
                    <div style="font-size: 16px; font-weight: 600; margin-top: 4px;">Hospital Administrator</div>
                </div>
                <div>
                    <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Access Level</label>
                    <div style="font-size: 14px; font-weight: 500; margin-top: 4px;">Full Access - All Modules</div>
                </div>
                <div>
                    <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Department</label>
                    <div style="font-size: 14px; font-weight: 500; margin-top: 4px;">Administration</div>
                </div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 25px; border-radius: 8px;">
            <h4 style="color: white; margin-bottom: 15px;">📊 Your Statistics</h4>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Total Logins</label>
                    <div style="font-size: 16px; font-weight: 600; margin-top: 4px;">156</div>
                </div>
                <div>
                    <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Active Since</label>
                    <div style="font-size: 14px; font-weight: 500; margin-top: 4px;">January 15, 2026</div>
                </div>
                <div>
                    <label style="font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Current Status</label>
                    <div style="font-size: 14px; font-weight: 500; margin-top: 4px;"><span class="status-badge active">Active</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Info -->
<div class="section">
    <h3>Additional Information</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 16px; border-radius: 6px; border-left: 4px solid #667eea;">
            <h4 style="color: #2c3e50; margin-bottom: 8px;">Permissions</h4>
            <p style="font-size: 13px; color: #8fa8ba;">Full hospital management access including tokens, staff, and reports</p>
        </div>

        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 16px; border-radius: 6px; border-left: 4px solid #27ae60;">
            <h4 style="color: #2c3e50; margin-bottom: 8px;">Account Security</h4>
            <p style="font-size: 13px; color: #8fa8ba;">Password last changed 30 days ago. Enable 2FA for added security</p>
        </div>

        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 16px; border-radius: 6px; border-left: 4px solid #3498db;">
            <h4 style="color: #2c3e50; margin-bottom: 8px;">Activity</h4>
            <p style="font-size: 13px; color: #8fa8ba;">45 logins this month from 3 different locations</p>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>
