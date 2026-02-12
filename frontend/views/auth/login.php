<?php
/**
 * Login / Authentication Page - MPIN Based
 * Users enter phone number and MPIN to login
 * Forgot MPIN option generates new MPIN              even mpin correct enter login is sucess it says 
 * invalid mpin 
 */

// Set language if switching
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load backend init
require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../../backend/helpers/OTPHelper.php';
require_once __DIR__ . '/../../../backend/services/SparrowSMSService.php';

$authController = new AuthController($db);

// Handle language preference
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Check if user is logged in
if (isset($_SESSION['user_id']) && $_GET['action'] !== 'logout') {
    header('Location: /smarthealth_nepal/frontend/views/home/');
    exit;
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    $_SESSION = [];
    header('Location: /smarthealth_nepal/frontend/views/home/');
    exit;
}

// Initialize response variables
$step = isset($_POST['step']) ? $_POST['step'] : 'phone';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$response = null;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login'; // 'login' or 'forgot'

// ============================================
// MODE 1: LOGIN WITH PHONE & MPIN
// ============================================

// Handle phone submission (Step 1: Enter phone number)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'phone' && $mode === 'login') {
    $phone = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? '');
    
    if (empty($phone)) {
        $response = [
            'success' => false,
            'message' => 'Please enter your phone number'
        ];
    } else {
        // Validate phone format
        require_once __DIR__ . '/../../../backend/helpers/SMSHelper.php';
        $smsHelper = new SMSHelper();
        
        if (!$smsHelper->isValidPhone($phone)) {
            $response = [
                'success' => false,
                'message' => 'Invalid phone number format'
            ];
        } else {
            $phone = $smsHelper->formatPhone($phone);
            
            // Check if user exists
            $phoneEscaped = $db->real_escape_string($phone);
            $userQuery = "SELECT id, full_name FROM users WHERE phone_number = '$phoneEscaped' LIMIT 1";
            $userResult = $db->query($userQuery);
            
            if ($userResult && $userResult->num_rows > 0) {
                // User exists, proceed to MPIN entry
                $step = 'mpin';
                $response = [
                    'success' => true,
                    'message' => 'Enter your MPIN to login'
                ];
            } else {
                // User doesn't exist - they need to book a token first
                $response = [
                    'success' => false,
                    'message' => 'No account found. Please book a token first to create your account.'
                ];
            }
        }
    }
}

// Handle MPIN verification (Step 2: Enter MPIN & Login)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'mpin' && $mode === 'login') {
    $phone = $_POST['phone'] ?? '';
    $mpin = $_POST['mpin'] ?? '';
    
    if (empty($phone) || empty($mpin)) {
        $response = [
            'success' => false,
            'message' => 'Please enter both phone number and MPIN'
        ];
    } else {
        // Verify MPIN
        $phoneEscaped = $db->real_escape_string($phone);
        $mpinEscaped = $db->real_escape_string($mpin);
        
        $loginQuery = "SELECT id, full_name, phone_number FROM users 
                      WHERE phone_number = '$phoneEscaped' 
                      AND (mpin = '$mpinEscaped' OR old_mpin = '$mpinEscaped')
                      LIMIT 1";
        
        $loginResult = $db->query($loginQuery);
        
        if ($loginResult && $loginResult->num_rows > 0) {
            // MPIN is correct - Log user in
            $user = $loginResult->fetch_assoc();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['is_logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['phone_number'] = $user['phone_number'];
            
            error_log("User logged in via MPIN: " . $user['full_name'] . " | Phone: " . $phone);
            
            // Redirect to home or token booking
            header('Location: /smarthealth_nepal/frontend/views/home/');
            exit;
        } else {
            // Invalid MPIN
            $response = [
                'success' => false,
                'message' => 'Invalid MPIN. Please check and try again.'
            ];
        }
    }
}

// ============================================
// MODE 2: FORGOT MPIN
// ============================================

// Handle forgot MPIN phone entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'phone' && $mode === 'forgot') {
    $phone = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? '');
    
    if (empty($phone)) {
        $response = [
            'success' => false,
            'message' => 'Please enter your phone number'
        ];
    } else {
        // Validate phone format
        require_once __DIR__ . '/../../../backend/helpers/SMSHelper.php';
        $smsHelper = new SMSHelper();
        
        if (!$smsHelper->isValidPhone($phone)) {
            $response = [
                'success' => false,
                'message' => 'Invalid phone number format'
            ];
        } else {
            $phone = $smsHelper->formatPhone($phone);
            
            // Check if user exists
            $phoneEscaped = $db->real_escape_string($phone);
            $userQuery = "SELECT id FROM users WHERE phone_number = '$phoneEscaped' LIMIT 1";
            $userResult = $db->query($userQuery);
            
            if ($userResult && $userResult->num_rows > 0) {
                // User exists, proceed with MPIN reset
                $step = 'reset';
                $response = [
                    'success' => true,
                    'message' => 'Click below to generate a new MPIN'
                ];
            } else {
                // User doesn't exist
                $response = [
                    'success' => false,
                    'message' => 'No account found with this phone number.'
                ];
            }
        }
    }
}

