<?php
/**
 * Admin - Active Token Management
 * Display and manage active tokens in the queue
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/DashboardController.php';
require_once __DIR__ . '/../../../backend/models/TokenModel.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

// Ensure language is loaded
if (!isset($lang)) {
    $lang = [];
}

global $db;
$dashboardController = new DashboardController($db);
$activeQueue = $dashboardController->getActiveQueue(50);

$pageTitle = $lang['active_tokens'] ?? 'Active Tokens';
$activePage = 'token_management';
?>

<?php require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> <?php echo $lang['active_tokens'] ?? 'Active Tokens'; ?>
                    </h5>
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($activeQueue)): ?>
                    <div class="alert alert-info m-3">
                        <?php echo $lang['no_active_tokens'] ?? 'No active tokens'; ?>
                    </div>
                    <?php else: ?>
                    
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo $lang['token'] ?? 'Token'; ?></th>
                                <th><?php echo $lang['priority'] ?? 'Priority'; ?></th>
                                <th><?php echo $lang['patient_name'] ?? 'Patient'; ?></th>
                                <th><?php echo $lang['phone'] ?? 'Phone'; ?></th>
                                <th><?php echo $lang['department'] ?? 'Department'; ?></th>
                                <th><?php echo $lang['wait_time'] ?? 'Wait Time'; ?></th>
                                <th><?php echo $lang['status'] ?? 'Status'; ?></th>
                                <th><?php echo $lang['actions'] ?? 'Actions'; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeQueue as $token): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary"><?php echo $token['token_number']; ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $priority_color = match($token['priority']) {
                                        'Emergency' => 'danger',
                                        'Priority' => 'warning',
                                        'Chronic' => 'info',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $priority_color; ?>">
                                        <?php echo $token['priority']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($token['name']); ?></td>
                                <td><?php echo htmlspecialchars($token['phone']); ?></td>
                                <td><?php echo htmlspecialchars($token['dept_name']); ?></td>
                                <td><?php echo $token['wait_time']; ?> min</td>
                                <td>
                                    <span class="badge bg-<?php echo $token['status'] === 'Called' ? 'danger' : 'success'; ?>">
                                        <?php echo $token['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($token['status'] === 'Active'): ?>
                                        <form method="POST" action="/smarthealth_nepal/admin/api/call_token.php" style="display:inline;">
                                            <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                            <button type="submit" class="btn btn-warning btn-sm" title="Call">
                                                <i class="fas fa-phone"></i> Call
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span class="text-muted small">Called</span>
                                        <?php endif; ?>
                                        
                                        <form method="POST" action="/smarthealth_nepal/admin/api/complete_token.php" style="display:inline;">
                                            <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm" title="Complete">
                                                <i class="fas fa-check"></i> Done
                                            </button>
                                        </form>
                                        
                                        <form method="POST" action="/smarthealth_nepal/admin/api/miss_token.php" style="display:inline;">
                                            <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Miss" onclick="return confirm('Mark as missed?')">
                                                <i class="fas fa-times"></i> Miss
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
