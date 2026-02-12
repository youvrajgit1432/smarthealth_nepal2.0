<?php
/**
 * Home Page
 */

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

$pageTitle = $lang['welcome'] ?? 'SmartHealth Nepal';
$activePage = 'home';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="hero-section bg-primary text-white py-5 rounded">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h2><?php echo $lang['hero_title'] ?? 'Healthcare Made Simple'; ?></h2>
            <p><?php echo $lang['hero_subtitle'] ?? 'Efficient, transparent, and patient-focused'; ?></p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="<?php echo __DIR__; ?>/auth/login.php" class="btn btn-light btn-lg">
                <i class="fas fa-sign-in-alt"></i> <?php echo $lang['login'] ?? 'Login'; ?>
            </a>
            <?php else: ?>
            <a href="<?php echo __DIR__; ?>/token/book.php" class="btn btn-light btn-lg">
                <i class="fas fa-plus-circle"></i> <?php echo $lang['book_token'] ?? 'Book Token'; ?>
            </a>
            <?php endif; ?>
        </div>
        <div class="col-md-6 text-center">
            <i class="fas fa-hospital" style="font-size: 5rem; opacity: 0.8;"></i>
        </div>
    </div>
</div>

<section class="features py-5 mt-5">
    <h2 class="text-center mb-5"><?php echo $lang['features_title'] ?? 'Why Choose SmartHealth Nepal?'; ?></h2>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-stethoscope fa-3x text-primary mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_1_title'] ?? 'Smart Triage'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_1_desc'] ?? 'Quick health assessment'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-list-check fa-3x text-primary mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_2_title'] ?? 'Live Queue'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_2_desc'] ?? 'Track your position'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-heartbeat fa-3x text-primary mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_3_title'] ?? 'Chronic Care'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_3_desc'] ?? 'Follow-up reminders'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-baby fa-3x text-success mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_4_title'] ?? 'Maternal Health'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_4_desc'] ?? 'Pregnancy tracking'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-wifi fa-3x text-info mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_5_title'] ?? 'Offline Support'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_5_desc'] ?? 'Staff-assisted booking'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-sms fa-3x text-warning mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_6_title'] ?? 'SMS Updates'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_6_desc'] ?? 'No internet needed'; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about py-5 bg-light rounded p-4">
    <h2><?php echo $lang['about_us'] ?? 'About SmartHealth Nepal'; ?></h2>
    <p>
        SmartHealth Nepal is a sustainable digital patient flow and chronic care tracking system designed for 
        government hospitals in Nepal. It addresses overcrowding, improves emergency response, and provides 
        continuous chronic disease monitoring with SMS reminders for patients without reliable internet access.
    </p>
    <p class="text-muted">
        Built for the NIST Tech Carnival 2.0 Hackathon | Team CIVIX
    </p>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php';?>
