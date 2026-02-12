<?php
/**
 * Login / Authentication Page
 */

// Set language if switching
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load backend init
require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';

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

// Handle phone submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'phone') {
    $phone = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? '');
    $result = $authController->sendOTP($phone);
    
    if ($result['success']) {
        $step = 'otp';
        $response = $result;
    } else {
        $response = $result;
    }
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'otp') {
    $phone = $_POST['phone'] ?? '';
    $otp = $_POST['otp'] ?? '';
    
    $result = $authController->verifyOTP($phone, $otp);
    
    if ($result['success']) {
        // Redirect to home or token booking
        header('Location: /smarthealth_nepal/frontend/views/token/book.php');
        exit;
    } else {
        $response = $result;
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
                    <?php echo $lang['login'] ?? 'Login'; ?>
                </h5>
            </div>
            <div class="card-body p-5">
                
                <?php if ($response && !$response['success']): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $response['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($response && isset($response['debug_otp'])): ?>
                <div class="alert alert-warning">
                    <strong><?php echo $lang['info'] ?? 'Debug OTP'; ?>:</strong> <?php echo $response['debug_otp']; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($step === 'phone'): ?>
                <!-- Phone Input Step -->
                <form method="POST">
                    <input type="hidden" name="step" value="phone">
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            <i class="fas fa-phone"></i> <?php echo $lang['phone_number'] ?? 'Phone Number'; ?>
                        </label>
                        <input type="text" class="form-control form-control-lg" id="phone" name="phone" 
                               placeholder="98xxxxxxxx or +977xxxxxxxxxx" required inputmode="numeric">
                        <small class="text-muted">
                            <?php echo 'Enter your Nepali mobile number'; ?>
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-arrow-right"></i> <?php echo $lang['send_otp'] ?? 'Send OTP'; ?>
                    </button>
                </form>
                
                <hr>
                
                <div class="alert alert-info">
                    <strong><?php echo $lang['info'] ?? 'Info'; ?>:</strong>
                    <?php echo 'First-time users will have an account created automatically.'; ?>
                </div>
                
                <?php elseif ($step === 'otp'): ?>
                <!-- OTP Verification Step -->
                <form method="POST">
                    <input type="hidden" name="step" value="otp">
                    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                    
                    <div class="mb-3">
                        <p class="text-center text-muted">
                            <?php echo $lang['verification_code_sent'] ?? 'OTP sent to'; ?><br>
                            <strong><?php echo htmlspecialchars($phone); ?></strong>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="otp" class="form-label">
                            <i class="fas fa-key"></i> <?php echo $lang['otp'] ?? 'OTP'; ?>
                        </label>
                        <input type="text" class="form-control form-control-lg text-center" id="otp" name="otp" 
                               placeholder="000000" maxlength="6" required inputmode="numeric">
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-check"></i> <?php echo $lang['verify'] ?? 'Verify'; ?>
                    </button>
                </form>
                
                <hr>
                
                <form method="POST">
                    <input type="hidden" name="step" value="phone">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> <?php echo $lang['back'] ?? 'Back'; ?>
                    </button>
                </form>
                
                <?php endif; ?>
            </div>
            
            <div class="card-footer text-center text-muted">
                <small>
                    <?php echo 'SmartHealth Nepal - Sustainable Digital Healthcare'; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php';?>
