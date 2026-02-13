<?php
/**
 * Token Booking Page - New Smart Flow with Auto-Login
 * 
 * Flow:
 * 1. If logged in -> go straight to booking details
 * 2. If not logged in -> enter phone to get OTP
 * 3. Verify OTP -> Auto-login user -> Complete booking
 * 4. Logged-in users can book multiple tokens without re-entering phone/OTP
 */

// Set language if switching
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load backend
require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/TokenController.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../../backend/helpers/OTPHelper.php';
require_once __DIR__ . '/../../../backend/helpers/HospitalHelper.php';
require_once __DIR__ . '/../../../backend/helpers/AppointmentSlotHelper.php';
require_once __DIR__ . '/../../../backend/services/SparrowSMSService.php';

// Initialize variables
$authController = new AuthController($db);
$tokenController = new TokenController($db);
$hospitalHelper = new HospitalHelper($db);
$lang = [];

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Check login status
$isLoggedIn = $authController->isLoggedIn();
$user = $isLoggedIn ? $authController->getCurrentUser() : null;
$userId = $_SESSION['user_id'] ?? null;
$userPhone = $user['phone_number'] ?? null;

// Determine current step - if logged in, skip to booking
$defaultStep = $isLoggedIn ? 'booking_details' : 'phone_entry';
$currentStep = isset($_POST['step']) ? $_POST['step'] : $defaultStep;

// Response messages - clear when logged in user first loads form
$response = [
    'success' => null,
    'message' => '',
    'type' => ''
];

// Clear any stale OTP session errors for logged-in users
if ($isLoggedIn && $currentStep === 'booking_details' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // First-time load for logged-in user - no errors
    $response['success'] = null;
}

// ============================================
// STEP 1: PHONE NUMBER ENTRY (Only for non-logged-in users)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentStep === 'phone_entry') {
    $phoneNumber = $_POST['phone_number'] ?? '';
    
    // Validate phone number
    if (empty($phoneNumber)) {
        $response = [
            'success' => false,
            'message' => $lang['enter_phone'] ?? 'Please enter your phone number',
            'type' => 'error'
        ];
    } else {
        // Format phone number
        require_once __DIR__ . '/../../../backend/helpers/SMSHelper.php';
        $smsHelper = new SMSHelper();
        
        if (!$smsHelper->isValidPhone($phoneNumber)) {
            $response = [
                'success' => false,
                'message' => $lang['invalid_phone'] ?? 'Invalid phone number format',
                'type' => 'error'
            ];
        } else {
            $phoneNumber = $smsHelper->formatPhone($phoneNumber);
            
            // SEND OTP - Check if one already exists (don't generate multiple)
            $otpResult = $tokenController->sendOTPForBooking($phoneNumber);
            
            if ($otpResult['success'] || !$otpResult['sms_sent']) {
                $_SESSION['booking_phone'] = $phoneNumber;
                $_SESSION['otp_session_id'] = $otpResult['otp_session_id'] ?? null;
                $_SESSION['sms_sent'] = $otpResult['sms_sent'] ?? false;
                $_SESSION['is_existing_otp'] = $otpResult['is_existing'] ?? false;
                
                $currentStep = 'otp_verification';  // Jump directly to OTP verification
                
                $message = $otpResult['is_existing'] 
                    ? 'OTP already sent. Please enter it below.'
                    : ($otpResult['sms_sent'] 
                        ? 'OTP sent successfully to your phone' 
                        : 'OTP generated. You can proceed with verification.');
                
                $response = [
                    'success' => true,
                    'message' => $message,
                    'type' => 'success',
                    'sms_sent' => $otpResult['sms_sent'],
                    'is_existing_otp' => $otpResult['is_existing'] ?? false
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => $otpResult['error'] ?? 'Failed to send OTP',
                    'type' => 'error',
                    'sms_sent' => false
                ];
            }
        }
    }
}

