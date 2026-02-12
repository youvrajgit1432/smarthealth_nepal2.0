<?php
/**
 * SMS Booking Page
 * Instructions for booking tokens via SMS for users without internet access
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

$pageTitle = $lang['sms_booking'] ?? 'SMS Booking';
$activePage = 'sms_booking';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4 mb-5">
    <div class="col-lg-10">
        
        <!-- Header -->
        <div class="alert alert-success mb-4" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.15), rgba(139, 195, 74, 0.1)); border: 2px solid #4caf50; border-radius: 15px;">
            <i class="fas fa-sms me-2" style="color: #2e7d32; font-size: 1.3rem;"></i>
            <strong style="color: #1b5e20; font-size: 1.1rem;"><?php echo $lang['sms_booking_title'] ?? 'Book Tokens via SMS'; ?></strong><br>
            <span style="color: #2e7d32;"><?php echo $lang['sms_booking_desc'] ?? 'No smartphone? No internet? No problem! Book your tokens via simple SMS text messages.'; ?></span>
        </div>

        <!-- Main Info Card -->
        <div class="card shadow-lg mb-4" style="border-top: 5px solid #4caf50;">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo $lang['how_sms_booking_works'] ?? 'How SMS Booking Works'; ?>
                </h5>
            </div>
            <div class="card-body p-4">
                <p class="lead mb-4">
                    <?php echo $lang['sms_booking_intro'] ?? 'Simply send a text message to our SmartHealth number and we will help you book a token at your nearest hospital.'; ?>
                </p>

                <!-- Simple Steps -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6 col-lg-3">
                        <div class="text-center p-4" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(76, 175, 80, 0.05)); border-radius: 12px; border: 2px solid #c8e6c9;">
                            <div style="font-size: 2.5rem; color: #2e7d32; margin-bottom: 15px; font-weight: bold;">1</div>
                            <h6 style="color: #1b5e20; font-weight: 600; margin-bottom: 10px;"><?php echo $lang['sms_step_1'] ?? 'Send SMS'; ?></h6>
                            <p style="color: #424242; font-size: 0.9rem;">
                                <?php echo $lang['sms_step_1_desc'] ?? 'Text BOOK to 32323'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="text-center p-4" style="background: linear-gradient(135deg, rgba(33, 150, 243, 0.1), rgba(33, 150, 243, 0.05)); border-radius: 12px; border: 2px solid #bbdefb;">
                            <div style="font-size: 2.5rem; color: #0d47a1; margin-bottom: 15px; font-weight: bold;">2</div>
                            <h6 style="color: #1565c0; font-weight: 600; margin-bottom: 10px;"><?php echo $lang['sms_step_2'] ?? 'Get Options'; ?></h6>
                            <p style="color: #424242; font-size: 0.9rem;">
                                <?php echo $lang['sms_step_2_desc'] ?? 'We send you hospital & department options'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="text-center p-4" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05)); border-radius: 12px; border: 2px solid #fff59d;">
                            <div style="font-size: 2.5rem; color: #f57f17; margin-bottom: 15px; font-weight: bold;">3</div>
                            <h6 style="color: #e65100; font-weight: 600; margin-bottom: 10px;"><?php echo $lang['sms_step_3'] ?? 'Reply'; ?></h6>
                            <p style="color: #424242; font-size: 0.9rem;">
                                <?php echo $lang['sms_step_3_desc'] ?? 'Reply with your choice (e.g., 1 for first option)'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="text-center p-4" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(76, 175, 80, 0.05)); border-radius: 12px; border: 2px solid #c8e6c9;">
                            <div style="font-size: 2.5rem; color: #2e7d32; margin-bottom: 15px; font-weight: bold;">✓</div>
                            <h6 style="color: #1b5e20; font-weight: 600; margin-bottom: 10px;"><?php echo $lang['sms_step_4'] ?? 'Confirmed'; ?></h6>
                            <p style="color: #424242; font-size: 0.9rem;">
                                <?php echo $lang['sms_step_4_desc'] ?? 'Token booked! Get SMS updates'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SMS Commands -->
                <div class="alert alert-info" style="background: linear-gradient(135deg, rgba(33, 150, 243, 0.12), rgba(33, 150, 243, 0.08)); border: 2px solid #2196f3; border-radius: 12px;">
                    <h6 style="color: #0d47a1; font-weight: 700; margin-bottom: 15px;">
                        <i class="fas fa-phone me-2"></i><?php echo $lang['sms_commands'] ?? 'Available SMS Commands'; ?>
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="color: #424242;">
                            <tbody>
                                <tr>
                                    <td style="font-family: monospace; background: rgba(33, 150, 243, 0.05); padding: 10px; border-radius: 8px; font-weight: 600; color: #0d47a1;">BOOK</td>
                                    <td><?php echo $lang['cmd_book_desc'] ?? 'Start the booking process'; ?></td>
                                </tr>
                                <tr>
                                    <td style="font-family: monospace; background: rgba(33, 150, 243, 0.05); padding: 10px; border-radius: 8px; font-weight: 600; color: #0d47a1;">TRACK</td>
                                    <td><?php echo $lang['cmd_track_desc'] ?? 'Check your token status using token number'; ?></td>
                                </tr>
                                <tr>
                                    <td style="font-family: monospace; background: rgba(33, 150, 243, 0.05); padding: 10px; border-radius: 8px; font-weight: 600; color: #0d47a1;">STATUS</td>
                                    <td><?php echo $lang['cmd_status_desc'] ?? 'Check all your recent bookings'; ?></td>
                                </tr>
                                <tr>
                                    <td style="font-family: monospace; background: rgba(33, 150, 243, 0.05); padding: 10px; border-radius: 8px; font-weight: 600; color: #0d47a1;">HELP</td>
                                    <td><?php echo $lang['cmd_help_desc'] ?? 'Get list of all commands'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Example Conversation -->
        <div class="card shadow-lg mb-4" style="border-top: 5px solid #ff9800;">
            <div class="card-header" style="background: linear-gradient(90deg, #ff9800, #f57c00); color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-comments me-2"></i>
                    <?php echo $lang['example_conversation'] ?? 'Example SMS Conversation'; ?>
                </h5>
            </div>
            <div class="card-body p-4">
                <div style="background: #f5f5f5; padding: 15px; border-radius: 10px; font-family: monospace; font-size: 0.95rem; line-height: 2;">
                    <div style="margin-bottom: 15px;">
                        <div style="background: #e3f2fd; padding: 12px; border-radius: 8px; margin-bottom: 8px; border-left: 4px solid #2196f3;">
                            <strong style="color: #0d47a1;">You:</strong> BOOK<br>
                            <small style="color: #1565c0;">Send this to 32323</small>
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <div style="background: #c8e6c9; padding: 12px; border-radius: 8px; margin-bottom: 8px; border-left: 4px solid #4caf50;">
                            <strong style="color: #1b5e20;">SmartHealth:</strong><br>
                            Hello! Choose your hospital:<br>
                            1. Kathmandu General Hospital<br>
                            2. Bhaktapur Health Center<br>
                            3. Lalitpur Medical Clinic
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <div style="background: #e3f2fd; padding: 12px; border-radius: 8px; margin-bottom: 8px; border-left: 4px solid #2196f3;">
                            <strong style="color: #0d47a1;">You:</strong> 1
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <div style="background: #c8e6c9; padding: 12px; border-radius: 8px; margin-bottom: 8px; border-left: 4px solid #4caf50;">
                            <strong style="color: #1b5e20;">SmartHealth:</strong><br>
                            Which department do you need?<br>
                            1. General Medicine<br>
                            2. Pediatrics<br>
                            3. Emergency<br>
                            4. Maternal Health
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <div style="background: #e3f2fd; padding: 12px; border-radius: 8px; margin-bottom: 8px; border-left: 4px solid #2196f3;">
                            <strong style="color: #0d47a1;">You:</strong> 1
                        </div>
                    </div>

                    <div>
                        <div style="background: #fff9c4; padding: 12px; border-radius: 8px; border-left: 4px solid #fbc02d;">
                            <strong style="color: #f57f17;">SmartHealth:</strong><br>
                            ✓ Token Booked!<br>
                            Token #: 123456<br>
                            Hospital: Kathmandu General<br>
                            Dept: General Medicine<br>
                            Expected Wait: 45 minutes<br>
                            More info and updates will be sent via SMS
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Benefits Box -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.12), rgba(139, 195, 74, 0.08)); border-left: 5px solid #4caf50;">
                    <div class="card-body p-4 text-center">
                        <div style="font-size: 2.5rem; color: #2e7d32; margin-bottom: 15px;">
                            <i class="fas fa-wifi-off"></i>
                        </div>
                        <h6 style="color: #1b5e20; font-weight: 700; margin-bottom: 10px;"><?php echo $lang['benefit_offline'] ?? 'No Internet'; ?></h6>
                        <p style="color: #424242; font-size: 0.9rem; margin-bottom: 0;">
                            <?php echo $lang['benefit_offline_desc'] ?? 'Works with basic SMS - no app or data needed'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, rgba(33, 150, 243, 0.12), rgba(66, 165, 245, 0.08)); border-left: 5px solid #2196f3;">
                    <div class="card-body p-4 text-center">
                        <div style="font-size: 2.5rem; color: #0d47a1; margin-bottom: 15px;">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h6 style="color: #0d47a1; font-weight: 700; margin-bottom: 10px;"><?php echo $lang['benefit_oldphone'] ?? 'Old Phones'; ?></h6>
                        <p style="color: #424242; font-size: 0.9rem; margin-bottom: 0;">
                            <?php echo $lang['benefit_oldphone_desc'] ?? 'Works on any phone that can send SMS'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, rgba(255, 152, 0, 0.12), rgba(255, 193, 7, 0.08)); border-left: 5px solid #ff9800;">
                    <div class="card-body p-4 text-center">
                        <div style="font-size: 2.5rem; color: #e65100; margin-bottom: 15px;">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h6 style="color: #bf360c; font-weight: 700; margin-bottom: 10px;"><?php echo $lang['benefit_instant'] ?? 'Instant'; ?></h6>
                        <p style="color: #424242; font-size: 0.9rem; margin-bottom: 0;">
                            <?php echo $lang['benefit_instant_desc'] ?? 'Immediate confirmations via SMS'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supported Networks -->
        <div class="card shadow-lg mb-4" style="border-top: 5px solid #2196f3;">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-network-wired me-2"></i>
                    <?php echo $lang['supported_networks'] ?? 'Supported Networks'; ?>
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div style="background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(255, 152, 0, 0.05)); padding: 15px; border-radius: 10px; border: 2px solid #ffb74d; text-align: center;">
                            <h6 style="color: #e65100; font-weight: 700; margin-bottom: 5px;">Ncell Axiata</h6>
                            <p style="color: #424242; font-size: 0.9rem; margin-bottom: 0;">SMS to 32323</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div style="background: linear-gradient(135deg, rgba(244, 67, 54, 0.1), rgba(244, 67, 54, 0.05)); padding: 15px; border-radius: 10px; border: 2px solid #ef5350; text-align: center;">
                            <h6 style="color: #c62828; font-weight: 700; margin-bottom: 5px;">Nepal Telecom</h6>
                            <p style="color: #424242; font-size: 0.9rem; margin-bottom: 0;">SMS to 32323</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(76, 175, 80, 0.05)); padding: 15px; border-radius: 10px; border: 2px solid #81c784; text-align: center;">
                            <h6 style="color: #1b5e20; font-weight: 700; margin-bottom: 5px;">Smartcell</h6>
                            <p style="color: #424242; font-size: 0.9rem; margin-bottom: 0;">SMS to 32323</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="card shadow-lg mb-4" style="border-top: 5px solid #9c27b0;">
            <div class="card-header" style="background: linear-gradient(90deg, #9c27b0, #7b1fa2); color: white;">
                <h5 class="card-title mb-0">
                    <i class="fas fa-question-circle me-2"></i>
                    <?php echo $lang['faq_title'] ?? 'Frequently Asked Questions'; ?>
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="accordion" id="faqAccordion">
                    <!-- FAQ 1 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <?php echo $lang['faq_q1'] ?? 'How much does SMS booking cost?'; ?>
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo $lang['faq_a1'] ?? 'SMS booking is completely free! You only pay for the SMS message (standard SMS rates apply based on your mobile network provider).'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <?php echo $lang['faq_q2'] ?? 'How long does it take to get a response?'; ?>
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo $lang['faq_a2'] ?? 'Usually within 1-2 minutes. During peak hours (9 AM - 5 PM), it might take up to 5 minutes.'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <?php echo $lang['faq_q3'] ?? 'Can I book for someone else via SMS?'; ?>
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo $lang['faq_a3'] ?? 'Yes! You can book for family members. During the booking process, you can provide their phone number and we will send the token to them.'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <?php echo $lang['faq_q4'] ?? 'What if I enter the wrong option?'; ?>
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo $lang['faq_a4'] ?? 'Simply send "CANCEL" to abort the current booking and start fresh with "BOOK".'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <?php echo $lang['faq_q5'] ?? 'Can I check my token status?'; ?>
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?php echo $lang['faq_a5'] ?? 'Yes! Send "STATUS" to see all your recent bookings and their current status. You will also receive SMS updates automatically.'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Section -->
        <div class="alert alert-info alert-dismissible fade show" style="background: linear-gradient(135deg, rgba(33, 150, 243, 0.12), rgba(33, 150, 243, 0.08)); border: 2px solid #2196f3; border-radius: 15px;">
            <h6 style="color: #0d47a1; font-weight: 700; margin-bottom: 10px;">
                <i class="fas fa-headset me-2"></i><?php echo $lang['need_help'] ?? 'Need More Help?'; ?>
            </h6>
            <p style="color: #1565c0; margin-bottom: 10px;">
                <?php echo $lang['contact_support'] ?? 'Our support team is available 24/7 to help you with SMS booking questions.'; ?>
            </p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="tel:+977-1-XXXXXXX" class="btn btn-sm btn-info">
                    <i class="fas fa-phone me-2"></i><?php echo $lang['call_support'] ?? 'Call Support'; ?>
                </a>
                <a href="mailto:support@smarthealth.npl" class="btn btn-sm btn-info">
                    <i class="fas fa-envelope me-2"></i><?php echo $lang['email_support'] ?? 'Email Support'; ?>
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-5 mb-4">
            <a href="/smarthealth_nepal/frontend/public/index.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-arrow-left me-2"></i><?php echo $lang['back_home'] ?? 'Back to Home'; ?>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>