<?php
/**
 * Services Directory Index
 * Redirects to department list
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set language if switching
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load language
$langFile = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($langFile)) {
    require_once $langFile;
} else {
    require_once __DIR__ . '/../../../backend/lang/en.php';
}

$pageTitle = $lang['services'] ?? 'Services';
$activePage = 'services';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="mb-4"><i class="fas fa-stethoscope"></i> <?php echo $lang['services'] ?? 'Services'; ?></h2>
            
            <div class="row">
                <!-- Department Services -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-hospital"></i> <?php echo $lang['services'] ?? 'Services'; ?></h5>
                            <p class="card-text">Browse our hospital departments and services available.</p>
                            <a href="department_list.php" class="btn btn-primary">View Departments</a>
                        </div>
                    </div>
                </div>

                <!-- Emergency Services -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-ambulance"></i> <?php echo $lang['emergency'] ?? 'Emergency'; ?></h5>
                            <p class="card-text">Urgent and emergency medical care available 24/7.</p>
                            <a href="emergency.php" class="btn btn-danger">Emergency Info</a>
                        </div>
                    </div>
                </div>

                <!-- Health Education -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-book-medical"></i> Health Education</h5>
                            <p class="card-text">Learn about health tips and disease prevention.</p>
                            <a href="health_education.php" class="btn btn-info">Education Resources</a>
                        </div>
                    </div>
                </div>

                <!-- Referral Services -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-arrow-right-arrow-left"></i> Referral Service</h5>
                            <p class="card-text">Get referrals to specialized departments and hospitals.</p>
                            <a href="referral.php" class="btn btn-success">Referral Info</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
