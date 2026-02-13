<?php
/**
 * About SmartHealth
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set page variables
$pageTitle = 'About SmartHealth Nepal';
$activePage = '';

// Include header
include 'layouts/header.php';
?>

<div class="section">
    <div style="text-align: center; padding: 40px 20px;">
        <h1>SmartHealth Nepal 🏥</h1>
        <p style="color: #8fa8ba; font-size: 16px; max-width: 600px; margin: 20px auto;">
            Advanced Hospital Token Management & Healthcare Queue System
        </p>
    </div>
</div>

<!-- About Section -->
<div class="section">
    <h2>About Us</h2>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; align-items: center;">
        <div>
            <p style="color: #4b5563; line-height: 1.8; margin-bottom: 15px;">
                SmartHealth Nepal is a comprehensive hospital management platform designed to streamline token management, patient queuing, and healthcare workflows across Nepal's healthcare institutions.
            </p>
            <p style="color: #4b5563; line-height: 1.8; margin-bottom: 15px;">
                Our platform empowers hospitals to:
            </p>
            <ul style="color: #4b5563; line-height: 1.8; margin-left: 20px;">
                <li>✓ Manage token generation and tracking</li>
                <li>✓ Optimize patient queues automatically</li>
                <li>✓ Handle emergency cases efficiently</li>
                <li>✓ Generate comprehensive analytics and reports</li>
                <li>✓ Improve patient satisfaction and experience</li>
                <li>✓ Reduce wait times significantly</li>
            </ul>
        </div>

        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; border-radius: 12px; color: white; text-align: center;">
            <div style="font-size: 48px; font-weight: 700; margin-bottom: 20px;">100+</div>
            <div style="font-size: 18px; margin-bottom: 30px;">Hospitals Across Nepal</div>
            <hr style="opacity: 0.3; margin: 20px 0;">
            <div style="font-size: 36px; font-weight: 700; margin-bottom: 20px;">50K+</div>
            <div style="font-size: 18px;">Daily Patients Managed</div>
        </div>
    </div>
</div>

<!-- Key Features -->
<div class="section">
    <h2>Key Features</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 25px; border-radius: 8px; border-left: 4px solid #667eea;">
            <h4 style="color: #2c3e50; margin-bottom: 10px;">🎫 Token Management</h4>
            <p style="color: #8fa8ba; font-size: 13px;">Generate, track, and manage patient tokens efficiently</p>
        </div>

        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 25px; border-radius: 8px; border-left: 4px solid #27ae60;">
            <h4 style="color: #2c3e50; margin-bottom: 10px;">📱 Multi-Channel Booking</h4>
            <p style="color: #8fa8ba; font-size: 13px;">SMS, App, and web-based booking systems</p>
        </div>

        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 25px; border-radius: 8px; border-left: 4px solid #e74c3c;">
            <h4 style="color: #2c3e50; margin-bottom: 10px;">🚨 Emergency Queue</h4>
            <p style="color: #8fa8ba; font-size: 13px;">Handle emergency cases with priority management</p>
        </div>

        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 25px; border-radius: 8px; border-left: 4px solid #3498db;">
            <h4 style="color: #2c3e50; margin-bottom: 10px;">📊 Analytics & Reports</h4>
            <p style="color: #8fa8ba; font-size: 13px;">Comprehensive insights and performance metrics</p>
        </div>

        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 25px; border-radius: 8px; border-left: 4px solid #f39c12;">
            <h4 style="color: #2c3e50; margin-bottom: 10px;">👥 Staff Management</h4>
            <p style="color: #8fa8ba; font-size: 13px;">Organize and manage healthcare professionals</p>
        </div>

        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 25px; border-radius: 8px; border-left: 4px solid #16a085;">
            <h4 style="color: #2c3e50; margin-bottom: 10px;">🔒 Security First</h4>
            <p style="color: #8fa8ba; font-size: 13px;">Enterprise-grade security and data protection</p>
        </div>
    </div>
</div>

<!-- Version Info -->
<div class="section">
    <h2>Product Information</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px;">
            <label style="font-size: 12px; color: #8fa8ba; font-weight: 600; text-transform: uppercase;">Current Version</label>
            <div style="font-size: 24px; color: #2c3e50; font-weight: 700; margin-top: 5px;">2.0.0</div>
        </div>

        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px;">
            <label style="font-size: 12px; color: #8fa8ba; font-weight: 600; text-transform: uppercase;">Last Updated</label>
            <div style="font-size: 16px; color: #2c3e50; font-weight: 600; margin-top: 5px;">February 13, 2026</div>
        </div>

        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px;">
            <label style="font-size: 12px; color: #8fa8ba; font-weight: 600; text-transform: uppercase;">Support</label>
            <div style="font-size: 14px; color: #2c3e50; font-weight: 600; margin-top: 5px;"><a href="mailto:support@smarthealth.com" style="color: #667eea;">support@smarthealth.com</a></div>
        </div>
    </div>
</div>

<!-- Team -->
<div class="section">
    <h2>Our Team</h2>
    <p style="color: #8fa8ba; margin-bottom: 25px;">Dedicated professionals committed to improving healthcare delivery in Nepal</p>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
        <div style="text-align: center; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 25px; border-radius: 8px;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 36px; font-weight: 700;">RC</div>
            <h4 style="color: #2c3e50; margin-bottom: 5px;">Rajesh Chaudhary</h4>
            <p style="color: #8fa8ba; font-size: 12px;">Founder & CEO</p>
        </div>

        <div style="text-align: center; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 25px; border-radius: 8px;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #27ae60 0%, #229954 100%); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 36px; font-weight: 700;">PS</div>
            <h4 style="color: #2c3e50; margin-bottom: 5px;">Priya Sharma</h4>
            <p style="color: #8fa8ba; font-size: 12px;">Lead Developer</p>
        </div>

        <div style="text-align: center; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 25px; border-radius: 8px;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #e74c3c 0%, #d63031 100%); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 36px; font-weight: 700;">AK</div>
            <h4 style="color: #2c3e50; margin-bottom: 5px;">Anish Kumar</h4>
            <p style="color: #8fa8ba; font-size: 12px;">Product Manager</p>
        </div>
    </div>
</div>

<!-- Contact -->
<div class="section" style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <h2 style="color: white; margin-bottom: 20px;">Get In Touch</h2>
    <p style="font-size: 16px; margin-bottom: 25px; opacity: 0.9;">Have questions or feedback? We'd love to hear from you!</p>
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="mailto:info@smarthealth.com" class="btn btn-secondary">📧 Email Us</a>
        <a href="tel:+977-1412-3456" class="btn btn-secondary">📞 Call Us</a>
        <button class="btn btn-secondary">💬 Send Message</button>
    </div>
</div>

<?php include 'layouts/footer.php'; ?>
