<?php
/**
 * Admin - Referral Management
 * Manage patient referrals between hospitals
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/ServiceController.php';

// Check admin login
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /smarthealth_nepal/admin/public/index.php?page=login');
    exit;
}

// Load admin language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

$serviceController = new ServiceController($db);
$pending = $serviceController->getPendingReferrals();
$all = $serviceController->getAllReferrals();

$pageTitle = $lang['referral_management'] ?? 'Referral Management';
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            
            <!-- Pending Referrals -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-hourglass-half"></i> <?php echo $lang['pending_referrals'] ?? 'Pending Referrals'; ?>
                    </h5>
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($pending)): ?>
                    <div class="alert alert-success m-3">
                        <?php echo $lang['no_pending'] ?? 'No pending referrals'; ?>
                    </div>
                    <?php else: ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $lang['patient'] ?? 'Patient'; ?></th>
                                    <th><?php echo $lang['phone'] ?? 'Phone'; ?></th>
                                    <th><?php echo $lang['from'] ?? 'From'; ?></th>
                                    <th><?php echo $lang['to'] ?? 'To'; ?></th>
                                    <th><?php echo $lang['reason'] ?? 'Reason'; ?></th>
                                    <th><?php echo $lang['date'] ?? 'Date'; ?></th>
                                    <th><?php echo $lang['actions'] ?? 'Actions'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending as $referral): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($referral['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($referral['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($referral['from_hospital']); ?></td>
                                    <td><?php echo htmlspecialchars($referral['to_hospital']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($referral['reason'], 0, 30)); ?></td>
                                    <td><?php echo date('d M Y', strtotime($referral['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <form method="POST" action="/smarthealth_nepal/admin/api/approve_referral.php" 
                                                  style="display:inline;">
                                                <input type="hidden" name="referral_id" value="<?php echo $referral['id']; ?>">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="/smarthealth_nepal/admin/api/reject_referral.php" 
                                                  style="display:inline;">
                                                <input type="hidden" name="referral_id" value="<?php echo $referral['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Reject this referral?')">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- All Referrals -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-share"></i> <?php echo $lang['all_referrals'] ?? 'All Referrals'; ?>
                    </h5>
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($all)): ?>
                    <div class="alert alert-info m-3">
                        <?php echo $lang['no_referrals'] ?? 'No referrals yet'; ?>
                    </div>
                    <?php else: ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $lang['patient'] ?? 'Patient'; ?></th>
                                    <th><?php echo $lang['from'] ?? 'From'; ?></th>
                                    <th><?php echo $lang['to'] ?? 'To'; ?></th>
                                    <th><?php echo $lang['status'] ?? 'Status'; ?></th>
                                    <th><?php echo $lang['date'] ?? 'Date'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all as $referral): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($referral['name']); ?></td>
                                    <td><?php echo htmlspecialchars($referral['from_hospital']); ?></td>
                                    <td><?php echo htmlspecialchars($referral['to_hospital']); ?></td>
                                    <td>
                                        <?php 
                                        $status_color = match($referral['status']) {
                                            'Pending' => 'warning',
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $status_color; ?>">
                                            <?php echo $referral['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($referral['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