// ============================================
// STEP 2: OTP VERIFICATION (validates and auto-logins)
// ============================================
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentStep === 'otp_verification') {
    $phoneNumber = $_SESSION['booking_phone'] ?? $userPhone ?? '';
    $otpCode = $_POST['otp_code'] ?? '';
    
    if (empty($otpCode)) {
        $response = [
            'success' => false,
            'message' => $lang['enter_otp'] ?? 'Please enter OTP',
            'type' => 'error'
        ];
    } else if (empty($phoneNumber)) {
        $response = [
            'success' => false,
            'message' => 'Phone number is required for OTP verification',
            'type' => 'error'
        ];
    } else {
        // VERIFY OTP
        $otpHelper = new OTPHelper($db, new SparrowSMSService($db));
        $verifyResult = $otpHelper->verifyOTP($phoneNumber, $otpCode);
        
        if ($verifyResult['success']) {
            // OTP verified successfully!
            $_SESSION['otp_verified'] = true;
            $_SESSION['mpin'] = $verifyResult['mpin'] ?? '';
            $_SESSION['otp_session_id'] = $verifyResult['session_id'] ?? $_SESSION['otp_session_id'];
            
            // AUTO-LOGIN: Create user session if doesn't exist
            if (!$isLoggedIn && $verifyResult['user_id']) {
                // User exists - auto-login
                $_SESSION['user_id'] = $verifyResult['user_id'];
                $_SESSION['user_name'] = $verifyResult['user_name'];
                $_SESSION['is_logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                error_log("User auto-logged in: " . $verifyResult['user_name'] . " | Phone: " . $phoneNumber);
            } elseif (!$isLoggedIn && !$verifyResult['user_id']) {
                // New user - will create during booking
                $_SESSION['user_phone'] = $phoneNumber;
                $_SESSION['new_user'] = true;
                
                error_log("New user registration initiated | Phone: " . $phoneNumber);
            }
            
            // Move to booking details step
            $currentStep = 'booking_details';
            
            $response = [
                'success' => true,
                'message' => $lang['otp_verified'] ?? 'OTP verified successfully',
                'type' => 'success',
                'auto_logged_in' => true,
                'is_new_user' => !$verifyResult['user_id']
            ];
        } else {
            $response = [
                'success' => false,
                'message' => $verifyResult['error'] ?? 'Invalid OTP',
                'type' => 'error'
            ];
        }
    }
}

// ============================================
// STEP 3: BOOKING DETAILS (Triage info & Department selection)
// ============================================
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentStep === 'booking_details') {
    $phoneNumber = $_SESSION['booking_phone'] ?? $userPhone ?? '';
    $departmentId = $_POST['department_id'] ?? null;
    $hospitalId = $_POST['hospital_id'] ?? null;
    
    // Validate inputs
    if (empty($phoneNumber) || empty($departmentId)) {
        $response = [
            'success' => false,
            'message' => $lang['fill_all_fields'] ?? 'Please select a department',
            'type' => 'error'
        ];
    } else {
        // Store booking data in session for later use
        $_SESSION['booking_data'] = [
            'full_name' => $_POST['full_name'] ?? '',
            'have_fever' => isset($_POST['have_fever']),
            'fever_days' => $_POST['fever_days'] ?? 0,
            'difficulty_breathing' => isset($_POST['difficulty_breathing']),
            'any_injury' => isset($_POST['any_injury']),
            'injury_severity' => $_POST['injury_severity'] ?? '',
            'are_pregnant' => isset($_POST['are_pregnant']),
            'chronic_disease' => isset($_POST['chronic_disease']),
            'chronic_disease_names' => isset($_POST['chronic_disease_names']) ? explode(',', $_POST['chronic_disease_names']) : [],
            'emergency_signs' => isset($_POST['emergency_signs']),
            'additional_notes' => $_POST['additional_notes'] ?? ''
        ];
        
        // Store appointment scheduling data
        $_SESSION['booking_appointment'] = [
            'appointment_date' => $_POST['appointment_date'] ?? null,
            'appointment_slot_id' => $_POST['appointment_slot_id'] ?? null,
            'booking_type' => $_POST['booking_type'] ?? 'Regular'
        ];
        
        // Store location data
        $_SESSION['booking_location'] = [
            'district' => $_POST['district'] ?? null,
            'municipality' => $_POST['municipality'] ?? null,
            'ward' => $_POST['ward'] ?? null
        ];
        
        $_SESSION['booking_department_id'] = $departmentId;
        $_SESSION['booking_hospital_id'] = $hospitalId;
        $_SESSION['booking_phone'] = $phoneNumber;
        
        // CHOOSE BOOKING METHOD BASED ON USER STATUS
        // For logged-in users: use bookToken() directly (no OTP needed)
        // For new users: use completeBookingAfterOTPVerification() (OTP already verified)
        if ($isLoggedIn && $userId) {
            // User is already logged in - booking for themselves
            // Use the direct bookToken method
            $bookingResult = $tokenController->bookToken(
                $userId,
                $departmentId,
                $_SESSION['booking_data'],
                $hospitalId,
                $_SESSION['booking_location']
            );
            
            // Add appointment slot booking if appointment was selected
            if ($bookingResult['success'] && !empty($_SESSION['booking_appointment']['appointment_slot_id'])) {
                $appointmentSlotId = $_SESSION['booking_appointment']['appointment_slot_id'];
                $tokenId = $bookingResult['token']['id'] ?? null;
                
                if ($tokenId && $appointmentSlotId) {
                    $appointmentHelper = new AppointmentSlotHelper($db);
                    $slotResult = $appointmentHelper->bookSlot($appointmentSlotId, $tokenId);
                    
                    if ($slotResult['success']) {
                        // Update token with appointment details
                        $db->query("UPDATE tokens SET 
                            appointment_date = '{$_SESSION['booking_appointment']['appointment_date']}',
                            appointment_slot_id = $appointmentSlotId,
                            booking_type = '{$_SESSION['booking_appointment']['booking_type']}'
                            WHERE id = $tokenId"
                        );
                    }
                }
            }
        } else {
            // User is not logged in - must have completed OTP verification
            $bookingResult = $tokenController->completeBookingAfterOTPVerification(
                $phoneNumber,
                $departmentId,
                $_SESSION['booking_data'],
                $_SESSION['otp_session_id'] ?? null,
                $hospitalId,
                $_SESSION['booking_location'],
                $_SESSION['booking_appointment'] ?? null
            );
        }
        
        if ($bookingResult['success']) {
            // SUCCESS - Token generated!
            // Ensure user is logged in after token generation
            if (!empty($bookingResult['user_id'])) {
                $_SESSION['user_id'] = $bookingResult['user_id'];
                $_SESSION['is_logged_in'] = true;
                $_SESSION['login_time'] = time();
            }
            
            // Store token data for confirmation page
            $_SESSION['booking_success'] = true;
            $_SESSION['token_data'] = $bookingResult;
            
            // Clear booking session data
            unset($_SESSION['booking_phone']);
            unset($_SESSION['booking_data']);
            unset($_SESSION['booking_department_id']);
            unset($_SESSION['booking_hospital_id']);
            unset($_SESSION['booking_location']);
            unset($_SESSION['booking_appointment']);
            unset($_SESSION['new_user']);
            
            header('Location: /smarthealth_nepal/frontend/views/token/confirmation.php');
            exit;
        } else {
            $response = [
                'success' => false,
                'message' => $bookingResult['message'] ?? 'Failed to complete booking',
                'type' => 'error'
            ];
        }
    }
}

// Check if user is in middle of booking without required session data
if (empty($_SESSION['booking_phone']) && empty($userPhone) && $currentStep !== 'phone_entry') {
    $currentStep = 'phone_entry';
}

// Get departments list
$deptList = $tokenController->getDepartmentsList();

