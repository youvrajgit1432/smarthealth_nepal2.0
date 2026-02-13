<?php
/**
 * Notification Settings
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set page variables
$pageTitle = 'Notification Settings';
$activePage = '';

// Include header
include '../layouts/header.php';
?>

<div class="section">
    <h2>Notification Preferences</h2>

    <form style="max-width: 800px; margin-top: 30px;">
        <!-- System Notifications -->
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <h4 style="color: #2c3e50; margin-bottom: 15px;">🔔 System Notifications</h4>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                <input type="checkbox" checked id="newTokens">
                <label for="newTokens" style="margin: 0; font-weight: 500;">New Token Assigned</label>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                <input type="checkbox" checked id="emergencyQueue">
                <label for="emergencyQueue" style="margin: 0; font-weight: 500;">Emergency Queue Alerts</label>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                <input type="checkbox" checked id="staffUpdates">
                <label for="staffUpdates" style="margin: 0; font-weight: 500;">Staff Updates</label>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px;">
                <input type="checkbox" id="maintenanceAlerts">
                <label for="maintenanceAlerts" style="margin: 0; font-weight: 500;">System Maintenance Alerts</label>
            </div>
        </div>

        <!-- Email Notifications -->
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <h4 style="color: #2c3e50; margin-bottom: 15px;">📧 Email Notifications</h4>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                <input type="checkbox" checked id="dailyReport">
                <label for="dailyReport" style="margin: 0; font-weight: 500;">Daily Summary Report</label>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                <input type="checkbox" checked id="weeklyReport">
                <label for="weeklyReport" style="margin: 0; font-weight: 500;">Weekly Performance Report</label>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                <input type="checkbox" id="monthlyAnalytics">
                <label for="monthlyAnalytics" style="margin: 0; font-weight: 500;">Monthly Analytics</label>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px;">
                <input type="checkbox" checked id="promotions">
                <label for="promotions" style="margin: 0; font-weight: 500;">Product Updates & Promotions</label>
            </div>
        </div>

        <!-- SMS Notifications -->
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <h4 style="color: #2c3e50; margin-bottom: 15px;">📱 SMS Notifications</h4>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                <input type="checkbox" checked id="smsUrgent">
                <label for="smsUrgent" style="margin: 0; font-weight: 500;">Urgent Alerts Only</label>
            </div>
            <div class="form-group">
                <label style="font-weight: 600; color: #2c3e50;">Phone Number</label>
                <input type="tel" placeholder="+977 1-4123456" value="+977 1-4123456" style="margin-top: 5px;">
            </div>
        </div>

        <!-- Notification Frequency -->
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); padding: 20px; border-radius: 6px; margin-bottom: 20px;">
            <h4 style="color: #2c3e50; margin-bottom: 15px;">⏰ Notification Frequency</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label>Quiet Hours Start Time</label>
                    <input type="time" value="22:00">
                </div>
                <div class="form-group">
                    <label>Quiet Hours End Time</label>
                    <input type="time" value="08:00">
                </div>
            </div>
            <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-top: 15px;">
                <input type="checkbox" checked id="quietHours">
                <label for="quietHours" style="margin: 0; font-weight: 500;">Disable notifications during quiet hours</label>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">💾 Save Preferences</button>
            <button type="reset" class="btn btn-secondary">↻ Reset to Defaults</button>
        </div>
    </form>
</div>

<?php include '../layouts/footer.php'; ?>