// Handle MPIN reset (Generate new MPIN)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'reset' && $mode === 'forgot') {
    $phone = $_POST['phone'] ?? '';
    
    if (empty($phone)) {
        $response = [
            'success' => false,
            'message' => 'Phone number is required'
        ];
    } else {
        // Generate new MPIN using OTPHelper
        $otpHelper = new OTPHelper($db, new SparrowSMSService($db));
        $mpinResult = $otpHelper->forgotMPIN($phone);
        
        if ($mpinResult['success']) {
            $response = [
                'success' => true,
                'message' => 'New MPIN sent to your phone. You can now login with the new MPIN.',
                'show_success' => true
            ];
            $step = 'phone'; // Reset to phone entry
            $mode = 'login'; // Switch back to login mode
            $phone = ''; // Clear phone for next attempt
        } else {
            $response = [
                'success' => false,
                'message' => $mpinResult['error'] ?? 'Failed to generate new MPIN'
            ];
        }
    }
}

$pageTitle = 'Login - SmartHealth Nepal';
$activePage = 'login';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sign-in-alt"></i>
                    <?php echo $mode === 'forgot' ? 'Forgot MPIN' : 'Login'; ?>
                </h5>
            </div>
            <div class="card-body p-5">
                
                <?php if ($response && !$response['success']): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $response['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php elseif ($response && $response['success'] && isset($response['show_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?php echo $response['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php elseif ($response && $response['success']): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <?php echo $response['message']; ?>
                </div>
                <?php endif; ?>
                
                <!-- ====================== STEP 1: PHONE ENTRY ====================== -->
                <?php if ($step === 'phone'): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="phone">
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone"></i> Phone Number
                        </label>
                        <input type="text" class="form-control form-control-lg" id="phone" name="phone" 
                               placeholder="98xxxxxxxx or +977xxxxxxxxxx" required inputmode="numeric"
                               value="<?php echo htmlspecialchars($phone); ?>">
                        <small class="text-muted">
                            Nepali mobile number (10 digits)
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-arrow-right"></i> 
                        <?php echo $mode === 'forgot' ? 'Verify Phone' : 'Continue to MPIN'; ?>
                    </button>
                </form>
                
                <hr>
                
                <?php if ($mode === 'login'): ?>
                <div class="text-center">
                    <p class="text-muted">Don't have an account?</p>
                    <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus-circle"></i> Book a Token
                    </a>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <p class="text-muted">Forgot your MPIN?</p>
                    <a href="/smarthealth_nepal/frontend/views/auth/login.php?mode=forgot" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-key"></i> Reset MPIN
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center">
                    <a href="/smarthealth_nepal/frontend/views/auth/login.php?mode=login" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- ====================== STEP 2: MPIN ENTRY ====================== -->
                <?php elseif ($step === 'mpin'): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="mpin">
                    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                    
                    <div class="mb-3">
                        <p class="text-center text-muted">
                            <strong><?php echo htmlspecialchars($phone); ?></strong>
                        </p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="mpin" class="form-label">
                            <i class="fas fa-key"></i> MPIN
                        </label>
                        <input type="password" class="form-control form-control-lg text-center" id="mpin" name="mpin" 
                               placeholder="0000" maxlength="4" required inputmode="numeric"
                               style="letter-spacing: 20px; font-size: 24px;">
                        <small class="text-muted">
                            4-digit MPIN sent to your phone during token booking
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <hr>
                
                <form method="POST">
                    <input type="hidden" name="step" value="phone">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <small><a href="/smarthealth_nepal/frontend/views/auth/login.php?mode=forgot">Forgot MPIN?</a></small>
                </div>
                
                <!-- ====================== STEP 3: FORGOT MPIN RESET ====================== -->
                <?php elseif ($step === 'reset'): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="reset">
                    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Reset MPIN for:</strong><br>
                        <?php echo htmlspecialchars($phone); ?>
                    </div>
                    
                    <div class="text-center mb-4">
                        <p class="text-muted">
                            A new MPIN will be generated and sent to your phone via SMS.<br>
                            Your old MPIN will be kept as backup.
                        </p>
                    </div>
                    
                    <button type="submit" class="btn btn-danger btn-lg w-100">
                        <i class="fas fa-refresh"></i> Generate New MPIN
                    </button>
                </form>
                
                <hr>
                
                <form method="POST" action="/smarthealth_nepal/frontend/views/auth/login.php?mode=login">
                    <input type="hidden" name="step" value="phone">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </button>
                </form>
                
                <?php endif; ?>
                
            </div>
            
            <div class="card-footer text-center text-muted">
                <small>
                    SmartHealth Nepal - Sustainable Digital Healthcare
                </small>
            </div>
        </div>
    </div>
</div>

<script>
// Format phone number input - only accept digits
document.getElementById('phone') && document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '').slice(0, 10);
    e.target.value = value;
});

// Format MPIN input - only accept 4 digits
document.getElementById('mpin') && document.getElementById('mpin').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '').slice(0, 4);
    e.target.value = value;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php';?>
