<?php
/**
 * Book Token For Others (Assisted Booking)
 * Allows users to book tokens for family members or others who can't book themselves
 * Works like regular booking but for someone else's phone number
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/TokenController.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../../backend/helpers/OTPHelper.php';
require_once __DIR__ . '/../../../backend/services/SparrowSMSService.php';

$authController = new AuthController($db);
$tokenController = new TokenController($db);

$lang = [];
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Initialize
$currentStep = isset($_POST['step']) ? $_POST['step'] : 'enter_details';
$response = ['success' => null, 'message' => '', 'type' => ''];
$bookingPhone = $_SESSION['assisted_booking_phone'] ?? '';
$bookingName = $_SESSION['assisted_booking_name'] ?? '';

// ============================================
// STEP 1: ENTER PERSON DETAILS (Name & Phone)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentStep === 'enter_details') {
    $personPhone = trim($_POST['person_phone'] ?? '');
    $personName = trim($_POST['person_name'] ?? '');

    if (empty($personPhone)) {
        $response = [
            'success' => false,
            'message' => $lang['enter_phone_required'] ?? 'Please enter the person\'s phone number',
            'type' => 'error'
        ];
    } else {
        require_once __DIR__ . '/../../../backend/helpers/SMSHelper.php';
        $smsHelper = new SMSHelper();
        
        if (!$smsHelper->isValidPhone($personPhone)) {
            $response = [
                'success' => false,
                'message' => $lang['invalid_phone_number'] ?? 'Invalid phone number. Must be 10 digits.',
                'type' => 'error'
            ];
        } else {
            // Format phone number
            require_once __DIR__ . '/../../../backend/helpers/SMSHelper.php';
            $smsHelper = new SMSHelper();
            $personPhone = $smsHelper->formatPhone($personPhone);
            
            // Store in session
            $_SESSION['assisted_booking_phone'] = $personPhone;
            $_SESSION['assisted_booking_name'] = $personName;
            
            // SEND OTP using same method as book.php - Check if one already exists (don't generate multiple)
            $otpResult = $tokenController->sendOTPForBooking($personPhone);
            
            if ($otpResult['success'] || !$otpResult['sms_sent']) {
                $_SESSION['otp_session_id'] = $otpResult['otp_session_id'] ?? null;
                $_SESSION['sms_sent'] = $otpResult['sms_sent'] ?? false;
                $_SESSION['is_existing_otp'] = $otpResult['is_existing'] ?? false;
                
                $message = $otpResult['is_existing'] 
                    ? 'OTP already sent. Please enter it below.'
                    : ($otpResult['sms_sent'] 
                        ? 'OTP sent successfully to ' . $personPhone
                        : 'OTP generated. You can proceed with verification.');
                
                $response = [
                    'success' => true,
                    'message' => $message,
                    'type' => 'success'
                ];
                $currentStep = 'verify_otp';
                $bookingPhone = $personPhone;
                $bookingName = $personName;
            } else {
                $response = [
                    'success' => false,
                    'message' => $otpResult['message'] ?? 'Failed to send OTP',
                    'type' => 'error'
                ];
            }
        }
    }
}

// ============================================
// STEP 2: VERIFY OTP
// ============================================
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentStep === 'verify_otp') {
    $otpCode = $_POST['otp_code'] ?? '';
    $bookingPhone = $_SESSION['assisted_booking_phone'] ?? '';

    if (empty($otpCode)) {
        $response = [
            'success' => false,
            'message' => $lang['enter_otp'] ?? 'Please enter OTP',
            'type' => 'error'
        ];
    } elseif (empty($bookingPhone)) {
        $response = [
            'success' => false,
            'message' => $lang['session_expired'] ?? 'Session expired. Please start over.',
            'type' => 'error'
        ];
    } else {
        $otpHelper = new OTPHelper($db, new SparrowSMSService($db));
        $verifyResult = $otpHelper->verifyOTP($bookingPhone, $otpCode);
        
        if ($verifyResult['success']) {
            $response = [
                'success' => true,
                'message' => $lang['otp_verified'] ?? 'OTP verified successfully',
                'type' => 'success'
            ];
            $currentStep = 'booking_details';
        } else {
            $response = [
                'success' => false,
                'message' => $verifyResult['message'] ?? 'Invalid OTP',
                'type' => 'error'
            ];
        }
    }
}

// ============================================
// STEP 3: COMPLETE BOOKING
// ============================================
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentStep === 'booking_details') {
    $bookingPhone = $_SESSION['assisted_booking_phone'] ?? '';
    $departmentId = $_POST['department_id'] ?? null;
    $hospitalId = $_POST['hospital_id'] ?? null;

    if (empty($bookingPhone) || empty($departmentId)) {
        $response = [
            'success' => false,
            'message' => $lang['select_all_fields'] ?? 'Please select all required fields',
            'type' => 'error'
        ];
    } else {
        // Prepare triage data
        $triageData = [
            'has_fever' => isset($_POST['has_fever']) ? 1 : 0,
            'fever_days' => $_POST['fever_days'] ?? null,
            'difficulty_breathing' => isset($_POST['difficulty_breathing']) ? 1 : 0,
            'has_injury' => isset($_POST['has_injury']) ? 1 : 0,
            'injury_severity' => $_POST['injury_severity'] ?? null,
            'is_pregnant' => isset($_POST['is_pregnant']) ? 1 : 0,
            'has_chronic_disease' => isset($_POST['has_chronic_disease']) ? 1 : 0,
            'chronic_disease_types' => isset($_POST['chronic_diseases']) ? json_encode($_POST['chronic_diseases']) : null,
            'additional_notes' => $_POST['additional_notes'] ?? null
        ];

        $locationData = [
            'district' => $_POST['district'] ?? null,
            'municipality' => $_POST['municipality'] ?? null,
            'ward' => $_POST['ward'] ?? null
        ];

        // Complete the booking
        $bookingResult = $tokenController->completeBookingAfterOTPVerification(
            $bookingPhone,
            $departmentId,
            $triageData,
            null,
            $hospitalId,
            $locationData
        );

        if ($bookingResult['success']) {
            $_SESSION['token_data'] = [
                'token' => $bookingResult['token'],
                'department' => $bookingResult['department'],
                'person_name' => $_SESSION['assisted_booking_name'] ?? 'Person'
            ];
            
            // Clear session data
            unset($_SESSION['assisted_booking_phone']);
            unset($_SESSION['assisted_booking_name']);
            
            header('Location: /smarthealth_nepal/frontend/views/token/confirmation.php');
            exit;
        } else {
            $response = [
                'success' => false,
                'message' => $bookingResult['message'] ?? 'Booking failed',
                'type' => 'error'
            ];
        }
    }
}

// Get departments list
$deptList = $tokenController->getDepartmentsList();

$pageTitle = $lang['assisted_booking'] ?? 'Book for Others';
$activePage = 'assisted';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4 mb-5">
    <div class="col-lg-9">
        
        <!-- Header -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong><?php echo $lang['assisted_info_title'] ?? 'Book Tokens for Others'; ?></strong><br>
            <?php echo $lang['assisted_info_desc'] ?? 'Help your family members or friends book hospital appointments. No need for them to have a smartphone!'; ?>
        </div>

        <!-- Booking Form Card -->
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-heart me-2"></i>
                    <?php echo $lang['book_token_for_others'] ?? 'Book Token For Others'; ?>
                </h5>
            </div>

            <div class="card-body p-4">
                
                <!-- Error/Success Message -->
                <?php if (!empty($response['message'])): ?>
                <div class="alert alert-<?php echo $response['type']; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $response['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                    <?php echo $response['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- STEP 1: Enter Person Details -->
                <?php if ($currentStep === 'enter_details'): ?>
                <form method="POST" action="">
                    <input type="hidden" name="step" value="enter_details">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <?php echo $lang['person_phone'] ?? 'Person\'s Phone Number'; ?> <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg" name="person_phone" 
                               placeholder="9801234567" maxlength="10" required>
                        <small class="text-muted"><?php echo $lang['enter_10_digits'] ?? '10 digits (e.g., 9801234567)'; ?></small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user text-primary me-2"></i>
                            <?php echo $lang['person_name'] ?? 'Person\'s Name'; ?> <span class="text-muted">(<?php echo $lang['optional'] ?? 'Optional'; ?>)</span>
                        </label>
                        <input type="text" class="form-control form-control-lg" name="person_name" 
                               placeholder="e.g., Father, Mother, Brother">
                        <small class="text-muted"><?php echo $lang['help_identify'] ?? 'This helps you remember who the booking is for'; ?></small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-right me-2"></i><?php echo $lang['send_otp'] ?? 'Send OTP'; ?>
                        </button>
                    </div>
                </form>

                <!-- STEP 2: Verify OTP -->
                <?php elseif ($currentStep === 'verify_otp'): ?>
                <div class="mb-4 p-3 bg-light rounded">
                    <h6><?php echo $lang['verification_details'] ?? 'Verification Details'; ?></h6>
                    <p class="mb-0">
                        <strong><?php echo $lang['phone_number'] ?? 'Phone'; ?>:</strong> 
                        <code><?php echo htmlspecialchars($bookingPhone); ?></code>
                    </p>
                    <?php if (!empty($bookingName)): ?>
                    <p class="mb-0">
                        <strong><?php echo $lang['person_name'] ?? 'Name'; ?>:</strong> 
                        <?php echo htmlspecialchars($bookingName); ?>
                    </p>
                    <?php endif; ?>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="step" value="verify_otp">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-key text-primary me-2"></i>
                            <?php echo $lang['otp_code'] ?? 'OTP Code'; ?> <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg text-center" name="otp_code" 
                               placeholder="000000" maxlength="6" required autofocus
                               style="font-size: 2rem; letter-spacing: 0.5rem;">
                        <small class="text-muted"><?php echo $lang['otp_received'] ?? 'Enter the OTP sent to the phone number'; ?></small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check me-2"></i><?php echo $lang['verify_and_continue'] ?? 'Verify & Continue'; ?>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                            <i class="fas fa-arrow-left me-2"></i><?php echo $lang['go_back'] ?? 'Go Back'; ?>
                        </button>
                    </div>
                </form>

                <!-- STEP 3: Booking Details (Simplified) -->
                <?php elseif ($currentStep === 'booking_details'): ?>
                <div class="mb-4 p-3 bg-light rounded">
                    <h6><?php echo $lang['booking_summary'] ?? 'Booking Summary'; ?></h6>
                    <p class="mb-0">
                        <strong><?php echo $lang['phone_number'] ?? 'Phone'; ?>:</strong> 
                        <code><?php echo htmlspecialchars($bookingPhone); ?></code>
                    </p>
                    <?php if (!empty($bookingName)): ?>
                    <p class="mb-0">
                        <strong><?php echo $lang['person_name'] ?? 'Name'; ?>:</strong> 
                        <?php echo htmlspecialchars($bookingName); ?>
                    </p>
                    <?php endif; ?>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="step" value="booking_details">
                    <input type="hidden" name="phone_number" value="<?php echo htmlspecialchars($bookingPhone); ?>">

                    <!-- Department Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-hospital text-primary me-2"></i>
                            <?php echo $lang['select_department'] ?? 'Select Department'; ?> <span class="text-danger">*</span>
                        </label>
                        <select class="form-control form-control-lg" name="department_id" required>
                            <option value="">-- <?php echo $lang['choose_department'] ?? 'Choose Department'; ?> --</option>
                            <?php 
                            // Remove duplicate departments (keep first occurrence of each ID)
                            $seenDeptIds = array();
                            $uniqueDepts = array();
                            $depts = $deptList['departments'] ?? $deptList;
                            foreach ($depts as $dept) {
                                if (!isset($seenDeptIds[$dept['id']])) {
                                    $seenDeptIds[$dept['id']] = true;
                                    $uniqueDepts[] = $dept;
                                }
                            }
                            
                            foreach ($uniqueDepts as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>">
                                <?php echo htmlspecialchars($dept['name'] ?? ($dept['name_en'] ?? $dept['name_ne'])); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Quick Health Info (Optional) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-stethoscope text-primary me-2"></i>
                            <?php echo $lang['brief_symptoms'] ?? 'Brief Symptoms (Optional)'; ?>
                        </label>
                        <textarea class="form-control" name="additional_notes" rows="2" 
                                  placeholder="e.g., High fever, Body pain, Cough..."></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-plus-circle me-2"></i><?php echo $lang['complete_booking'] ?? 'Complete Booking'; ?>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                            <i class="fas fa-arrow-left me-2"></i><?php echo $lang['go_back'] ?? 'Go Back'; ?>
                        </button>
                    </div>
                </form>
                <?php endif; ?>

            </div>
        </div>

        <!-- Help Section -->
        <div class="alert alert-light mt-4">
            <h6 class="mb-3"><i class="fas fa-question-circle text-primary me-2"></i><?php echo $lang['how_it_works_assisted'] ?? 'How It Works'; ?></h6>
            <ol class="mb-0">
                <li><?php echo $lang['step_enter_phone'] ?? 'Enter the phone number of the person you want to book for'; ?></li>
                <li><?php echo $lang['step_verify_otp'] ?? 'They will receive an OTP on their phone'; ?></li>
                <li><?php echo $lang['step_enter_otp'] ?? 'Enter the OTP they receive'; ?></li>
                <li><?php echo $lang['step_select_dept'] ?? 'Select the department they need'; ?></li>
                <li><?php echo $lang['step_booking_done'] ?? 'Booking complete! Token sent to their phone'; ?></li>
            </ol>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-4">
            <a href="/smarthealth_nepal/frontend/views/offline/index.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i><?php echo $lang['back_to_options'] ?? 'Back to Booking Options'; ?>
            </a>
        </div>
    </div>
</div>

<script>
// Auto-format phone input
document.querySelector('input[name="person_phone"]')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').slice(0, 10);
});

// Auto-format OTP input
document.querySelector('input[name="otp_code"]')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
