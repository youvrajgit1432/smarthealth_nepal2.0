<?php
/**
 * Token Status Tracking Page
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/TokenController.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';

$authController = new AuthController($db);
if (!$authController->isLoggedIn()) {
    header('Location: /smarthealth_nepal/frontend/views/auth/login.php');
    exit;
}

$tokenController = new TokenController($db);
$userId = $_SESSION['user_id'];

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Get token status
$status = $tokenController->getTokenStatus($userId);

$pageTitle = $lang['track_your_status'] ?? 'Track Token Status';
$activePage = 'status';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        
        <?php if (!$status['success']): ?>
        
        <div class="alert alert-warning">
            <h5><?php echo $lang['no_active_tokens'] ?? 'No active tokens'; ?></h5>
            <p><?php echo $status['message'] ?? ''; ?></p>
            <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> <?php echo $lang['book_new_token'] ?? 'Book New Token'; ?>
            </a>
        </div>
        
        <?php else: ?>
        
        <?php 
        $token = $status['token'];
        $load = $status['department_load'];
        $position = $status['queue_position'];
        
        // Get hospital information if hospital_id exists
        $hospitalInfo = null;
        if (!empty($token['hospital_id'])) {
            $hospitalResult = $db->query("SELECT hl.id, hl.hospital_name, hl.district, hl.municipality, hl.phone, hl.address 
                                         FROM hospital_locations hl 
                                         WHERE hl.id = " . intval($token['hospital_id']) . " LIMIT 1");
            if ($hospitalResult && $hospitalResult->num_rows > 0) {
                $hospitalInfo = $hospitalResult->fetch_assoc();
            }
        }
        
        // Status badge color
        $statusColor = $token['status'] === 'Called' ? 'danger' : 'success';
        
        // Priority color
        $priorityColor = match($token['priority']) {
            'Emergency' => 'danger',
            'Priority' => 'warning',
            'Chronic' => 'info',
            default => 'secondary'
        };
        ?>
        
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-search"></i>
                    <?php echo $lang['token_status'] ?? 'Token Status'; ?>
                </h5>
            </div>
            
            <div class="card-body p-4">
                
                <!-- Token Number -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['your_token'] ?? 'Your Token'; ?></h6>
                        <h1 class="text-primary" style="font-size: 3rem;">
                            <?php echo str_pad($token['token_number'], 3, '0', STR_PAD_LEFT); ?>
                        </h1>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['status'] ?? 'Status'; ?></h6>
                        <p>
                            <span class="badge bg-<?php echo $statusColor; ?> p-2" style="font-size: 1rem;">
                                <?php 
                                echo match($token['status']) {
                                    'Active' => $lang['status_active'] ?? 'Active - In Queue',
                                    'Called' => $lang['status_called'] ?? 'Called - Please Proceed',
                                    'Completed' => $lang['status_completed'] ?? 'Completed',
                                    default => $token['status']
                                };
                                ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <!-- Department Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['department'] ?? 'Department'; ?></h6>
                        <p class="h5"><?php echo $token['department_name']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['priority_level'] ?? 'Priority'; ?></h6>
                        <p>
                            <span class="badge bg-<?php echo $priorityColor; ?> p-2">
                                <?php echo $token['priority']; ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <!-- Hospital Info -->
                <?php if ($hospitalInfo): ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted"><i class="fas fa-hospital"></i> <?php echo $lang['hospital_name'] ?? 'Hospital'; ?></h6>
                        <p class="h5"><?php echo htmlspecialchars($hospitalInfo['hospital_name']); ?></p>
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($hospitalInfo['district']); ?>
                        </small>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted"><i class="fas fa-phone"></i> <?php echo $lang['contact'] ?? 'Contact'; ?></h6>
                        <p class="h5"><?php echo htmlspecialchars($hospitalInfo['phone'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Queue Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['people_ahead'] ?? 'People Ahead'; ?></h6>
                        <p class="h4"><?php echo $position; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted"><?php echo $lang['estimated_wait'] ?? 'Estimated Wait'; ?></h6>
                        <p class="h4"><?php echo $token['estimated_wait_time']; ?> <?php echo $lang['minutes'] ?? 'min'; ?></p>
                    </div>
                </div>
                
                <!-- Department Load -->
                <div class="mb-4">
                    <h6 class="text-muted"><?php echo $lang['current_load'] ?? 'Department Load'; ?></h6>
                    <div class="d-flex align-items-center">
                        <progress value="<?php echo $load['percentage']; ?>" max="100" style="width: 100%; height: 30px;">
                        </progress>
                        <span class="ms-3 badge 
                            bg-<?php echo $load['load'] === 'High' ? 'danger' : 
                                        ($load['load'] === 'Moderate' ? 'warning' : 'success'); ?> p-2">
                            <?php echo $load['load']; ?> (<?php echo $load['percentage']; ?>%)
                        </span>
                    </div>
                    <small class="text-muted">
                        <?php echo $load['active_count'] . ' / ' . $load['max_capacity']; ?> 
                        <?php echo $lang['patients'] ?? 'patients'; ?>
                    </small>
                </div>
                
                <!-- Status Message -->
                <?php if ($token['status'] === 'Called'): ?>
                <div class="alert alert-danger">
                    <h6><?php echo $lang['status_called'] ?? 'CALLED'; ?></h6>
                    <p><?php echo 'Please proceed to the counter immediately'; ?></p>
                </div>
                <?php elseif ($token['status'] === 'Active'): ?>
                <div class="alert alert-info">
                    <h6><?php echo $lang['your_position'] ?? 'Your Position'; ?></h6>
                    <p><?php echo 'You are #' . ($position + 1) . ' in the queue'; ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Time Info -->
                <div class="alert alert-light">
                    <small class="text-muted">
                        <?php echo $lang['booked'] ?? 'Booked'; ?>: <?php echo date('H:i - d M Y', strtotime($token['created_at'])); ?>
                    </small>
                </div>
            </div>
            
            <div class="card-footer p-3">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> <?php echo $lang['refresh'] ?? 'Refresh'; ?>
                    </button>
                    <a href="/smarthealth_nepal/frontend/views/home/" class="btn btn-secondary">
                        <i class="fas fa-home"></i> <?php echo $lang['go_home'] ?? 'Go Home'; ?>
                    </a>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php';?>