$pageTitle = $lang['book_new_token'] ?? 'Book Token';
$activePage = 'book';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-ticket-alt"></i>
                    <?php echo $lang['book_new_token'] ?? 'Book New Token'; ?>
                </h5>
            </div>
            
            <div class="card-body p-4">
                
                <?php if ($response['success'] === false): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $response['message']; ?>
                </div>
                <?php elseif ($response['success'] === true && $currentStep !== 'phone_entry'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $response['message']; ?>
                    <?php if (isset($_SESSION['booking_phone']) && file_exists('../../backend/otp_debug.php')): ?>
                    <br><small><strong>Development Tip:</strong> View <a href="/smarthealth_nepal/backend/otp_debug.php" target="_blank">OTP Debug Panel</a> to see generated codes</small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- ====================== STEP 1: PHONE ENTRY ====================== -->
                <?php if ($currentStep === 'phone_entry'): ?>
                
                <form method="POST" id="phoneForm">
                    <input type="hidden" name="step" value="phone_entry">
                    
                    <div class="mb-4">
                        <h6 class="badge bg-info mb-3" style="font-size: 14px;">
                            <i class="fas fa-phone"></i> <?php echo $lang['step_1'] ?? 'Step 1'; ?>: <?php echo $lang['enter_phone'] ?? 'Enter Phone Number'; ?>
                        </h6>
                        
                        <label for="phoneNumber" class="form-label">
                            <strong><?php echo $lang['phone_number'] ?? 'Phone Number'; ?> *</strong>
                        </label>
                        <input type="tel" class="form-control form-control-lg" id="phoneNumber" name="phone_number" 
                               placeholder="98XXXXXXXX" required 
                               pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number">
                        <small class="form-text text-muted">
                            <?php echo $lang['phone_format'] ?? 'Enter 10-digit phone number (98XXXXXXXX)'; ?>
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-right"></i> <?php echo $lang['next'] ?? 'Next'; ?>
                        </button>
                        <a href="/smarthealth_nepal/frontend/views/home/" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> <?php echo $lang['cancel'] ?? 'Cancel'; ?>
                        </a>
                    </div>
                </form>
                
                <!-- ====================== STEP 2: BOOKING DETAILS ====================== -->
                <?php elseif ($currentStep === 'booking_details'): ?>
                
                <form method="POST" id="bookingForm">
                    <input type="hidden" name="step" value="booking_details">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong><?php echo $lang['phone_message'] ?? 'Phone: '; ?></strong> <?php echo htmlspecialchars($_SESSION['booking_phone'] ?? ''); ?>
                    </div>
                    
                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="fullName" class="form-label">
                            <strong><?php echo $lang['full_name'] ?? 'Full Name'; ?></strong>
                        </label>
                        <input type="text" class="form-control" id="fullName" name="full_name" placeholder="Enter your full name">
                    </div>
                    
                    <!-- Location Selection -->
                    <fieldset class="mb-4">
                        <legend class="mb-3">
                            <h6 class="badge bg-info"><i class="fas fa-map-marker-alt"></i> <?php echo $lang['your_location'] ?? 'Your Location'; ?></h6>
                        </legend>
                        
                        <div class="row">
                            <!-- District Selection -->
                            <div class="col-md-4 mb-3">
                                <label for="district" class="form-label">
                                    <strong><?php echo $lang['district'] ?? 'District'; ?></strong>
                                </label>
                                <select class="form-control" id="district" name="district" onchange="updateMunicipalities()">
                                    <option value="">Select District...</option>
                                    <?php 
                                    $districts = $hospitalHelper->getDistrictsList();
                                    foreach ($districts as $dist): 
                                    ?>
                                    <option value="<?php echo htmlspecialchars($dist); ?>">
                                        <?php echo htmlspecialchars($dist); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Municipality Selection -->
                            <div class="col-md-4 mb-3">
                                <label for="municipality" class="form-label">
                                    <strong><?php echo $lang['municipality'] ?? 'Municipality'; ?></strong>
                                </label>
                                <select class="form-control" id="municipality" name="municipality" onchange="updateWards()">
                                    <option value="">Select Municipality...</option>
                                </select>
                            </div>
                            
                            <!-- Ward Selection -->
                            <div class="col-md-4 mb-3">
                                <label for="ward" class="form-label">
                                    <strong><?php echo $lang['ward'] ?? 'Ward'; ?></strong>
                                </label>
                                <select class="form-control" id="ward" name="ward">
                                    <option value="">Select Ward...</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                    
                    <!-- Hospital Selection / Suggestions -->
                    <div id="hospitalSuggestionContainer" class="mb-4" style="display:none;">
                        <fieldset>
                            <legend class="mb-3">
                                <h6 class="badge bg-success"><i class="fas fa-hospital"></i> <?php echo $lang['suggested_hospitals'] ?? 'Select Hospital'; ?></h6>
                            </legend>
                            <div id="hospitalOptions">
                                <select class="form-control form-control-lg" id="hospital_id" name="hospital_id" required disabled>
                                    <option value="">Loading hospitals...</option>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    
                    <hr>
                    
                    <!-- Department Selection -->
                    <fieldset class="mb-4">
                        <legend class="mb-3">
                            <h6 class="badge bg-info"><?php echo $lang['select_department'] ?? 'Select Department'; ?></h6>
                        </legend>
                        
                        <div class="row">
                            <?php 
                            // Remove duplicate departments (keep first occurrence of each ID)
                            $seenDeptIds = array();
                            $uniqueDepts = array();
                            foreach ($deptList['departments'] as $dept) {
                                if (!isset($seenDeptIds[$dept['id']])) {
                                    $seenDeptIds[$dept['id']] = true;
                                    $uniqueDepts[] = $dept;
                                }
                            }
                            
                            foreach ($uniqueDepts as $dept): ?>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="department_id"
                                           id="dept_<?php echo $dept['id']; ?>" value="<?php echo $dept['id']; ?>"
                                           <?php echo empty($_SESSION['booking_department_id']) && $uniqueDepts[0]['id'] == $dept['id'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="dept_<?php echo $dept['id']; ?>">
                                        <strong><?php echo $_SESSION['language'] === 'ne' ? $dept['name_ne'] : $dept['name']; ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            Load: <span class="badge bg-<?php 
                                                echo $dept['load'] === 'High' ? 'danger' : ($dept['load'] === 'Moderate' ? 'warning' : 'success');
                                            ?>">
                                                <?php echo $dept['load']; ?>
                                            </span>
                                        </small>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    
                    <!-- Health Triage -->
                    <fieldset class="mb-4">
                        <legend class="mb-3">
                            <h6 class="badge bg-info"><?php echo $lang['health_assessment'] ?? 'Health Assessment'; ?></h6>
                        </legend>
                        
                        <p class="text-muted"><?php echo $lang['questionnaire'] ?? 'Please answer the following questions:'; ?></p>
                        
                        <!-- Fever -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="have_fever" id="fever" onchange="toggleFeverDays()">
                                <label class="form-check-label" for="fever">
                                    <strong><?php echo $lang['have_fever'] ?? 'Do you have fever?'; ?></strong>
                                </label>
                            </div>
                            <div id="feverDays" class="mt-2" style="display:none;">
                                <label for="feverDuration" class="form-label"><?php echo $lang['fever_days'] ?? 'How many days?'; ?></label>
                                <input type="number" class="form-control" id="feverDuration" name="fever_days" min="1" max="30">
                            </div>
                        </div>
                        
                        <!-- Breathing -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="difficulty_breathing" id="breathing">
                                <label class="form-check-label" for="breathing">
                                    <strong><?php echo $lang['difficulty_breathing'] ?? 'Difficulty breathing?'; ?></strong>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Injury -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="any_injury" id="injury" onchange="toggleInjurySeverity()">
                                <label class="form-check-label" for="injury">
                                    <strong><?php echo $lang['any_injury'] ?? 'Any injury?'; ?></strong>
                                </label>
                            </div>
                            <div id="injurySeverity" class="mt-2" style="display:none;">
                                <label for="severity" class="form-label"><?php echo $lang['injury_severity'] ?? 'Severity'; ?></label>
                                <select class="form-control" name="injury_severity" id="severity">
                                    <option value="">Select...</option>
                                    <option value="Minor">Minor</option>
                                    <option value="Moderate">Moderate</option>
                                    <option value="Severe">Severe</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Pregnancy -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="are_pregnant" id="pregnant">
                                <label class="form-check-label" for="pregnant">
                                    <strong><?php echo $lang['are_pregnant'] ?? 'Pregnant?'; ?></strong>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Chronic Disease -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="chronic_disease" id="chronic" onchange="toggleChronicList()">
                                <label class="form-check-label" for="chronic">
                                    <strong><?php echo $lang['chronic_disease'] ?? 'Chronic disease?'; ?></strong>
                                </label>
                            </div>
                            <div id="chronicList" class="mt-2" style="display:none;">
                                <label for="diseases" class="form-label"><?php echo $lang['select_disease'] ?? 'Select disease:'; ?></label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chronic_diseases" value="Diabetes" id="diabetes">
                                        <label class="form-check-label" for="diabetes"><?php echo $lang['diabetes'] ?? 'Diabetes'; ?></label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chronic_diseases" value="Hypertension" id="hypertension">
                                        <label class="form-check-label" for="hypertension"><?php echo $lang['hypertension'] ?? 'Hypertension'; ?></label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chronic_diseases" value="Respiratory" id="respiratory">
                                        <label class="form-check-label" for="respiratory"><?php echo $lang['respiratory'] ?? 'Respiratory Disease'; ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Emergency Signs -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="emergency_signs" id="emergency">
                                <label class="form-check-label" for="emergency">
                                    <strong class="text-danger"><?php echo $lang['emergency_signs'] ?? 'Emergency signs?'; ?></strong>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Additional Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?php echo $lang['additional_notes'] ?? 'Additional information:'; ?></label>
                            <textarea class="form-control" name="additional_notes" id="notes" rows="3"></textarea>
                        </div>
                    </fieldset>
                    
                    <hr>
                    
                    <!-- Appointment Scheduling -->
                    <fieldset class="mb-4">
                        <legend class="mb-3">
                            <h6 class="badge bg-info"><i class="fas fa-calendar-alt"></i> <?php echo $lang['appointment_scheduling'] ?? 'Appointment Scheduling'; ?></h6>
                        </legend>
                        
                        <p class="text-muted"><?php echo $lang['select_preferred_date'] ?? 'Select your preferred appointment date and time:'; ?></p>
                        
                        <!-- Appointment Date Selection -->
                        <div class="mb-3">
                            <label for="appointmentDate" class="form-label">
                                <strong><?php echo $lang['appointment_date'] ?? 'Preferred Date'; ?></strong>
                            </label>
                            <select class="form-control form-control-lg" id="appointmentDate" name="appointment_date" onchange="loadTimeSlots()">
                                <option value="">Loading available dates...</option>
                            </select>
                            <small class="form-text text-muted">
                                <?php echo $lang['date_info'] ?? 'Only dates with available slots are shown'; ?>
                            </small>
                        </div>
                        
                        <!-- Time Window Selection -->
                        <div class="mb-3">
                            <label for="appointmentSlot" class="form-label">
                                <strong><?php echo $lang['time_window'] ?? 'Preferred Time Window'; ?></strong>
                            </label>
                            <div id="timeSlotsContainer" class="row" style="display: none;">
                                <div id="timeSlotOptions">
                                    <select class="form-control form-control-lg" id="appointmentSlot" name="appointment_slot_id">
                                        <option value="">Select a time window...</option>
                                    </select>
                                </div>
                            </div>
                            <div id="noSlotsAvailable" class="alert alert-warning" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <?php echo $lang['no_slots_available'] ?? 'No available time slots for selected date'; ?>
                            </div>
                        </div>
                        
                        <!-- Booking Type (Regular vs Urgent) -->
                        <div class="mb-3">
                            <label class="form-label">
                                <strong><?php echo $lang['booking_type'] ?? 'Booking Type'; ?></strong>
                            </label>
                            <div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="booking_type" id="bookingRegular" value="Regular" checked>
                                    <label class="form-check-label" for="bookingRegular">
                                        <strong><?php echo $lang['booking_regular'] ?? 'Regular Check-up'; ?></strong>
                                        <br><small class="text-muted"><?php echo $lang['regular_info'] ?? 'Book early morning slot for regular appointments'; ?></small>
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="booking_type" id="bookingUrgent" value="Urgent">
                                    <label class="form-check-label" for="bookingUrgent">
                                        <strong class="text-warning"><?php echo $lang['booking_urgent'] ?? 'Urgent'; ?></strong>
                                        <br><small class="text-muted"><?php echo $lang['urgent_info'] ?? 'For conditions that need earlier attention'; ?></small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-arrow-right"></i> <?php echo $lang['next'] ?? 'Next'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> <?php echo $lang['back'] ?? 'Back'; ?>
                        </button>
                    </div>
                </form>
                
                <!-- ====================== STEP 3: OTP VERIFICATION ====================== -->
                <?php elseif ($currentStep === 'otp_verification'): ?>
                
                <form method="POST" id="otpForm">
                    <input type="hidden" name="step" value="otp_verification">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong><?php echo $lang['otp_sent_to'] ?? 'OTP sent to: '; ?></strong> 
                        <?php echo htmlspecialchars(substr($_SESSION['booking_phone'], -6, 6) ?? ''); ?>
                    </div>
                    
                    <?php if (!($response['can_proceed'] ?? false) && file_exists('../../backend/otp_debug.php')): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong><?php echo $lang['sms_not_delivered'] ?? 'SMS Not Delivered'; ?></strong><br>
                        If you don't receive SMS within 10 seconds, please check the 
                        <a href="/smarthealth_nepal/backend/otp_debug.php" target="_blank" class="alert-link">OTP Debug Panel</a> 
                        to view the generated OTP code for testing purposes.
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <label for="otpCode" class="form-label">
                            <strong><?php echo $lang['enter_otp'] ?? 'Enter OTP'; ?> *</strong>
                        </label>
                        <input type="text" class="form-control form-control-lg" id="otpCode" name="otp_code" 
                               placeholder="000000" maxlength="6" required 
                               style="font-size: 24px; letter-spacing: 10px; text-align: center;">
                        <small class="form-text text-muted">
                            <?php echo $lang['otp_expires_in'] ?? 'OTP expires in 10 minutes'; ?>
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> <?php echo $lang['verify_otp'] ?? 'Verify OTP'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> <?php echo $lang['back'] ?? 'Back'; ?>
                        </button>
                    </div>
                </form>
                
                <!-- ====================== STEP 4: CONFIRM BOOKING ====================== -->
                <?php elseif ($currentStep === 'complete_booking'): ?>
                
                <form method="POST" id="confirmForm">
                    <input type="hidden" name="step" value="complete_booking">
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong><?php echo $lang['otp_verified_success'] ?? 'OTP Verified Successfully!'; ?></strong>
                    </div>
                    
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $lang['booking_summary'] ?? 'Booking Summary'; ?></h6>
                            <p class="mb-2">
                                <strong><?php echo $lang['phone'] ?? 'Phone'; ?>:</strong> 
                                <?php echo htmlspecialchars($_SESSION['booking_phone'] ?? ''); ?>
                            </p>
                            <p class="mb-2">
                                <strong><?php echo $lang['department'] ?? 'Department'; ?>:</strong> 
                                <?php 
                                $deptId = $_SESSION['booking_department_id'] ?? null;
                                foreach ($deptList['departments'] as $dept) {
                                    if ($dept['id'] == $deptId) {
                                        echo htmlspecialchars($_SESSION['language'] === 'ne' ? $dept['name_ne'] : $dept['name']);
                                        break;
                                    }
                                }
                                ?>
                            </p>
                            <p class="mb-0">
                                <strong><?php echo $lang['mpin_notice'] ?? 'MPIN'; ?>:</strong> 
                                <?php echo $lang['mpin_will_be_sent'] ?? 'An MPIN will be sent to your phone for future logins'; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-ticket-alt"></i> <?php echo $lang['complete_booking'] ?? 'Complete Booking'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> <?php echo $lang['back'] ?? 'Back'; ?>
                        </button>
                    </div>
                </form>
                
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<script>
// Load Nepal districts data on page load
let nepalDistrictData = null;

// User location data (auto-fill if logged in)
const userLocationData = {
    district: '<?php echo isset($user['district']) && !empty($user['district']) ? htmlspecialchars($user['district']) : ''; ?>',
    municipality: '<?php echo isset($user['municipality']) && !empty($user['municipality']) ? htmlspecialchars($user['municipality']) : ''; ?>',
    ward: '<?php echo isset($user['ward']) && !empty($user['ward']) ? htmlspecialchars($user['ward']) : ''; ?>',
    isLoggedIn: <?php echo $isLoggedIn ? 'true' : 'false'; ?>
};

document.addEventListener('DOMContentLoaded', function() {
    // Load districts JSON for cascading dropdowns
    loadNepalDistrictData();
    
    // Initialize district dropdown
    updateDistrictsList();
    
    // Auto-fill user location if logged in
    if (userLocationData.isLoggedIn && userLocationData.district) {
        setTimeout(() => initializeUserLocation(), 500);
    }
    
    // Add event listeners for symptom changes to reload hospitals
    const symptomCheckboxes = [
        'fever',
        'breathing',
        'injury',
        'pregnant',
        'chronic'
    ];
    
    symptomCheckboxes.forEach(id => {
        const elem = document.getElementById(id);
        if (elem) {
            elem.addEventListener('change', function() {
                console.log('Symptom changed:', id);
                // Reload hospitals if ward is already selected
                if (document.getElementById('ward') && document.getElementById('ward').value) {
                    setTimeout(() => loadNearbyHospitals(), 300);
                }
            });
        }
    });
    
    // Add listeners for chronic disease checkboxes
    const chronicCheckboxes = document.querySelectorAll('input[name="chronic_diseases"]');
    chronicCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('Chronic disease changed');
            // Reload hospitals if ward is already selected
            if (document.getElementById('ward') && document.getElementById('ward').value) {
                setTimeout(() => loadNearbyHospitals(), 300);
            }
        });
    });
});

