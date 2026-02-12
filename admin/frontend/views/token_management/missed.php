<?php
/**
 * Admin - Missed Tokens
 * Display and manage missed tokens
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/DashboardController.php';

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

$dashboardController = new DashboardController($db);
$missedTokens = $dashboardController->getMissedAppointments(100);

$pageTitle = $lang['missed_tokens'] ?? 'Missed Tokens';
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
                        <i class="fas fa-list"></i> <?php echo $lang['missed_tokens'] ?? 'Missed Tokens'; ?>
                    </h5>
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($missedTokens)): ?>
                    <div class="alert alert-success m-3">
                        <?php echo $lang['all_good'] ?? 'No missed tokens'; ?>
                    </div>
                    <?php else: ?>
                    
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo $lang['token'] ?? 'Token'; ?></th>
                                <th><?php echo $lang['patient_name'] ?? 'Patient'; ?></th>
                                <th><?php echo $lang['phone'] ?? 'Phone'; ?></th>
                                <th><?php echo $lang['department'] ?? 'Department'; ?></th>
                                <th><?php echo $lang['missed_date'] ?? 'Missed Date'; ?></th>
                                <th><?php echo $lang['actions'] ?? 'Actions'; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($missedTokens as $token): ?>
                            <tr>
                                <td>
                                    <strong class="text-danger"><?php echo $token['token_number']; ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($token['name']); ?></td>
                                <td><?php echo htmlspecialchars($token['phone']); ?></td>
                                <td><?php echo htmlspecialchars($token['dept_name']); ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($token['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/smarthealth_nepal/admin/frontend/views/token_management/reschedule.php?token_id=<?php echo $token['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-calendar"></i> Reschedule
                                        </a>
                                        
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#contactModal" 
                                                data-phone="<?php echo htmlspecialchars($token['phone']); ?>">
                                            <i class="fas fa-phone"></i> Contact
                                        </button>
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

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $lang['send_sms'] ?? 'Send SMS'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="smsForm">
                    <div class="mb-3">
                        <label class="form-label"><?php echo $lang['phone'] ?? 'Phone'; ?></label>
                        <input type="text" class="form-control" id="smsPhone" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $lang['message'] ?? 'Message'; ?></label>
                        <textarea class="form-control" id="smsMessage" rows="4">Your token was missed. Please book a new token to resume service.</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $lang['close'] ?? 'Close'; ?></button>
                <button type="button" class="btn btn-primary" onclick="sendSMS()"><i class="fas fa-paper-plane"></i> Send</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('contactModal').addEventListener('show.bs.modal', function(e) {
    const phone = e.relatedTarget.getAttribute('data-phone');
    document.getElementById('smsPhone').value = phone;
});

function sendSMS() {
    const phone = document.getElementById('smsPhone').value;
    const message = document.getElementById('smsMessage').value;
    
    fetch('/smarthealth_nepal/admin/api/send_sms.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'phone=' + encodeURIComponent(phone) + '&message=' + encodeURIComponent(message)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('SMS sent successfully');
            bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();
        } else {
            alert('Failed to send SMS: ' + d.message);
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
