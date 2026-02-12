<?php
/**
 * Emergency Services Page
 */

require_once __DIR__ . '/../../../backend/init.php';

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

$pageTitle = $lang['emergency'] ?? 'Emergency Services';
$activePage = 'emergency';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card shadow-lg border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-ambulance"></i>
                    <?php echo $lang['emergency_care'] ?? 'Emergency Services'; ?>
                </h5>
            </div>
            
            <div class="card-body p-4">
                <div class="alert alert-danger">
                    <h5>If you have an emergency:</h5>
                    <ul>
                        <li>Go directly to the Emergency Department at your nearest hospital</li>
                        <li>Do not wait for a token</li>
                        <li>Inform staff about your condition immediately</li>
                    </ul>
                </div>
                
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Emergency Symptoms:</h6>
                        <ul>
                            <li>Severe chest pain</li>
                            <li>Difficulty breathing</li>
                            <li>Unconsciousness</li>
                            <li>Severe bleeding</li>
                            <li>Severe allergic reaction</li>
                            <li>Severe burns</li>
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <strong>Emergency Contact:</strong><br>
                    Ambulance: 102<br>
                    Police: 100<br>
                    Fire: 101
                </div>
            </div>
            
            <div class="card-footer">
                <a href="/smarthealth_nepal/frontend/views/home/" class="btn btn-primary">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php';?>