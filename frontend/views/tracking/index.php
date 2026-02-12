<?php
/**
 * Public Token Tracking Page
 * Users can track their token by:
 * - Token Number (6 digits)
 * - Phone Number (10 digits)
 * 
 * Accessible without login
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set language if switching
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load backend
require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/TokenController.php';

// Load language
$lang = [];
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Initialize
$tokenController = new TokenController($db);
$tokenData = null;
$searchMethod = null;
$searchValue = null;
$errorMessage = '';
$successMessage = '';

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchMethod = $_POST['search_method'] ?? null;
    $searchValue = $_POST['search_value'] ?? '';
    
    if (empty($searchValue)) {
        $errorMessage = $lang['search_error_empty'] ?? 'Please enter a token number or phone number';
    } elseif ($searchMethod === 'token') {
        // Search by token number (new format: hospital_id + YYYYMMDD + serial)
        // Example: 1220260212001 = Hospital 12 + 20260212 + 001
        $cleanToken = str_replace('/', '', $searchValue);
        if (!preg_match('/^\d{1,15}$/', $cleanToken)) {
            $errorMessage = $lang['search_error_token_format'] ?? 'Token number must be numeric';
        } else {
            $tokenData = $tokenController->getTokenByTokenNumber(intval($cleanToken));
            if (!$tokenData['success']) {
                $errorMessage = $tokenData['message'] ?? $lang['token_not_found'] ?? 'Token not found';
                $tokenData = null;
            } else {
                $successMessage = $lang['token_found'] ?? 'Token found successfully';
                $tokenData = $tokenData['data'];
            }
        }
    } elseif ($searchMethod === 'phone') {
        // Search by phone number
        if (!preg_match('/^\d{10}$/', str_replace('-', '', $searchValue))) {
            $errorMessage = $lang['search_error_phone_format'] ?? 'Phone number must be 10 digits';
            $tokenData = null;
        } else {
            $tokenData = $tokenController->getTokensByPhoneNumber($searchValue);
            if (!$tokenData['success']) {
                $errorMessage = $tokenData['message'] ?? $lang['no_tokens_found'] ?? 'No tokens found for this phone number';
                $tokenData = null;
            } else {
                $successMessage = sprintf($lang['tokens_found'] ?? '%d token(s) found', count($tokenData['data']));
                $tokenData = $tokenData['data'];
            }
        }
    }
}

$pageTitle = $lang['track_token'] ?? 'Track Token';
$activePage = 'tracking';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4 mb-5">
    <div class="col-lg-8">
        
        <!-- Search Form Card -->
        <div class="card shadow-lg border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-search"></i>
                    <?php echo $lang['track_your_token'] ?? 'Track Your Token'; ?>
                </h5>
            </div>
            
            <div class="card-body p-4">
                <p class="text-muted mb-4">
                    <?php echo $lang['track_description'] ?? 'Enter your token number or phone number to check your queue status'; ?>
                </p>
                
                <!-- Search Method Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="token-tab" data-bs-toggle="tab" 
                                data-bs-target="#token-search" type="button" role="tab">
                            <i class="fas fa-ticket-alt me-2"></i><?php echo $lang['by_token_number'] ?? 'By Token Number'; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="phone-tab" data-bs-toggle="tab" 
                                data-bs-target="#phone-search" type="button" role="tab">
                            <i class="fas fa-phone me-2"></i><?php echo $lang['by_phone_number'] ?? 'By Phone Number'; ?>
                        </button>
                    </li>
                </ul>

                <!-- Error Message -->
                <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Success Message -->
                <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Token Number Search -->
                    <div class="tab-pane fade show active" id="token-search" role="tabpanel">
                        <form method="POST" action="">
                            <input type="hidden" name="search_method" value="token">
                            <div class="mb-3">
                                <label for="tokenInput" class="form-label">
                                    <i class="fas fa-ticket-alt text-primary"></i>
                                    <?php echo $lang['enter_token_number'] ?? 'Enter Token Number'; ?>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="tokenInput" 
                                       name="search_value" placeholder="1220260212001" maxlength="15"
                                       value="<?php echo $searchMethod === 'token' ? htmlspecialchars($searchValue) : ''; ?>">
                                <small class="text-muted d-block mt-2">
                                    <?php echo $lang['token_example'] ?? 'Example: 001, 123, 1220260212001 (1-15 digits)'; ?>
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-search me-2"></i><?php echo $lang['search_token'] ?? 'Search Token'; ?>
                            </button>
                        </form>
                    </div>

                    <!-- Phone Number Search -->
                    <div class="tab-pane fade" id="phone-search" role="tabpanel">
                        <form method="POST" action="">
                            <input type="hidden" name="search_method" value="phone">
                            <div class="mb-3">
                                <label for="phoneInput" class="form-label">
                                    <i class="fas fa-phone text-primary"></i>
                                    <?php echo $lang['enter_phone_number'] ?? 'Enter Phone Number'; ?>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="phoneInput" 
                                       name="search_value" placeholder="9803962360" maxlength="10"
                                       value="<?php echo $searchMethod === 'phone' ? htmlspecialchars($searchValue) : ''; ?>">
                                <small class="text-muted d-block mt-2">
                                    <?php echo $lang['phone_example'] ?? 'Example: 9803962360 (10 digits)'; ?>
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-search me-2"></i><?php echo $lang['search_phone'] ?? 'Find My Tokens'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Token Details Card(s) -->
        <?php if (!empty($tokenData) && empty($errorMessage)): ?>
            <?php 
            // Check if single token or multiple tokens
            $isMultiple = is_array($tokenData) && isset($tokenData[0]);
            $tokens = $isMultiple ? $tokenData : [$tokenData];
            ?>
            
            <?php foreach ($tokens as $token): ?>
            <div class="card shadow-lg mb-4 border-left border-<?php echo getStatusBorderColor($token['status']); ?>">
                <div class="card-header bg-<?php echo getStatusHeaderColor($token['status']); ?> text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-<?php echo getStatusIcon($token['status']); ?> me-2"></i>
                                <?php echo $lang['token'] ?? 'Token'; ?> #<?php echo str_pad($token['token_number'], 3, '0', STR_PAD_LEFT); ?>
                            </h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-white text-dark">
                                <?php echo getStatusLabel($token['status'], $lang); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Token Info Grid -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small mb-1">
                                <i class="fas fa-building text-primary"></i>
                                <?php echo $lang['department'] ?? 'Department'; ?>
                            </h6>
                            <p class="h5"><?php echo htmlspecialchars($token['department_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small mb-1">
                                <i class="fas fa-hospital text-primary"></i>
                                <?php echo $lang['hospital'] ?? 'Hospital'; ?>
                            </h6>
                            <p class="h5"><?php echo htmlspecialchars($token['hospital_name'] ?? 'N/A'); ?></p>
                        </div>
                    </div>

                    <!-- Priority & Booking Date -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small mb-1">
                                <i class="fas fa-exclamation-circle text-primary"></i>
                                <?php echo $lang['priority'] ?? 'Priority'; ?>
                            </h6>
                            <p>
                                <span class="badge bg-<?php echo getPriorityBadgeColor($token['priority'] ?? 'Normal'); ?>">
                                    <?php echo htmlspecialchars($token['priority'] ?? 'Normal'); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small mb-1">
                                <i class="fas fa-calendar text-primary"></i>
                                <?php echo $lang['booked_date'] ?? 'Booked Date'; ?>
                            </h6>
                            <p class="h6">
                                <?php echo date('d M Y, H:i', strtotime($token['created_at'] ?? date('Y-m-d H:i:s'))); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Status Message -->
                    <?php if ($token['status'] === 'Called'): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-bell me-2"></i><strong><?php echo $lang['urgent'] ?? 'URGENT'; ?></strong>
                        <br><?php echo $lang['status_called_message'] ?? 'Your token has been called. Please proceed to the hospital immediately.'; ?>
                    </div>
                    <?php elseif ($token['status'] === 'Active'): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo $lang['estimated_wait'] ?? 'Estimated Wait Time'; ?>: 
                        <strong><?php echo htmlspecialchars($token['estimated_wait_time'] ?? 'N/A'); ?> minutes</strong>
                    </div>
                    <?php elseif ($token['status'] === 'Completed'): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $lang['token_completed'] ?? 'This token has been completed.'; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Additional Details -->
                    <div class="alert alert-light">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small mb-2">
                                    <i class="fas fa-phone"></i> <?php echo $lang['phone_number'] ?? 'Phone'; ?>
                                </h6>
                                <p class="fw-bold">
                                    <?php echo htmlspecialchars($token['phone_number'] ?? 'N/A'); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small mb-2">
                                    <i class="fas fa-user"></i> <?php echo $lang['patient_name'] ?? 'Patient'; ?>
                                </h6>
                                <p class="fw-bold">
                                    <?php echo htmlspecialchars($token['patient_name'] ?? $token['full_name'] ?? 'N/A'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        <?php echo $lang['booked_on'] ?? 'Booked on'; ?>: 
                        <?php echo date('d M Y, H:i', strtotime($token['created_at'] ?? date('Y-m-d H:i:s'))); ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- New Search Button -->
            <div class="text-center mb-4">
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary btn-lg">
                    <i class="fas fa-search me-2"></i><?php echo $lang['search_another'] ?? 'Search Another Token'; ?>
                </a>
            </div>

        <?php endif; ?>

        <!-- Help Section -->
        <?php if (empty($tokenData) && empty($errorMessage)): ?>
        <div class="card bg-light border-0 mt-5">
            <div class="card-body p-4">
                <h5 class="card-title mb-3">
                    <i class="fas fa-question-circle text-primary"></i>
                    <?php echo $lang['tracking_help'] ?? 'How to Track Your Token'; ?>
                </h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-chevron-right text-primary me-2"></i>
                        <?php echo $lang['help_1'] ?? '1. You can find your token number in the confirmation SMS or email'; ?>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-chevron-right text-primary me-2"></i>
                        <?php echo $lang['help_2'] ?? '2. Alternatively, enter your phone number to see all your tokens'; ?>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-chevron-right text-primary me-2"></i>
                        <?php echo $lang['help_3'] ?? '3. Check your status in real-time and estimated wait time'; ?>
                    </li>
                    <li>
                        <i class="fas fa-chevron-right text-primary me-2"></i>
                        <?php echo $lang['help_4'] ?? '4. If your token status is "Called", please proceed to hospital immediately'; ?>
                    </li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-format token input to numbers only
document.getElementById('tokenInput')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').slice(0, 15);
});

// Auto-format phone input to numbers only
document.getElementById('phoneInput')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').slice(0, 10);
});

// Auto-refresh page every 30 seconds if token found and active
<?php if (!empty($tokenData) && is_array($tokens) && !empty($tokens[0]['status']) && $tokens[0]['status'] === 'Active'): ?>
setInterval(function() {
    location.reload();
}, 30000);
<?php endif; ?>
</script>

<?php 
// Helper Functions
function getStatusBorderColor($status) {
    return match($status) {
        'Called' => 'danger',
        'Active' => 'info',
        'Completed' => 'success',
        default => 'secondary'
    };
}

function getStatusHeaderColor($status) {
    return match($status) {
        'Called' => 'danger',
        'Active' => 'info',
        'Completed' => 'success',
        default => 'secondary'
    };
}

function getStatusIcon($status) {
    return match($status) {
        'Called' => 'bell',
        'Active' => 'hourglass-half',
        'Completed' => 'check-circle',
        default => 'question-circle'
    };
}

function getStatusLabel($status, $lang) {
    return match($status) {
        'Called' => $lang['status_called'] ?? 'Called',
        'Active' => $lang['status_active'] ?? 'In Queue',
        'Completed' => $lang['status_completed'] ?? 'Completed',
        default => $status
    };
}

function getPriorityBadgeColor($priority) {
    return match($priority) {
        'Emergency' => 'danger',
        'Priority' => 'warning',
        'Chronic' => 'info',
        'Maternal' => 'success',
        default => 'secondary'
    };
}
?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
