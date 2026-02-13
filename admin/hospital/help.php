<?php
/**
 * Help & Support
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set page variables
$pageTitle = 'Help & Support';
$activePage = '';

// Include header
include 'layouts/header.php';
?>

<div class="section">
    <h1>Help & Support Center</h1>
    <p style="color: #8fa8ba; margin-bottom: 30px;">Find answers to common questions and get help with SmartHealth Nepal</p>
</div>

<!-- Search Box -->
<div class="section">
    <input type="text" placeholder="🔍 Search for help articles..." style="width: 100%; padding: 16px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
</div>

<!-- FAQ Categories -->
<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
    <div class="stat-card info">
        <h3>❓ FAQ</h3>
        <div class="number" style="font-size: 28px; text-align: center;">24</div>
        <div class="subtitle" style="text-align: center;">Frequently Asked Questions</div>
    </div>
    <div class="stat-card warning">
        <h3>🆘 Tutorials</h3>
        <div class="number" style="font-size: 28px; text-align: center;">15</div>
        <div class="subtitle" style="text-align: center;">Video & Text Guides</div>
    </div>
    <div class="stat-card success">
        <h3>📞 Contact</h3>
        <div class="number" style="font-size: 28px; text-align: center;">3</div>
        <div class="subtitle" style="text-align: center;">Support Channels</div>
    </div>
    <div class="stat-card">
        <h3>📚 Documentation</h3>
        <div class="number" style="font-size: 28px; text-align: center;">12</div>
        <div class="subtitle" style="text-align: center;">Technical Docs</div>
    </div>
</div>

<!-- FAQ Section -->
<div class="section">
    <h2>Frequently Asked Questions</h2>
    <div style="margin-top: 20px;">
        <details style="margin-bottom: 15px;">
            <summary style="padding: 15px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 6px; cursor: pointer; font-weight: 600; color: #2c3e50;">
                ➕ How do I create a new token?
            </summary>
            <div style="padding: 15px 15px 15px 40px; background: #f9fafb; border-radius: 6px; margin-top: 5px;">
                <p style="color: #4b5563; line-height: 1.6;">
                    To create a new token:<br><br>
                    1. Navigate to "Manage Tokens" from the sidebar<br>
                    2. Click the "+ Add Token" button<br>
                    3. Select the department and enter patient details<br>
                    4. Choose the service and time slot<br>
                    5. Click "Create Token" to confirm
                </p>
            </div>
        </details>

        <details style="margin-bottom: 15px;">
            <summary style="padding: 15px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 6px; cursor: pointer; font-weight: 600; color: #2c3e50;">
                ➕ How do I manage staff members?
            </summary>
            <div style="padding: 15px 15px 15px 40px; background: #f9fafb; border-radius: 6px; margin-top: 5px;">
                <p style="color: #4b5563; line-height: 1.6;">
                    To manage staff:<br><br>
                    1. Go to "Staff Management" section<br>
                    2. Add employees with roles and permissions<br>
                    3. Update their schedules and departments<br>
                    4. Monitor their performance and ratings
                </p>
            </div>
        </details>

        <details style="margin-bottom: 15px;">
            <summary style="padding: 15px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 6px; cursor: pointer; font-weight: 600; color: #2c3e50;">
                ➕ How do I view hospital reports?
            </summary>
            <div style="padding: 15px 15px 15px 40px; background: #f9fafb; border-radius: 6px; margin-top: 5px;">
                <p style="color: #4b5563; line-height: 1.6;">
                    Reports can be accessed from:<br><br>
                    1. Click "Reports" in the sidebar<br>
                    2. Select date range and department filters<br>
                    3. View various analytics and statistics<br>
                    4. Export as PDF, Excel, or email
                </p>
            </div>
        </details>

        <details style="margin-bottom: 15px;">
            <summary style="padding: 15px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-radius: 6px; cursor: pointer; font-weight: 600; color: #2c3e50;">
                ➕ How do I reset my password?
            </summary>
            <div style="padding: 15px 15px 15px 40px; background: #f9fafb; border-radius: 6px; margin-top: 5px;">
                <p style="color: #4b5563; line-height: 1.6;">
                    Click on your profile icon → Change Password and follow the steps to update your password securely.
                </p>
            </div>
        </details>
    </div>
</div>

<!-- Contact Support -->
<div class="section">
    <h2>Need Additional Help?</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); padding: 20px; border-radius: 6px; border-left: 4px solid #17a2b8;">
            <h4 style="color: #0c5460; margin-bottom: 10px;">📞 Call Support</h4>
            <p style="color: #0c5460; font-size: 13px; margin-bottom: 10px;">Speak directly with our support team</p>
            <a href="tel:+977-1412-3456" style="color: #0c5460; font-weight: 600;">+977-1412-3456</a>
        </div>

        <div style="background: linear-gradient(135deg, #d1e7dd 0%, #badbcc 100%); padding: 20px; border-radius: 6px; border-left: 4px solid #198754;">
            <h4 style="color: #0a3622; margin-bottom: 10px;">📧 Email Support</h4>
            <p style="color: #0a3622; font-size: 13px; margin-bottom: 10px;">Send us an email with your query</p>
            <a href="mailto:support@smarthealth.com" style="color: #0a3622; font-weight: 600;">support@smarthealth.com</a>
        </div>

        <div style="background: linear-gradient(135deg, #ffeaa7 0%, #ffde9a 100%); padding: 20px; border-radius: 6px; border-left: 4px solid #f39c12;">
            <h4 style="color: #856404; margin-bottom: 10px;">💬 Live Chat</h4>
            <p style="color: #856404; font-size: 13px; margin-bottom: 10px;">Chat with our support team instantly</p>
            <button class="btn btn-small" style="background: #f39c12; color: white; border: none;">Start Chat</button>
        </div>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
