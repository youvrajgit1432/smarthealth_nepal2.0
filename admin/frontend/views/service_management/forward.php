<?php
/**
 * Admin - Forward Referral
 * Forward referrals to other hospitals
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../admin/backend/controllers/ServiceController.php';

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

$serviceController = new ServiceController($db);
$referrals = $serviceController->getAllReferrals('Approved');

$pageTitle = $lang['forward_referral'] ?? 'Forward Referral';
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
                        <i class="fas fa-arrow-right"></i> <?php echo $lang['forward_referral'] ?? 'Forward Referral'; ?>
                    </h5>
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($referrals)): ?>
                    <div class="alert alert-info m-3">
                        <?php echo $lang['no_referrals_to_forward'] ?? 'No referrals to forward'; ?>
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
                                    <th><?php echo $lang['actions'] ?? 'Actions'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($referrals as $referral): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($referral['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($referral['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($referral['from_hospital']); ?></td>
                                    <td><?php echo htmlspecialchars($referral['to_hospital']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($referral['reason'], 0, 30)); ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#forwardModal" 
                                                onclick="prepareForward(<?php echo $referral['id']; ?>, '<?php echo htmlspecialchars($referral['to_hospital']); ?>')">
                                            <i class="fas fa-paper-plane"></i> Forward
                                        </button>
                                    </td>
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

<!-- Forward Modal -->
<div class="modal fade" id="forwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $lang['forward_referral'] ?? 'Forward Referral'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/smarthealth_nepal/admin/api/forward_referral.php">
                <div class="modal-body">
                    <input type="hidden" name="referral_id" id="forwardRef">
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo $lang['forward_to_hospital'] ?? 'Forward to Hospital'; ?></label>
                        <input type="text" name="to_hospital" class="form-control" id="forwardHospital" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo $lang['notes'] ?? 'Notes'; ?></label>
                        <textarea name="notes" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Forward
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function prepareForward(refId, hospital) {
    document.getElementById('forwardRef').value = refId;
    document.getElementById('forwardHospital').value = hospital;
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
