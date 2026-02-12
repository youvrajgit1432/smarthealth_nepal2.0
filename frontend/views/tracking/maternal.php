<?php
/**
 * Maternal Health Tracking Page
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';

$authController = new AuthController($db);
if (!$authController->isLoggedIn()) {
    header('Location: /smarthealth_nepal/frontend/views/auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Load language
$lang_file = __DIR__ . '/../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Get maternal health data from database
$result = $db->query("SELECT * FROM maternal_health WHERE user_id = $userId LIMIT 1");
$maternal = $result->fetch_assoc();

$pageTitle = $lang['maternal_health'] ?? 'Maternal Health Tracking';
$activePage = 'maternal';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-baby"></i>
                    <?php echo $lang['pregnancy_status'] ?? 'Maternal Health Tracking'; ?>
                </h5>
            </div>
            
            <div class="card-body p-4">
                
                <?php if (!$maternal): ?>
                <div class="alert alert-info text-center">
                    <p><?php echo $lang['not_pregnant'] ?? 'No pregnancy data registered'; ?></p>
                    <p class="text-muted">If you are pregnant, please update your information with hospital staff.</p>
                </div>
                <?php else: ?>
                
                <!-- Status -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['pregnancy_status'] ?? 'Status'; ?></h6>
                        <p class="h5">
                            <span class="badge bg-success p-2">
                                <?php echo $maternal['pregnancy_status']; ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['weeks_pregnant'] ?? 'Weeks Pregnant'; ?></h6>
                        <p class="h5">
                            <?php 
                            if ($maternal['last_menstrual_period']) {
                                $lmp = new DateTime($maternal['last_menstrual_period']);
                                $today = new DateTime();
                                $weeks = (int)($lmp->diff($today)->days / 7);
                                echo $weeks . ' weeks';
                            } else {
                                echo 'Not available';
                            }
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- Due Date -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['expected_due_date'] ?? 'Expected Due Date'; ?></h6>
                        <p class="h5"><?php echo date('M d, Y', strtotime($maternal['expected_due_date'])); ?></p>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['days_remaining'] ?? 'Days Remaining'; ?></h6>
                        <p class="h5">
                            <?php 
                            $daysLeft = (int)((strtotime($maternal['expected_due_date']) - time()) / 86400);
                            echo $daysLeft . ' days';
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- Antenatal -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['antenatal_visits'] ?? 'Antenatal Visits'; ?></h6>
                        <p class="h5"><?php echo $maternal['antenatal_visits_completed']; ?> completed</p>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['next_checkup'] ?? 'Next Checkup'; ?></h6>
                        <p class="h5"><?php echo date('M d, Y', strtotime($maternal['next_antenatal_date'])); ?></p>
                    </div>
                </div>
                
                <!-- High Risk Alert -->
                <?php if ($maternal['is_high_risk']): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong><?php echo $lang['high_risk_pregnancy'] ?? 'High-Risk Pregnancy'; ?></strong>
                    <p><?php echo $lang['seek_immediate'] ?? 'Seek immediate medical attention if experiencing complications'; ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Vaccination Status -->
                <?php if ($maternal['vaccinations_needed']): ?>
                <div class="alert alert-warning">
                    <h6><?php echo $lang['vaccinations_due'] ?? 'Vaccinations Due'; ?></h6>
                    <ul>
                        <?php 
                        $vaccinations = json_decode($maternal['vaccinations_needed'], true);
                        foreach ($vaccinations as $vax) {
                            echo '<li>' . $vax . '</li>';
                        }
                        ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Reminder Alert -->
                <div class="alert alert-info">
                    <p><?php echo $lang['maternal_alert'] ?? 'Please ensure regular checkups for safe pregnancy'; ?></p>
                </div>
                
                <?php endif; ?>
            </div>
            
            <div class="card-footer p-3">
                <div class="d-grid gap-2">
                    <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> <?php echo $lang['book_followup'] ?? 'Book Antenatal Checkup'; ?>
                    </a>
                    <a href="/smarthealth_nepal/frontend/views/home/" class="btn btn-secondary">
                        <i class="fas fa-home"></i> <?php echo $lang['go_home'] ?? 'Go Home'; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php';?>
