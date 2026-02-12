<?php
/**
 * Health History Page
 * Shows user's health assessments and medical history
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
$profileData = $profileController->getUserProfile($userId);

if (!$profileData['success']) {
    die('Error loading profile');
}

$assessments = $profileData['assessments'];
$user = $profileData['user'];

$pageTitle = $lang['health_history'] ?? 'Health History';
$activePage = 'profile';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><i class="fas fa-history text-primary"></i> <?php echo $lang['health_history'] ?? 'Health History'; ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></p>
        </div>
    </div>
    
    <!-- Back Button & Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php"><?php echo $lang['profile'] ?? 'Profile'; ?></a></li>
                    <li class="breadcrumb-item active"><?php echo $lang['health_history'] ?? 'Health History'; ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <?php if (empty($assessments)): ?>
    <!-- Empty State -->
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm text-center">
                <div class="card-body py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5><?php echo $lang['no_history'] ?? 'No Health History'; ?></h5>
                    <p class="text-muted mb-0"><?php echo $lang['start_booking'] ?? 'Start by booking a token to build your health history'; ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    
    <!-- Assessments Timeline -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="timeline">
                <?php foreach ($assessments as $i => $assessment): ?>
                <div class="timeline-item">
                    <div class="timeline-marker bg-primary"></div>
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('M d, Y \a\t h:i A', strtotime($assessment['assessment_date'])); ?>
                                </h6>
                                <small class="text-muted"><?php echo $assessment['department_name'] ?? 'N/A'; ?></small>
                            </div>
                            <span class="badge bg-success"><?php echo $assessment['token_number'] ?? 'No Token'; ?></span>
                        </div>
                        
                        <div class="card-body">
                            <!-- Symptoms/Triage Info -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Symptoms & Signs</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if ($assessment['has_fever']): ?>
                                            <span class="badge bg-danger"><i class="fas fa-thermometer-half"></i> Fever (<?php echo $assessment['fever_days']; ?> days)</span>
                                        <?php endif; ?>
                                        <?php if ($assessment['difficulty_breathing']): ?>
                                            <span class="badge bg-danger"><i class="fas fa-lungs"></i> Difficulty Breathing</span>
                                        <?php endif; ?>
                                        <?php if ($assessment['has_injury']): ?>
                                            <span class="badge bg-warning"><i class="fas fa-bone"></i> Injury (<?php echo $assessment['injury_severity']; ?>)</span>
                                        <?php endif; ?>
                                        <?php if ($assessment['is_pregnant']): ?>
                                            <span class="badge bg-info"><i class="fas fa-heart"></i> Pregnancy</span>
                                        <?php endif; ?>
                                        <?php if ($assessment['has_chronic_disease'] && !empty($assessment['chronic_disease_types'])): ?>
                                            <?php foreach ($assessment['chronic_disease_types'] as $disease): ?>
                                                <span class="badge bg-secondary"><i class="fas fa-pills"></i> <?php echo htmlspecialchars($disease); ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php if ($assessment['has_emergency_signs']): ?>
                                            <span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Emergency Signs</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Location Info -->
                            <?php if ($assessment['assessment_district'] || $assessment['assessment_municipality']): ?>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Location</h6>
                                    <p class="mb-0">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                        <?php 
                                            $location = [];
                                            if ($assessment['assessment_ward']) $location[] = 'Ward ' . $assessment['assessment_ward'];
                                            if ($assessment['assessment_municipality']) $location[] = $assessment['assessment_municipality'];
                                            if ($assessment['assessment_district']) $location[] = $assessment['assessment_district'];
                                            echo htmlspecialchars(implode(', ', $location));
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Hospital Info -->
                            <?php if ($assessment['hospital_name'] || $assessment['hospital_id']): ?>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Hospital</h6>
                                    <p class="mb-0">
                                        <i class="fas fa-hospital text-info"></i>
                                        <?php echo htmlspecialchars($assessment['hospital_name'] ?? 'Hospital ID: ' . $assessment['hospital_id']); ?>
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Additional Notes -->
                            <?php if ($assessment['additional_notes']): ?>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.85rem;">Notes</h6>
                                    <p class="mb-0 text-wrap" style="word-break: break-word;">
                                        <?php echo htmlspecialchars($assessment['additional_notes']); ?>
                                    </p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
    
    <!-- Actions -->
    <div class="row mt-4 mb-4">
        <div class="col-12 text-center">
            <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Book New Token
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
    }
    
    .timeline-item {
        position: relative;
        padding-left: 40px;
        margin-bottom: 2rem;
    }
    
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 16px;
        top: 30px;
        bottom: -30px;
        width: 2px;
        background-color: #dee2e6;
    }
    
    .timeline-marker {
        position: absolute;
        left: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 2px #dee2e6;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