function toggleFeverDays() {
    document.getElementById('feverDays') && (document.getElementById('feverDays').style.display = 
        document.getElementById('fever').checked ? 'block' : 'none');
}

function toggleInjurySeverity() {
    document.getElementById('injurySeverity') && (document.getElementById('injurySeverity').style.display = 
        document.getElementById('injury').checked ? 'block' : 'none');
}

function toggleChronicList() {
    document.getElementById('chronicList') && (document.getElementById('chronicList').style.display = 
        document.getElementById('chronic').checked ? 'block' : 'none');
}

// Format phone number input
document.getElementById('phoneNumber') && document.getElementById('phoneNumber').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '').slice(0, 10);
    e.target.value = value;
});

// Format OTP input to uppercase
document.getElementById('otpCode') && document.getElementById('otpCode').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase().replace(/[^0-9]/g, '').slice(0, 6);
});

// ========== LOCATION-BASED HOSPITAL SELECTION WITH PRIORITY ==========

/**
 * Load Nepal district data from JSON file
 */
function loadNepalDistrictData() {
    fetch('/smarthealth_nepal/backend/data/nepal_districts.json')
        .then(response => response.json())
        .then(data => {
            nepalDistrictData = data;
            console.log('Nepal district data loaded successfully');
        })
        .catch(error => {
            console.error('Error loading Nepal district data:', error);
        });
}

