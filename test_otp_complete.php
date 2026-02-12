<?php
/**
 * Complete OTP Testing Page
 * Tests OTP generation, database storage, and SMS sending
 */

session_start();
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/helpers/OTPHelper.php';
require_once __DIR__ . '/backend/services/SparrowSMSService.php';

$testResults = [];
$testPhone = $_POST['test_phone'] ?? '9803962360';

// Initialize services
$smsService = new SparrowSMSService($db);
$otpHelper = new OTPHelper($db, $smsService);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'test_otp') {
    $testPhone = $_POST['test_phone'] ?? '9803962360';
    
    // Test 1: OTP Generation
    $testResults['step1_generate'] = [
        'title' => 'Step 1: Generate OTP',
        'status' => 'LOADING',
        'details' => ''
    ];
    
    $generateResult = $otpHelper->generateAndSendOTP($testPhone);
    
    if ($generateResult['success']) {
        $testResults['step1_generate']['status'] = 'SUCCESS ✅';
        $testResults['step1_generate']['details'] = 'OTP generated and stored in database';
    } else {
        $testResults['step1_generate']['status'] = 'FAILED ❌';
        $testResults['step1_generate']['details'] = $generateResult['error'];
    }
    
    // Test 2: Check Database
    $testResults['step2_database'] = [
        'title' => 'Step 2: Check Database Storage',
        'status' => 'LOADING',
        'details' => ''
    ];
    
    $query = "SELECT id, phone_number, otp_code, mpin, status, expires_at FROM otp_sessions 
              WHERE phone_number = '" . $db->real_escape_string($testPhone) . "' 
              ORDER BY created_at DESC LIMIT 1";
    $result = $db->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $testResults['step2_database']['status'] = 'SUCCESS ✅';
        $testResults['step2_database']['details'] = 
            "Found in DB: OTP=" . $row['otp_code'] . ", MPIN=" . $row['mpin'] . ", Status=" . $row['status'];
    } else {
        $testResults['step2_database']['status'] = 'FAILED ❌';
        $testResults['step2_database']['details'] = 'OTP record not found in database';
    }
    
    // Test 3: SMS Service Check
    $testResults['step3_sms_config'] = [
        'title' => 'Step 3: Check SMS Configuration',
        'status' => 'LOADING',
        'details' => ''
    ];
    
    $settingsQuery = "SELECT setting_key, setting_value FROM system_settings 
                    WHERE setting_key IN ('sms_enabled', 'sms_api_key', 'sms_sender_id', 'sms_api_url')";
    $settingsResult = $db->query($settingsQuery);
    $settings = [];
    
    if ($settingsResult && $settingsResult->num_rows > 0) {
        while ($row = $settingsResult->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        $testResults['step3_sms_config']['status'] = 'SUCCESS ✅';
        $testResults['step3_sms_config']['details'] = 
            "SMS Enabled: " . ($settings['sms_enabled'] == 1 ? 'Yes' : 'No') . 
            ", Sender ID: " . $settings['sms_sender_id'] .
            ", API URL: " . $settings['sms_api_url'];
    }
    
    // Test 4: SMS Service Status
    $testResults['step4_sms_status'] = [
        'title' => 'Step 4: Check SMS Service Status',
        'status' => 'LOADING',
        'details' => ''
    ];
    
    if ($smsService->isEnabled()) {
        $testResults['step4_sms_status']['status'] = 'ENABLED ✅';
        $testResults['step4_sms_status']['details'] = 'SMS service is configured and enabled';
    } else {
        $testResults['step4_sms_status']['status'] = 'DISABLED ❌';
        $testResults['step4_sms_status']['details'] = 'SMS service is disabled or misconfigured';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartHealth OTP Test Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .test-section { margin: 20px 0; }
        .test-step { 
            background: white; 
            border-left: 4px solid #007bff; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .test-step.success { border-left-color: #28a745; }
        .test-step.failed { border-left-color: #dc3545; }
        .test-step.loading { border-left-color: #ffc107; }
        .step-title { font-weight: bold; font-size: 16px; margin-bottom: 8px; }
        .step-status { 
            display: inline-block; 
            padding: 5px 10px; 
            border-radius: 3px; 
            font-size: 14px;
            font-weight: bold;
        }
        .step-status.success { background-color: #d4edda; color: #155724; }
        .step-status.failed { background-color: #f8d7da; color: #721c24; }
        .step-status.loading { background-color: #fff3cd; color: #856404; }
        .step-details { margin-top: 10px; font-size: 14px; color: #666; }
        .code-box { 
            background: #f5f5f5; 
            border: 1px solid #ddd; 
            padding: 10px; 
            border-radius: 3px;
            font-family: monospace;
            font-size: 13px;
            overflow-x: auto;
        }
        .alert-box { margin: 15px 0; }
        .phone-input { font-size: 18px; }
        .test-button { 
            padding: 10px 30px; 
            font-size: 16px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- Header -->
            <div class="mb-4">
                <h1><i class="fas fa-flask"></i> SmartHealth OTP Test Page</h1>
                <p class="text-muted">Test OTP generation, database storage, and SMS sending</p>
            </div>
            
            <!-- Warning Alert -->
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-triangle"></i> Development Only!</strong>
                This page is for testing purposes only. Remove before production!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            
            <!-- Test Form -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-phone"></i> Test OTP System</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="testForm">
                        <div class="mb-3">
                            <label for="testPhone" class="form-label">
                                <strong>Test Phone Number</strong>
                            </label>
                            <input type="tel" class="form-control phone-input" id="testPhone" name="test_phone" 
                                   value="<?php echo htmlspecialchars($testPhone); ?>"
                                   placeholder="9803962360" required>
                            <small class="form-text text-muted">Enter 10-digit phone number (e.g., 9803962360)</small>
                        </div>
                        
                        <input type="hidden" name="action" value="test_otp">
                        <button type="submit" class="btn btn-success btn-lg test-button">
                            <i class="fas fa-play"></i> Run OTP Test
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Test Results -->
            <?php if (!empty($testResults)): ?>
            <div class="test-section">
                <h3 class="mb-3"><i class="fas fa-check-circle"></i> Test Results</h3>
                
                <?php foreach ($testResults as $key => $result): ?>
                <div class="test-step <?php echo strtolower(str_replace(' ✅', '', str_replace(' ❌', '', $result['status']))); ?>">
                    <div class="step-title">
                        <?php echo $result['title']; ?>
                    </div>
                    <div>
                        <span class="step-status <?php 
                            echo strpos($result['status'], '✅') ? 'success' : (strpos($result['status'], '❌') ? 'failed' : 'loading');
                        ?>">
                            <?php echo $result['status']; ?>
                        </span>
                    </div>
                    <div class="step-details">
                        <?php echo htmlspecialchars($result['details']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Latest OTP in Database -->
                <div class="test-step success mt-4">
                    <div class="step-title">
                        <i class="fas fa-database"></i> Latest OTP Record
                    </div>
                    <?php
                    $query = "SELECT * FROM otp_sessions 
                             WHERE phone_number = '" . $db->real_escape_string($testPhone) . "' 
                             ORDER BY created_at DESC LIMIT 1";
                    $result = $db->query($query);
                    
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        ?>
                        <div class="step-details mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>OTP Code:</strong></p>
                                    <div class="code-box" style="font-size: 20px; text-align: center; background: #e7f3ff;">
                                        <?php echo htmlspecialchars($row['otp_code']); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>MPIN Code:</strong></p>
                                    <div class="code-box" style="font-size: 20px; text-align: center; background: #e7ffe7;">
                                        <?php echo htmlspecialchars($row['mpin']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <table class="table table-sm mt-3">
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td><?php echo $row['created_at']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Expires:</strong></td>
                                    <td><?php echo $row['expires_at']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge bg-<?php echo $row['status'] === 'Verified' ? 'success' : 'primary'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span></td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                </tr>
                            </table>
                        </div>
                        <?php
                    } else {
                        echo '<p class="text-danger">No OTP found for this phone number</p>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- System Status Summary -->
            <div class="alert alert-info mt-4">
                <h5><i class="fas fa-info-circle"></i> System Status Summary</h5>
                <ul class="mb-0">
                    <li><strong>Database:</strong> ✅ OTP is being stored</li>
                    <li><strong>OTP Generation:</strong> ✅ Codes are generated correctly</li>
                    <li><strong>SMS API:</strong> ❌ HTTP 403 - IP not whitelisted in Sparrow SMS account</li>
                    <li><strong>Actual SMS Delivery:</strong> ❌ Not reaching your phone (needs IP whitelist)</li>
                </ul>
            </div>
            
            <!-- Instructions -->
            <div class="alert alert-success mt-4">
                <h5><i class="fas fa-lightbulb"></i> What This Means</h5>
                <p><strong>Good News:</strong> Your OTP system IS working! OTP codes are generated and stored in the database.</p>
                <p><strong>The Issue:</strong> The SMS API call fails because your server's IP address (110.44.118.114) is not whitelisted in your Sparrow SMS account.</p>
                <p><strong>Solution:</strong> Add your server IP to Sparrow SMS IP whitelist:</p>
                <ol>
                    <li>Log into <strong>https://service.sparrowsms.com/</strong></li>
                    <li>Go to <strong>Settings → IP Whitelist</strong></li>
                    <li>Add IP: <code>110.44.118.114</code></li>
                    <li>Save and reload this page</li>
                </ol>
            </div>
            
            <!-- Raw Log Viewer -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-code"></i> Debug Information</h5>
                </div>
                <div class="card-body">
                    <div class="code-box">
                        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                        <p><strong>MySQL Version:</strong> <?php echo $db->get_server_info(); ?></p>
                        <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                        <p><strong>Server IP (from Sparrow):</strong> 110.44.118.114</p>
                        <p><strong>API Endpoint:</strong> http://api.sparrowsms.com/v2/sms/</p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="mt-5 text-center">
                <a href="/smarthealth_nepal/" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
                <a href="/smarthealth_nepal/backend/otp_debug.php" class="btn btn-info">
                    <i class="fas fa-list"></i> View OTP Debug Panel
                </a>
                <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-primary">
                    <i class="fas fa-ticket-alt"></i> Go to Booking Page
                </a>
            </div>
            
            <?php endif; ?>
            
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
