<?php
/**
 * Edit User Profile
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set page variables
$pageTitle = 'Edit Profile';
$activePage = '';

// Include header
include '../layouts/header.php';
?>

<!-- Hospital Info Display -->
<div class="section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 25px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px;">
        <div>
            <label style="font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Hospital Name</label>
            <div style="font-size: 20px; font-weight: 700; margin-top: 8px;">Bhaktapur Hospital</div>
            <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">HOS-2025-0001</div>
        </div>
        <div>
            <label style="font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Your Position</label>
            <div style="font-size: 20px; font-weight: 700; margin-top: 8px;">Hospital Administrator</div>
            <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;">Full Access - All Modules</div>
        </div>
        <div>
            <label style="font-size: 12px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px;">Since</label>
            <div style="font-size: 20px; font-weight: 700; margin-top: 8px;">January 15, 2026</div>
            <div style="font-size: 12px; opacity: 0.9; margin-top: 5px;"><span style="background: rgba(255,255,255,0.3); padding: 4px 8px; border-radius: 4px;">Active</span></div>
        </div>
    </div>
</div>

<div class="section">
    <h2>Edit Personal Information</h2>
    <form method="POST" style="max-width: 800px; margin-top: 30px;">
        <div class="form-grid">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" placeholder="Enter first name" value="<?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Last Name</label>
                <input type="text" placeholder="Enter last name" value="Admin" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" placeholder="Enter email" value="<?php echo htmlspecialchars($_SESSION['admin_email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" placeholder="Enter phone number" value="+977 1-4123456">
            </div>

            <div class="form-group">
                <label>Department</label>
                <select>
                    <option selected>Administration</option>
                    <option>Management</option>
                    <option>Operations</option>
                </select>
            </div>

            <div class="form-group">
                <label>Designation</label>
                <input type="text" placeholder="Enter designation" value="Hospital Administrator">
            </div>
        </div>

        <div class="form-group">
            <label>Bio</label>
            <textarea placeholder="Write a short bio about yourself">Experienced hospital administrator with expertise in healthcare management</textarea>
        </div>

        <div class="form-group">
            <label>Address</label>
            <input type="text" placeholder="Enter your address">
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>City</label>
                <input type="text" placeholder="Enter city" value="Kathmandu">
            </div>

            <div class="form-group">
                <label>District</label>
                <select>
                    <option selected>Kathmandu</option>
                    <option>Bhaktapur</option>
                    <option>Lalitpur</option>
                </select>
            </div>

            <div class="form-group">
                <label>Country</label>
                <input type="text" placeholder="Enter country" value="Nepal">
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            <a href="view.php" class="btn btn-secondary">❌ Cancel</a>
        </div>
    </form>
</div>

<!-- Hospital Assignment Section -->
<div class="section">
    <h2>Hospital Assignment</h2>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
        <div>
            <label style="font-weight: 600; color: #2c3e50; font-size: 13px; text-transform: uppercase;">Assigned Hospital</label>
            <div style="margin-top: 10px; padding: 16px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 6px; border-left: 4px solid #667eea;">
                <div style="font-size: 16px; font-weight: 600; color: #2c3e50;">Bhaktapur Hospital</div>
                <div style="font-size: 12px; color: #8fa8ba; margin-top: 5px;">ID: HOS-2025-0001</div>
                <div style="font-size: 12px; color: #8fa8ba; margin-top: 3px;">Location: Bhaktapur, Kathmandu Valley</div>
            </div>
        </div>

        <div>
            <label style="font-weight: 600; color: #2c3e50; font-size: 13px; text-transform: uppercase;">Administrator Status</label>
            <div style="margin-top: 10px; padding: 16px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 6px; border-left: 4px solid #27ae60;">
                <div style="font-size: 16px; font-weight: 600; color: #2c3e50;">Primary Administrator</div>
                <div style="font-size: 12px; color: #8fa8ba; margin-top: 5px;">Full access to all hospital modules</div>
                <div style="margin-top: 10px;"><span class="status-badge active">Active</span></div>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>