/**
 * Update districts list from loaded data
 */
function updateDistrictsList() {
    const districtSelect = document.getElementById('district');
    if (!districtSelect) return;
    
    if (nepalDistrictData && nepalDistrictData.divisions) {
        districtSelect.innerHTML = '<option value="">Select District...</option>';
        nepalDistrictData.divisions.forEach(div => {
            const option = document.createElement('option');
            option.value = div.district;
            option.textContent = `${div.district} (${div.province})`;
            districtSelect.appendChild(option);
        });
    }
}

/**
 * Update municipalities based on selected district
 */
function updateMunicipalities() {
    const district = document.getElementById('district').value;
    const municipalitySelect = document.getElementById('municipality');
    
    if (!district) {
        municipalitySelect.innerHTML = '<option value="">Select Municipality...</option>';
        return;
    }
    
    // Use loaded Nepal data if available
    if (nepalDistrictData && nepalDistrictData.divisions) {
        municipalitySelect.innerHTML = '<option value="">Select Municipality...</option>';
        
        const divisionData = nepalDistrictData.divisions.find(d => d.district === district);
        if (divisionData && divisionData.local_levels) {
            divisionData.local_levels.forEach(localLevel => {
                const option = document.createElement('option');
                option.value = localLevel.name;
                
                // Add priority indicator
                const priorityEmoji = getPriorityEmoji(localLevel.priority_rating);
                option.textContent = `${localLevel.name} ${priorityEmoji} (P${localLevel.priority_rating})`;
                option.dataset.priority = localLevel.priority_rating;
                
                municipalitySelect.appendChild(option);
            });
        }
    }
    
    // Reset dependent selects
    document.getElementById('ward').innerHTML = '<option value="">Select Ward...</option>';
    document.getElementById('hospitalOptions').innerHTML = '<p class="text-muted text-center">Select your location to see available hospitals</p>';
}

