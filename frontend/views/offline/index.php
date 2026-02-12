<?php
/**
 * Offline Booking Hub
 * Provides options for users with limited internet access or who need staff assistance
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set language
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load backend
require_once __DIR__ . '/../../../backend/init.php';

// Load language
$lang = [];
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

$pageTitle = $lang['offline_booking'] ?? 'Offline Booking';
$activePage = 'offline';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4 mb-5">
    <div class="col-lg-10">
        
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold mb-3" style="color: #1565c0;">
                <i class="fas fa-wifi"></i> <?php echo $lang['offline_booking_title'] ?? 'Offline & Assisted Booking'; ?>
            </h1>
            <p class="lead text-muted" style="font-size: 1.1rem;">
                <?php echo $lang['offline_subtitle'] ?? 'Multiple ways to book your healthcare appointment'; ?>
            </p>
        </div>

        <!-- Booking Options Grid -->
        <div class="row g-4 mb-5">
            
            <!-- Option 1: Staff-Assisted Booking -->
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm border-0" style="border-top: 5px solid #4caf50; transition: all 0.3s;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div style="width: 80px; height: 80px; margin: 0 auto; background: rgba(76,175,80,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-hands-helping" style="font-size: 2.5rem; color: #2e7d32;"></i>
                            </div>
                        </div>
                        
                        <h5 class="card-title text-center mb-3" style="color: #1565c0; font-weight: 700;">
                            <?php echo $lang['assisted_booking'] ?? 'Assisted Booking'; ?>
                        </h5>
                        
                        <p class="card-text text-muted mb-4" style="font-size: 0.95rem;">
                            <?php echo $lang['assisted_desc'] ?? 'Staff members at the hospital will help you book an appointment. Perfect for elderly patients or those unfamiliar with technology.'; ?>
                        </p>

                        <div class="alert alert-light mb-4">
                            <h6 class="mb-3" style="color: #1565c0; font-weight: 600;">
                                <i class="fas fa-check-circle me-2"></i><?php echo $lang['assisted_benefits'] ?? 'Benefits'; ?>:
                            </h6>
                            <ul class="small mb-0">
                                <li><?php echo $lang['assisted_benefit_1'] ?? 'Personalized assistance'; ?></li>
                                <li><?php echo $lang['assisted_benefit_2'] ?? 'No technical knowledge needed'; ?></li>
                                <li><?php echo $lang['assisted_benefit_3'] ?? 'Immediate confirmation'; ?></li>
                                <li><?php echo $lang['assisted_benefit_4'] ?? 'Direct help from staff'; ?></li>
                            </ul>
                        </div>

                        <a href="/smarthealth_nepal/frontend/views/offline/assisted_booking.php" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-arrow-right me-2"></i><?php echo $lang['visit_hospital'] ?? 'Visit Hospital'; ?>
                        </a>
                        
                        <p class="small text-muted text-center mt-3 mb-0">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            <?php echo $lang['available_all_hospitals'] ?? 'Available at all our hospitals'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Option 2: SMS Booking -->
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm border-0" style="border-top: 5px solid #2196f3; transition: all 0.3s;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div style="width: 80px; height: 80px; margin: 0 auto; background: rgba(33,150,243,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-sms" style="font-size: 2.5rem; color: #0d47a1;"></i>
                            </div>
                        </div>
                        
                        <h5 class="card-title text-center mb-3" style="color: #1565c0; font-weight: 700;">
                            <?php echo $lang['sms_booking'] ?? 'SMS Booking'; ?>
                        </h5>
                        
                        <p class="card-text text-muted mb-4" style="font-size: 0.95rem;">
                            <?php echo $lang['sms_desc'] ?? 'Book your appointment using SMS text messages. Works on any phone, no internet required!'; ?>
                        </p>

                        <div class="alert alert-light mb-4">
                            <h6 class="mb-3" style="color: #1565c0; font-weight: 600;">
                                <i class="fas fa-check-circle me-2"></i><?php echo $lang['sms_benefits'] ?? 'Benefits'; ?>:
                            </h6>
                            <ul class="small mb-0">
                                <li><?php echo $lang['sms_benefit_1'] ?? 'Works on any phone (no internet needed)'; ?></li>
                                <li><?php echo $lang['sms_benefit_2'] ?? 'Simple text commands'; ?></li>
                                <li><?php echo $lang['sms_benefit_3'] ?? 'Instant confirmation via SMS'; ?></li>
                                <li><?php echo $lang['sms_benefit_4'] ?? 'Track token via SMS updates'; ?></li>
                            </ul>
                        </div>

                        <a href="/smarthealth_nepal/frontend/views/offline/sms_booking.php" class="btn btn-info btn-lg w-100">
                            <i class="fas fa-arrow-right me-2"></i><?php echo $lang['send_sms'] ?? 'Learn SMS Commands'; ?>
                        </a>
                        
                        <p class="small text-muted text-center mt-3 mb-0">
                            <i class="fas fa-phone me-1"></i>
                            <?php echo $lang['sms_number_label'] ?? 'SMS to'; ?>: <strong>9801XXXXX</strong>
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Online Booking Option -->
        <div class="card shadow-sm border-0" style="border-top: 5px solid #ff9800; background: linear-gradient(135deg, rgba(255,152,0,0.05), rgba(255,152,0,0.02));">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="card-title mb-2" style="color: #1565c0; font-weight: 700;">
                            <i class="fas fa-globe me-2" style="color: #e65100;"></i>
                            <?php echo $lang['online_booking'] ?? 'Online Booking'; ?>
                        </h5>
                        <p class="card-text text-muted mb-0">
                            <?php echo $lang['online_desc'] ?? 'If you have internet access, you can book your token directly from our mobile app or website. Faster and easier!'; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-warning btn-lg">
                            <i class="fas fa-desktop me-2"></i><?php echo $lang['book_online'] ?? 'Book Online'; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparison Table -->
        <div class="mt-5 pt-4">
            <h3 class="mb-4" style="color: #1565c0; font-weight: 700;">
                <i class="fas fa-table me-2"></i><?php echo $lang['booking_comparison'] ?? 'Quick Comparison'; ?>
            </h3>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background: linear-gradient(90deg, #e3f2fd, #eef7f5); border-bottom: 3px solid #1565c0;">
                        <tr>
                            <th style="color: #1565c0; font-weight: 700;">
                                <?php echo $lang['feature'] ?? 'Feature'; ?>
                            </th>
                            <th style="color: #1565c0; font-weight: 700; text-align: center;">
                                <i class="fas fa-hands-helping me-1"></i><?php echo $lang['assisted'] ?? 'Assisted'; ?>
                            </th>
                            <th style="color: #1565c0; font-weight: 700; text-align: center;">
                                <i class="fas fa-sms me-1"></i><?php echo $lang['sms'] ?? 'SMS'; ?>
                            </th>
                            <th style="color: #1565c0; font-weight: 700; text-align: center;">
                                <i class="fas fa-desktop me-1"></i><?php echo $lang['online'] ?? 'Online'; ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php echo $lang['internet_required'] ?? 'Internet Required'; ?></strong></td>
                            <td class="text-center"><i class="fas fa-times text-danger fa-lg"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger fa-lg"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success fa-lg"></i></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $lang['support_available'] ?? 'Support Available'; ?></strong></td>
                            <td class="text-center"><i class="fas fa-check text-success fa-lg"></i></td>
                            <td class="text-center"><i class="fas fa-times text-warning fa-lg"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success fa-lg"></i></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $lang['instant_confirmation'] ?? 'Instant Confirmation'; ?></strong></td>
                            <td class="text-center"><i class="fas fa-check text-success fa-lg"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success fa-lg"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success fa-lg"></i></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $lang['sms_updates'] ?? 'SMS Updates'; ?></strong></td>
                            <td class="text-center"><i class="fas fa-check text-success fa-lg"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success fa-lg"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success fa-lg"></i></td>
                        </tr>
                        <tr>
                            <td><strong><?php echo $lang['speed'] ?? 'Speed'; ?></strong></td>
                            <td class="text-center"><span class="badge bg-success">Fast</span></td>
                            <td class="text-center"><span class="badge bg-warning">~1-2 min</span></td>
                            <td class="text-center"><span class="badge bg-info">Instant</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Help Section -->
        <div class="alert alert-light mt-5 p-4">
            <h5 class="mb-3" style="color: #1565c0; font-weight: 700;">
                <i class="fas fa-question-circle me-2"></i><?php echo $lang['need_help'] ?? 'Need Help?'; ?>
            </h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <p class="mb-2">
                        <i class="fas fa-phone text-primary me-2"></i>
                        <strong><?php echo $lang['call_us'] ?? 'Call Us'; ?>:</strong> +977-1-XXXXXXX
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <p class="mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <strong><?php echo $lang['email_us'] ?? 'Email'; ?>:</strong> info@smarthealth.npl
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <p class="mb-2">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <strong><?php echo $lang['visit_us'] ?? 'Visit'; ?>:</strong> <?php echo $lang['any_hospital'] ?? 'Any SmartHealth Hospital'; ?>
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <p class="mb-2">
                        <i class="fas fa-clock text-primary me-2"></i>
                        <strong><?php echo $lang['hours'] ?? 'Hours'; ?>:</strong> 24/7 <?php echo $lang['support'] ?? 'Support'; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-5">
            <a href="/smarthealth_nepal/frontend/public/index.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-home me-2"></i><?php echo $lang['go_home'] ?? 'Go Home'; ?>
            </a>
        </div>
    </div>
</div>

<style>
    .card {
        transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15) !important;
    }
    
    .btn:hover {
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.9rem;
        }
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
