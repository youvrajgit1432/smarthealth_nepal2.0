<?php
/**
 * Token Booking Confirmation Page
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../../backend/helpers/TimeHelper.php';

$authController = new AuthController($db);
// Allow confirmation page even if not logged in (just completed booking)
// if (!$authController->isLoggedIn()) {
//     header('Location: /smarthealth_nepal/frontend/views/home/');
//     exit;
// }

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Get token data
$tokenData = $_SESSION['token_data'] ?? null;

if (!$tokenData || !isset($tokenData['token'])) {
    header('Location: /smarthealth_nepal/frontend/views/token/book.php');
    exit;
}

$token = $tokenData['token'];
$dept = $tokenData['department'];

$pageTitle = $lang['token_booked'] ?? 'Token Confirmed';
$activePage = 'confirmation';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card shadow-lg border-success">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $lang['congratulations'] ?? 'Congratulations!'; ?>
                </h5>
            </div>
            
            <div class="card-body p-5 text-center">
                <h2><?php echo $lang['token_booked'] ?? 'Your token has been booked successfully!'; ?></h2>
                
                <div class="row my-5">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="text-muted"><?php echo $lang['your_token'] ?? 'Token Number'; ?></h6>
                                <h1 class="text-primary" style="font-size: 3rem;">
                                    <?php echo str_pad($token['token_number'], 3, '0', STR_PAD_LEFT); ?>
                                </h1>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-body">
                                <h6 class="text-muted"><?php echo $lang['estimated_wait'] ?? 'Estimated Wait Time'; ?></h6>
                                <h2 class="text-info"><?php echo TimeHelper::formatWaitTime($token['estimated_wait_time']); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['department'] ?? 'Department'; ?></h6>
                        <p class="h5"><?php echo $dept['name']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['priority_level'] ?? 'Priority'; ?></h6>
                        <p class="h5">
                            <span class="badge 
                                bg-<?php echo $token['priority'] === 'Emergency' ? 'danger' : 
                                            ($token['priority'] === 'Priority' ? 'warning' : 
                                            ($token['priority'] === 'Chronic' ? 'info' : 'success')); ?>">
                                <?php echo $token['priority']; ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <?php if ($token['priority'] === 'Emergency'): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $lang['emergency_notice'] ?? 'Emergency case - You will be seen immediately'; ?>
                </div>
                <?php elseif ($token['priority'] === 'Priority'): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    <?php echo $lang['priority_notice'] ?? 'High priority - You will be called soon'; ?>
                </div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <h6><?php echo $lang['important_notes'] ?? 'Important Notes'; ?></h6>
                    <ul class="text-start">
                        <li><?php echo $lang['keep_phone'] ?? 'Keep your phone ready for SMS updates'; ?></li>
                        <li><?php echo $lang['arrive_hospital'] ?? 'Arrive at hospital before estimated time'; ?></li>
                        <li><?php echo $lang['bring_documents'] ?? 'Bring relevant medical documents'; ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="card-footer text-center p-3">
                <p class="mb-3"><?php echo $lang['sms_sent'] ?? 'SMS sent to your phone with token details'; ?></p>
                
                <div class="d-grid gap-2">
                    <a href="/smarthealth_nepal/frontend/views/token/status.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-search"></i> <?php echo $lang['track_your_status'] ?? 'Track Token'; ?>
                    </a>
                    <a href="/smarthealth_nepal/frontend/views/home/" class="btn btn-secondary btn-lg">
                        <i class="fas fa-home"></i> <?php echo $lang['go_home'] ?? 'Go Home'; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Clear booking data
unset($_SESSION['booking_success']);
unset($_SESSION['token_data']);
?>

<?php require_once __DIR__ . '/../layouts/footer.php';?>