/**
 * Get emoji for priority rating
 */
function getPriorityEmoji(priority) {
    const emojis = {
        1: '⭐',
        2: '✨',
        3: '📍',
        4: '🏘️'
    };
    return emojis[priority] || '';
}

/**
 * Update wards based on selected municipality
 */
function updateWards() {
    const district = document.getElementById('district').value;
    const municipality = document.getElementById('municipality').value;
    const wardSelect = document.getElementById('ward');
    
    if (!district || !municipality) {
        wardSelect.innerHTML = '<option value="">Select Ward...</option>';
        return;
    }
    
    // Use loaded Nepal data if available
    if (nepalDistrictData && nepalDistrictData.divisions) {
        wardSelect.innerHTML = '<option value="">Select Ward...</option>';
        
        const divisionData = nepalDistrictData.divisions.find(d => d.district === district);
        if (divisionData) {
            const localLevel = divisionData.local_levels.find(ll => ll.name === municipality);
            if (localLevel && localLevel.wards) {
                for (let i = 1; i <= localLevel.wards; i++) {
                    const option = document.createElement('option');
                    option.value = i.toString();
                    option.textContent = `Ward ${i}`;
                    wardSelect.appendChild(option);
                }
            }
        }
    }
    
    // Load nearby hospitals
    loadNearbyHospitals();
}

/**
 * Initialize user location from database (auto-fill for logged-in users)
 * Populates district -> municipality -> ward -> hospitals
 */
function initializeUserLocation() {
    if (!userLocationData.district) {
        console.log('No user location data available');
        return;
    }
    
    console.log('Initializing user location:', userLocationData);
    
    // Step 1: Set and trigger district selection
    const districtSelect = document.getElementById('district');
    if (districtSelect) {
        districtSelect.value = userLocationData.district;
        districtSelect.dispatchEvent(new Event('change', { bubbles: true }));
        
        // Step 2: Wait for municipalities to load, then set municipality
        setTimeout(() => {
            const municipalitySelect = document.getElementById('municipality');
            if (municipalitySelect && userLocationData.municipality) {
                municipalitySelect.value = userLocationData.municipality;
                municipalitySelect.dispatchEvent(new Event('change', { bubbles: true }));
                
                // Step 3: Wait for wards to load, then set ward
                setTimeout(() => {
                    const wardSelect = document.getElementById('ward');
                    if (wardSelect && userLocationData.ward) {
                        wardSelect.value = userLocationData.ward;
                        wardSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        
                        // Step 4: Hospitals will auto-load and first one will be auto-selected
                        console.log('User location initialized - hospitals loading');
                    }
                }, 800);
            }
        }, 800);
    }
}

/**
 * Collect symptoms from health assessment form
 * Maps form checkboxes to actual disease/symptom names
 */
function collectSymptoms() {
    let symptoms = [];
    
    // Check fever
    if (document.getElementById('fever') && document.getElementById('fever').checked) {
        symptoms.push('fever');
    }
    
    // Check difficulty breathing
    if (document.getElementById('breathing') && document.getElementById('breathing').checked) {
        symptoms.push('respiratory');
    }
    
    // Check injury
    if (document.getElementById('injury') && document.getElementById('injury').checked) {
        symptoms.push('injury');
    }
    
    // Check pregnancy
    if (document.getElementById('pregnant') && document.getElementById('pregnant').checked) {
        symptoms.push('maternal');
    }
    
    // Check chronic diseases
    if (document.getElementById('chronic') && document.getElementById('chronic').checked) {
        const chronicChecks = document.querySelectorAll('input[name="chronic_diseases"]:checked');
        chronicChecks.forEach(check => {
            const value = check.value.toLowerCase();
            if (value === 'diabetes') symptoms.push('diabetes');
            if (value === 'hypertension') symptoms.push('hypertension');
            if (value === 'respiratory') symptoms.push('respiratory');
        });
    }
    
    return symptoms;
}

/**
 * Fetch hospitals from API and display them
 */
