<?php
/**
 * Admin - Reschedule Token
 * Reschedule missed or cancelled tokens
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../admin/backend/controllers/DashboardController.php';
require_once __DIR__ . '/../../../admin/backend/models/TokenModel.php';

// Check admin login
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /smarthealth_nepal/admin/public/index.php?page=login');
    exit;
}

// Load admin language
$lang_file = __DIR__ . '/../../../admin/backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

$token_id = $_GET['token_id'] ?? null;
$message = '';

if (!$token_id) {
    header('Location: active.php');
    exit;
}

$tokenModel = new TokenModel($db);
$token = $tokenModel->getTokenById($token_id);

if (!$token) {
    $message = '<div class="alert alert-danger">Token not found</div>';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_date = $_POST['new_date'] ?? '';
    $new_time = $_POST['new_time'] ?? '';
    
    if ($new_date && $new_time) {
        $new_datetime = $new_date . ' ' . $new_time . ':00';
        $result = $tokenModel->rescheduleToken($token_id, $new_datetime);
        
        if ($result['success']) {
            $message = '<div class="alert alert-success">Token rescheduled successfully</div>';
            $token = $tokenModel->getTokenById($token_id);
        } else {
            $message = '<div class="alert alert-danger">Failed to reschedule token</div>';
        }
    }
}

$pageTitle = $lang['reschedule_token'] ?? 'Reschedule Token';
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar"></i> <?php echo $lang['reschedule_token'] ?? 'Reschedule Token'; ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <?php if ($token): ?>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><?php echo $lang['token_information'] ?? 'Token Information'; ?></h6>
                            <dl class="row">
                                <dt class="col-sm-4"><?php echo $lang['token'] ?? 'Token'; ?></dt>
                                <dd class="col-sm-8"><strong><?php echo $token['token_number']; ?></strong></dd>
                                
                                <dt class="col-sm-4"><?php echo $lang['patient'] ?? 'Patient'; ?></dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($token['name']); ?></dd>
                                
                                <dt class="col-sm-4"><?php echo $lang['phone'] ?? 'Phone'; ?></dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($token['phone']); ?></dd>
                                
                                <dt class="col-sm-4"><?php echo $lang['department'] ?? 'Department'; ?></dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($token['dept_name']); ?></dd>
                                
                                <dt class="col-sm-4"><?php echo $lang['status'] ?? 'Status'; ?></dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-danger"><?php echo $token['status']; ?></span>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h6><?php echo $lang['current_schedule'] ?? 'Current Schedule'; ?></h6>
                            <p>
                                <strong><?php echo date('d M Y H:i', strtotime($token['created_at'])); ?></strong>
                            </p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6><?php echo $lang['new_date_time'] ?? 'New Date & Time'; ?></h6>
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $lang['new_date'] ?? 'New Date'; ?></label>
                                <input type="date" name="new_date" class="form-control" required 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo $lang['time'] ?? 'Time'; ?></label>
                                <input type="time" name="new_time" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-sm-flex">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $lang['reschedule'] ?? 'Reschedule'; ?>
                            </button>
                            <a href="active.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> <?php echo $lang['back'] ?? 'Back'; ?>
                            </a>
                        </div>
                    </form>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
