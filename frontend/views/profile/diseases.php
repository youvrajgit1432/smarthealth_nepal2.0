<?php
/**
 * My Conditions / Diseases Page
 * Shows user's chronic diseases and health conditions
 */

// Set language if switching
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load backend
require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/UserProfileController.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';

// Initialize controllers
$authController = new AuthController($db);
$profileController = new UserProfileController($db);
$lang = [];

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Check login
if (!$authController->isLoggedIn()) {
    header('Location: /smarthealth_nepal/frontend/views/auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = $authController->getCurrentUser();
$chronicDiseases = $profileController->getUserChronicDiseases($userId);

$pageTitle = $lang['my_conditions'] ?? 'My Conditions';
$activePage = 'profile';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><i class="fas fa-notes-medical text-success"></i> <?php echo $lang['my_conditions'] ?? 'My Conditions'; ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></p>
        </div>
    </div>
    
    <!-- Back Button & Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php"><?php echo $lang['profile'] ?? 'Profile'; ?></a></li>
                    <li class="breadcrumb-item active"><?php echo $lang['my_conditions'] ?? 'My Conditions'; ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <?php if (empty($chronicDiseases)): ?>
    <!-- Empty State -->
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm text-center">
                <div class="card-body py-5">
                    <i class="fas fa-heart fa-3x text-success mb-3"></i>
                    <h5><?php echo $lang['no_conditions'] ?? 'No Chronic Conditions'; ?></h5>
                    <p class="text-muted mb-0"><?php echo $lang['healthy_status'] ?? 'You don\'t have any recorded chronic conditions'; ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    
    <!-- Conditions List -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <?php foreach ($chronicDiseases as $disease): ?>
            <div class="card shadow-sm mb-3 border-left-success">
                <div class="card-header bg-light d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-pills text-success"></i>
                            <?php echo htmlspecialchars($disease['disease_name']); ?>
                        </h5>
                        <small class="text-muted">Code: <?php echo htmlspecialchars($disease['disease_code'] ?? 'N/A'); ?></small>
                    </div>
                    <span class="badge bg-success">Active</span>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Diagnosis Date</h6>
                            <p class="mb-0">
                                <i class="fas fa-calendar text-primary"></i>
                                <?php echo $disease['diagnosis_date'] ? date('M d, Y', strtotime($disease['diagnosis_date'])) : 'Not specified'; ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Last Visit</h6>
                            <p class="mb-0">
                                <i class="fas fa-check-circle text-success"></i>
                                <?php echo $disease['last_visit_date'] ? date('M d, Y', strtotime($disease['last_visit_date'])) : 'Never'; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Next Follow-up</h6>
                            <p class="mb-0">
                                <?php 
                                    $nextFollowup = strtotime($disease['next_followup_date']);
                                    $today = time();
                                    $daysUntil = ($nextFollowup - $today) / (24 * 60 * 60);
                                    
                                    if ($daysUntil < 0) {
                                        echo '<span class="badge bg-danger"><i class="fas fa-exclamation-circle"></i> Overdue by ' . abs(ceil($daysUntil)) . ' days</span>';
                                    } elseif ($daysUntil < 7) {
                                        echo '<span class="badge bg-warning"><i class="fas fa-clock"></i> Due in ' . ceil($daysUntil) . ' days</span>';
                                    } else {
                                        echo '<i class="fas fa-calendar-alt text-primary"></i> ' . date('M d, Y', $nextFollowup);
                                    }
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if (!empty($disease['doctor_notes'])): ?>
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Doctor's Notes</h6>
                            <p class="mb-0 text-wrap" style="word-break: break-word;">
                                <?php echo htmlspecialchars($disease['doctor_notes']); ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($disease['medications'])): ?>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Current Medications</h6>
                            <div class="alert alert-info mb-0">
                                <small><?php echo htmlspecialchars($disease['medications']); ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php endif; ?>
    
    <!-- Actions -->
    <div class="row mt-4 mb-4">
        <div class="col-12 text-center">
            <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Book Check-up
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>
</div>

<style>
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