function fetchAndDisplayHospitals(apiUrl, hospitalContainer, hospitalOptions) {
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Hospitals API response:', data);
            
            // Handle the API response - hospitals are nested under data.hospitals
            const hospitals = data.data?.hospitals || data.data || [];
            
            if (data.success && hospitals.length > 0) {
                // Create a wrapper div with recommended hospital info and select dropdown
                let hospitalHTML = '<div class="hospital-selection-wrapper">';
                
                // Show recommended hospital info with detailed reasons
                const recommendedHospital = hospitals[0];
                const recName = recommendedHospital.hospital_name || recommendedHospital.name || 'Nearest Hospital';
                const recDistance = recommendedHospital.distance ? ` (${recommendedHospital.distance} km away)` : '';
                const recScore = recommendedHospital.priority_score || 0;
                const recType = recommendedHospital.type || 'Hospital';
                
                // Build reasons explanation
                let reasonsText = 'Based on: ';
                const reasons = recommendedHospital.priority_reasons || [];
                const reasonLabels = {
                    'location_priority': 'Best location',
                    'specialty_match:1': 'Matches 1 specialty',
                    'specialty_match:2': 'Matches 2 specialties',
                    'specialty_match:3': 'Matches 3 specialties',
                    'specialty_match:4': 'Matches 4 specialties',
                    'specialty_match:5': 'Matches 5 specialties',
                    'nearby_under_5km': 'Within 5km',
                    'nearby_under_10km': 'Within 10km',
                    'nearby_under_20km': 'Within 20km',
                    'nearby_under_50km': 'Within 50km',
                    'government_hospital': 'Government hospital',
                };
                
                // Convert specialty match numbers
                const displayReasons = reasons.map(r => {
                    for (let key in reasonLabels) {
                        if (r.startsWith('specialty_match:')) {
                            const matches = r.split(':')[1];
                            return `Matches ${matches} specialty(ies)`;
                        }
                        if (r === key) {
                            return reasonLabels[key];
                        }
                    }
                    return r;
                });
                
                reasonsText += displayReasons.join(', ');
                
                hospitalHTML += `
                    <div class="alert alert-success mb-3" style="border-left: 4px solid #28a745;">
                        <div style="display: flex; align-items: flex-start;">
                            <i class="fas fa-star" style="color: #ffc107; font-size: 1.5rem; margin-right: 12px; margin-top: 2px;"></i>
                            <div style="flex: 1;">
                                <strong style="font-size: 1.1rem;">⭐ Recommended: ${recName}</strong><br>
                                <small class="text-muted">${recType}${recDistance}</small><br>
                                <small style="color: #666;">${reasonsText}</small>
                                <button type="button" class="btn btn-sm btn-link p-0 ms-2" onclick="showHospitalDetails()" style="font-size: 0.9rem;">View details →</button>
                            </div>
                        </div>
                    </div>
                `;
                
                // Create select dropdown
                hospitalHTML += '<select class="form-control form-control-lg" id="hospital_id" name="hospital_id" required>';
                
                // Display first (recommended) as selected with special label
                const firstHospital = hospitals[0];
                const firstName = firstHospital.hospital_name || firstHospital.name || 'Unknown Hospital';
                const firstDistance = firstHospital.distance ? ` - ${firstHospital.distance} km` : '';
                hospitalHTML += `<option value="${firstHospital.id}" selected>⭐ ${firstName}${firstDistance} (RECOMMENDED)</option>`;
                
                // Display other hospitals
                hospitals.slice(1).forEach(hospital => {
                    const hospitalName = hospital.hospital_name || hospital.name || 'Unknown Hospital';
                    const distance = hospital.distance ? ` - ${hospital.distance} km` : '';
                    const typeLabel = hospital.type === 'Government' ? ' [Govt]' : (hospital.type === 'Private' ? ' [Private]' : '');
                    const distanceNote = distance ? ` (${distance.replace(' - ', '')})` : '';
                    hospitalHTML += `<option value="${hospital.id}">${hospitalName}${typeLabel}${distanceNote}</option>`;
                });
                
                hospitalHTML += '</select>';
                hospitalHTML += '</div>'; // Close wrapper
                
                hospitalOptions.innerHTML = hospitalHTML;
                hospitalContainer.style.display = 'block';
                console.log(`Loaded ${hospitals.length} hospitals. Recommended: ${firstName}`);
                
                // Attach event listener after element is created
                setTimeout(attachHospitalEventListener, 100);
                
                // Auto-trigger appointment dates loading for the recommended hospital
                setTimeout(() => {
                    const deptRadios = document.querySelectorAll('input[name="department_id"]');
                    if (deptRadios.length > 0 && deptRadios[0].checked) {
                        loadAppointmentDates();
                    }
                }, 150);
            } else {
                hospitalOptions.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No hospitals found in your area. Please try a different location.</div>';
                hospitalContainer.style.display = 'block';
                console.log('No hospitals available:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading hospitals:', error);
            hospitalOptions.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error loading hospitals. Please try again.</div>';
            hospitalContainer.style.display = 'block';
        });
}

/**
 * Show hospital details (can be extended to show modal with detailed info)
 */
function showHospitalDetails() {
    const hospitalSelect = document.getElementById('hospital_id');
    if (hospitalSelect) {
        alert('Hospital details feature coming soon!');
    }
}

/**
 * Load nearby hospitals with priority ranking
 * Called when ward is selected
 * Uses geolocation for better proximity-based recommendations
 */
function loadNearbyHospitals() {
    const district = document.getElementById('district');
    const municipality = document.getElementById('municipality');
    const ward = document.getElementById('ward');
    const hospitalContainer = document.getElementById('hospitalSuggestionContainer');
    const hospitalOptions = document.getElementById('hospitalOptions');
    
    if (!district || !municipality || !district.value || !municipality.value) {
        hospitalContainer.style.display = 'none';
        return;
    }
    
    // Show loading state
    hospitalContainer.style.display = 'block';
    hospitalOptions.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Finding nearest hospitals for you...</div>';
    
    // Collect symptoms from form
    let symptoms = collectSymptoms();
    
    // Build API URL with parameters
    let apiUrl = `/smarthealth_nepal/backend/api/suggest_hospitals.php?action=hospitals-with-priority`;
    apiUrl += `&district=${encodeURIComponent(district.value)}`;
    apiUrl += `&municipality=${encodeURIComponent(municipality.value)}`;
    if (symptoms.length > 0) {
        apiUrl += `&symptoms=${encodeURIComponent(symptoms.join(','))}`;
    }
    
    console.log('Loading hospitals with URL:', apiUrl);
    console.log('Symptoms collected:', symptoms);
    
    // Try to get user's geolocation for better proximity ranking
    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                console.log('Geolocation available:', position.coords);
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                apiUrl += `&latitude=${lat}&longitude=${lon}`;
                console.log(`User location: ${lat}, ${lon}`);
                fetchAndDisplayHospitals(apiUrl, hospitalContainer, hospitalOptions);
            },
            function(error) {
                console.log('Geolocation not available or denied:', error.message);
                // Proceed without geolocation - API will still return hospitals sorted by municipality priority
                console.log('Proceeding with municipality-based recommendations');
                fetchAndDisplayHospitals(apiUrl, hospitalContainer, hospitalOptions);
            },
            { 
                timeout: 5000,  // 5 second timeout for geolocation
                enableHighAccuracy: false, // Don't need high accuracy for hospital proximity
                maximumAge: 60000 // Cache position for 1 minute
            }
        );
    } else {
        console.log('Geolocation not supported - using municipality-based ranking');
        fetchAndDisplayHospitals(apiUrl, hospitalContainer, hospitalOptions);
    }
}

/**
 * Load appointment dates for selected hospital and department
 * Called when hospital or department is selected
 */
function loadAppointmentDates() {
    const hospitalSelect = document.getElementById('hospital_id');
    const deptRadios = document.querySelectorAll('input[name="department_id"]');
    const dateSelect = document.getElementById('appointmentDate');
    
    if (!dateSelect) {
        console.log('Date select element not found');
        return;
    }
    
    let hospitalId = hospitalSelect ? hospitalSelect.value : null;
    let departmentId = null;
    
    // Get selected department
    for (let radio of deptRadios) {
        if (radio.checked) {
            departmentId = radio.value;
            break;
        }
    }
    
    // Reset if no selection
    if (!hospitalId || !departmentId) {
        dateSelect.innerHTML = '<option value="">Select hospital and department first...</option>';
        dateSelect.disabled = true;
        return;
    }
    
    // Show loading state
    dateSelect.innerHTML = '<option value="">Loading available dates...</option>';
    dateSelect.disabled = false;
    
    const apiUrl = `/smarthealth_nepal/backend/api/get_appointment_slots.php?action=get_dates&hospital_id=${hospitalId}&department_id=${departmentId}`;
    
    console.log('Loading dates from URL:', apiUrl);
    
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dates API response:', data);
            dateSelect.disabled = false;
            
            if (data.success && data.data && data.data.length > 0) {
                let dateHTML = '<option value="">Select a date...</option>';
                
                data.data.forEach(dateInfo => {
                    const displayText = `${dateInfo.formatted_date} (${dateInfo.available_spots} slots available)`;
                    dateHTML += `<option value="${dateInfo.slot_date}">${displayText}</option>`;
                });
                
                dateSelect.innerHTML = dateHTML;
                console.log(`Loaded ${data.data.length} available dates`);
            } else {
                dateSelect.innerHTML = '<option value="">No available dates for this department</option>';
                console.log('No dates available:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading dates:', error);
            dateSelect.disabled = false;
            dateSelect.innerHTML = '<option value="">Error loading dates</option>';
        });
}

/**
 * Load time slots for selected date
 * Called when appointment date is selected
 */
function loadTimeSlots() {
    const hospitalSelect = document.getElementById('hospital_id');
    const deptRadios = document.querySelectorAll('input[name="department_id"]');
    const dateSelect = document.getElementById('appointmentDate');
    const slotSelect = document.getElementById('appointmentSlot');
    const timeSlotsContainer = document.getElementById('timeSlotsContainer');
    const noSlotsMsg = document.getElementById('noSlotsAvailable');
    
    const hospitalId = hospitalSelect ? hospitalSelect.value : null;
    let departmentId = null;
    const slotDate = dateSelect.value;
    
    // Get selected department
    for (let radio of deptRadios) {
        if (radio.checked) {
            departmentId = radio.value;
            break;
        }
    }
    
    // Reset if incomplete
    if (!hospitalId || !departmentId || !slotDate) {
        timeSlotsContainer.style.display = 'none';
        noSlotsMsg.style.display = 'none';
        slotSelect.innerHTML = '<option value="">Select date first...</option>';
        return;
    }
    
    // Show loading state
    slotSelect.innerHTML = '<option value="">Loading time slots...</option>';
    timeSlotsContainer.style.display = 'block';
    noSlotsMsg.style.display = 'none';
    
    const apiUrl = `/smarthealth_nepal/backend/api/get_appointment_slots.php?action=get_slots&hospital_id=${hospitalId}&department_id=${departmentId}&slot_date=${slotDate}`;
    
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                let slotHTML = '<option value="">Select a time window...</option>';
                
                data.data.forEach(slot => {
                    const slotText = `${slot.time_window_start.substring(0, 5)} - ${slot.time_window_end.substring(0, 5)} (${slot.slot_type}, ${slot.available_spots} spot${slot.available_spots !== 1 ? 's' : ''})`;
                    slotHTML += `<option value="${slot.id}">${slotText}</option>`;
                });
                
                slotSelect.innerHTML = slotHTML;
                timeSlotsContainer.style.display = 'block';
                noSlotsMsg.style.display = 'none';
                console.log(`Loaded ${data.data.length} time slots`);
            } else {
                slotSelect.innerHTML = '<option value="">No available slots for this date</option>';
                timeSlotsContainer.style.display = 'block';
                noSlotsMsg.style.display = 'block';
                console.log('No slots available:', data.message);
            }
        })
        .catch(error => {
            slotSelect.innerHTML = '<option value="">Error loading slots</option>';
            timeSlotsContainer.style.display = 'block';
            noSlotsMsg.style.display = 'block';
            console.error('Error loading slots:', error);
        });
}

/**
 * Attach event listener to hospital select (used after hospitals are loaded)
 */
function attachHospitalEventListener() {
    const hospitalSelect = document.getElementById('hospital_id');
    if (hospitalSelect && !hospitalSelect.hasAttribute('data-listener-attached')) {
        hospitalSelect.addEventListener('change', function() {
            console.log('Hospital changed, loading dates');
            loadAppointmentDates();
        });
        hospitalSelect.setAttribute('data-listener-attached', 'true');
        console.log('Hospital event listener attached');
    }
}

/**
 * Initialize appointment scheduling when hospital or department changes
 */
document.addEventListener('DOMContentLoaded', function() {
    const deptRadios = document.querySelectorAll('input[name="department_id"]');
    const dateSelect = document.getElementById('appointmentDate');
    
    // Load dates when department changes
    deptRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Department changed, loading dates');
            loadAppointmentDates();
        });
    });
    
    // Try to attach hospital listener if it already exists (e.g., pre-selected)
    setTimeout(attachHospitalEventListener, 100);
    
    // Initial load if hospital already selected
    setTimeout(function() {
        const hospitalSelect = document.getElementById('hospital_id');
        if (hospitalSelect && hospitalSelect.value) {
            console.log('Hospital pre-selected, loading dates');
            loadAppointmentDates();
        }
    }, 200);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php';?>

