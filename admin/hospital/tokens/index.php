<?php
/**
 * Hospital Admin - Token Management
 * ---------------------------------
 * Filter, inspect and progress the live queue:
 *   Call  : Active/Pending/Confirmed/Rescheduled -> Called
 *   Complete: Called/Active                     -> Completed
 *   Miss  : Active/Called/Rescheduled/Pending   -> Missed
 *   Reschedule: Missed                          -> Rescheduled (re-queued today)
 *
 * Status values match the `tokens.status` enum exactly.
 */

// Central hospital-context resolution (auth + hospital/role rehydration).
require_once __DIR__ . '/../includes/context.php';
require_once __DIR__ . '/../../../backend/helpers/CsrfHelper.php';

$pageTitle = 'Manage Tokens';
$activePage = 'tokens';
$error = '';
$flash = $_SESSION['token_flash'] ?? null;
unset($_SESSION['token_flash']);

$validStatuses = ['Active', 'Called', 'Completed', 'Missed', 'Rescheduled', 'Pending', 'Confirmed', 'Cancelled'];
$validPriorities = ['Emergency', 'Priority', 'Normal', 'Chronic'];

$tokens = [];
$departments = [];

if (!isset($db) || $db->connect_error) {
    $error = 'Database connection failed.';
} else {
    if ($hospital_id) {
        $dq = $db->prepare("SELECT d.id, d.name_en FROM hospital_departments hd JOIN departments d ON hd.department_id = d.id WHERE hd.hospital_id = ? ORDER BY d.name_en");
        $dq->bind_param('i', $hospital_id);
        $dq->execute();
        $departments = $dq->get_result()->fetch_all(MYSQLI_ASSOC);
        $dq->close();
    }

    // -------------------------------------------------------------
    // Queue actions
    // -------------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        try {
            if (!csrf_verify()) {
                throw new Exception('Your session expired. Please try again.');
            }
            if (!$hospital_id) {
                throw new Exception('No hospital is associated with your account.');
            }

            $tokenId = (int) ($_POST['token_id'] ?? 0);
            if ($tokenId <= 0) {
                throw new Exception('Invalid token.');
            }

            // Load token scoped to this hospital (prevent cross-hospital access)
            $q = $db->prepare("SELECT id, status FROM tokens WHERE id = ? AND hospital_id = ? LIMIT 1");
            $q->bind_param('ii', $tokenId, $hospital_id);
            $q->execute();
            $token = $q->get_result()->fetch_assoc();
            $q->close();

            if (!$token) {
                throw new Exception('Token not found for your hospital.');
            }

            $action = $_POST['action'];
            $adminId = (int) $_SESSION['admin_id'];
            $current = $token['status'];

            $allowed = [
                'call'       => ['Active', 'Pending', 'Confirmed', 'Rescheduled'],
                'complete'   => ['Called', 'Active'],
                'miss'       => ['Active', 'Called', 'Rescheduled', 'Pending'],
                'reschedule' => ['Missed'],
            ];
            if (!isset($allowed[$action]) || !in_array($current, $allowed[$action], true)) {
                throw new Exception("This action is not valid for a token that is currently \"$current\".");
            }

            switch ($action) {
                case 'call':
                    $u = $db->prepare("UPDATE tokens SET status = 'Called', called_at = NOW() WHERE id = ? AND hospital_id = ?");
                    break;
                case 'complete':
                    $u = $db->prepare("UPDATE tokens SET status = 'Completed', completed_at = NOW() WHERE id = ? AND hospital_id = ?");
                    break;
                case 'miss':
                    $u = $db->prepare("UPDATE tokens SET status = 'Missed', missed_at = NOW(), missed_by = ? WHERE id = ? AND hospital_id = ?");
                    $u->bind_param('iii', $adminId, $tokenId, $hospital_id);
                    break;
                case 'reschedule':
                    // Put the token back into today's active queue
                    $u = $db->prepare("UPDATE tokens SET status = 'Rescheduled', created_at = NOW() WHERE id = ? AND hospital_id = ?");
                    break;
            }
            if ($action !== 'miss') {
                $u->bind_param('ii', $tokenId, $hospital_id);
            }
            $u->execute();
            if ($u->errno) {
                throw new Exception('Update failed: ' . $u->error);
            }
            $u->close();

            $_SESSION['token_flash'] = ['type' => 'success', 'message' => ucfirst($action) . ' action applied to the token.'];
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // -------------------------------------------------------------
    // Filters + listing
    // -------------------------------------------------------------
    $filter_status = in_array($_GET['status'] ?? '', $validStatuses, true) ? $_GET['status'] : '';
    $filter_priority = in_array($_GET['priority'] ?? '', $validPriorities, true) ? $_GET['priority'] : '';
    $filter_dept = (int) ($_GET['department_id'] ?? 0);
    $filter_date = $_GET['date'] ?? date('Y-m-d');
    $search = trim($_GET['search'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) {
        $filter_date = date('Y-m-d');
    }

    if ($hospital_id) {
        $query = "SELECT t.*, d.name_en AS department, u.full_name, u.phone_number
                    FROM tokens t
                    JOIN departments d ON t.department_id = d.id
                    LEFT JOIN users u ON t.user_id = u.id
                   WHERE t.hospital_id = ? AND DATE(t.created_at) = ?";
        $types = 'is';
        $params = [$hospital_id, $filter_date];

        if ($filter_status !== '') {
            $query .= " AND t.status = ?";
            $types .= 's';
            $params[] = $filter_status;
        }
        if ($filter_priority !== '') {
            $query .= " AND t.priority = ?";
            $types .= 's';
            $params[] = $filter_priority;
        }
        if ($filter_dept > 0) {
            $query .= " AND t.department_id = ?";
            $types .= 'i';
            $params[] = $filter_dept;
        }
        if ($search !== '') {
            $query .= " AND (t.token_number LIKE ? OR u.full_name LIKE ? OR u.phone_number LIKE ?)";
            $like = '%' . $search . '%';
            $types .= 'sss';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $query .= " ORDER BY FIELD(t.priority,'Emergency','Priority','Chronic','Normal'), t.token_number ASC";

        $stmt = $db->prepare($query);
        if ($stmt) {
            $bind = [];
            $bind[] = $types;
            foreach ($params as $k => $v) {
                $bind[] = &$params[$k];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind);
            $stmt->execute();
            $tokens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $error = 'Unable to load tokens: ' . $db->error;
        }
    }
}

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="section">
    <div class="page-head">
        <div>
            <h2>Token Management</h2>
            <p class="page-sub">Call, complete, miss or reschedule tokens in today's queue.</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!$hospital_id): ?>
        <div class="alert alert-warning">No hospital is associated with your account.</div>
    <?php else: ?>

    <form method="GET" class="filters-bar">
        <div class="filter-group">
            <label for="date">Date</label>
            <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>">
        </div>
        <div class="filter-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All</option>
                <?php foreach (['Active', 'Called', 'Completed', 'Missed', 'Rescheduled', 'Pending', 'Confirmed', 'Cancelled'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $filter_status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                <option value="">All</option>
                <?php foreach ($validPriorities as $p): ?>
                    <option value="<?php echo $p; ?>" <?php echo $filter_priority === $p ? 'selected' : ''; ?>><?php echo $p; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="department_id">Department</label>
            <select id="department_id" name="department_id">
                <option value="0">All</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?php echo (int) $d['id']; ?>" <?php echo $filter_dept === (int) $d['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name_en']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group grow">
            <label for="search">Search</label>
            <input type="search" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Token #, name or phone">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Apply</button>
            <a href="?" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <?php if (empty($tokens)): ?>
            <div class="empty-state"><p>No tokens match the selected filters.</p></div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Token #</th><th>Patient</th><th>Phone</th><th>Department</th>
                        <th>Priority</th><th>Status</th><th>Time</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tokens as $t): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($t['token_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($t['full_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($t['phone_number'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($t['department']); ?></td>
                            <td><span class="priority-badge <?php echo strtolower($t['priority']); ?>"><?php echo htmlspecialchars($t['priority']); ?></span></td>
                            <td><span class="status-badge <?php echo strtolower($t['status']); ?>"><?php echo htmlspecialchars($t['status']); ?></span></td>
                            <td><?php echo htmlspecialchars(date('H:i', strtotime($t['created_at']))); ?></td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="action-btn" onclick='showToken(<?php echo json_encode([
                                        "id" => (int) $t["id"],
                                        "token_number" => $t["token_number"],
                                        "patient" => $t["full_name"] ?? "-",
                                        "phone" => $t["phone_number"] ?? "-",
                                        "department" => $t["department"],
                                        "priority" => $t["priority"],
                                        "status" => $t["status"],
                                        "wait" => $t["estimated_wait_time"],
                                        "created" => $t["created_at"],
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>, this)'>View</button>

                                    <?php
                                    $actions = [];
                                    if (in_array($t['status'], ['Active', 'Pending', 'Confirmed', 'Rescheduled'], true)) {
                                        $actions[] = ['call', 'Call', 'action-primary'];
                                    }
                                    if (in_array($t['status'], ['Called', 'Active'], true)) {
                                        $actions[] = ['complete', 'Complete', 'action-success'];
                                    }
                                    if (in_array($t['status'], ['Active', 'Called', 'Rescheduled', 'Pending'], true)) {
                                        $actions[] = ['miss', 'Miss', 'action-danger'];
                                    }
                                    if ($t['status'] === 'Missed') {
                                        $actions[] = ['reschedule', 'Reschedule', 'action-primary'];
                                    }
                                    foreach ($actions as [$act, $label, $cls]): ?>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="<?php echo $act; ?>">
                                            <input type="hidden" name="token_id" value="<?php echo (int) $t['id']; ?>">
                                            <button type="submit" class="action-btn <?php echo $cls; ?>"><?php echo $label; ?></button>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Token detail modal -->
<div id="tokenModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="tokenModalTitle" hidden>
    <div class="modal-box modal-box-sm">
        <h3 id="tokenModalTitle">Token Details</h3>
        <table style="width:100%; font-size:14px;">
            <tbody>
                <tr><td style="color:#6b7280;padding:6px 0;">Token #</td><td id="tv-number" style="text-align:right;font-weight:600;"></td></tr>
                <tr><td style="color:#6b7280;padding:6px 0;">Patient</td><td id="tv-patient" style="text-align:right;"></td></tr>
                <tr><td style="color:#6b7280;padding:6px 0;">Phone</td><td id="tv-phone" style="text-align:right;"></td></tr>
                <tr><td style="color:#6b7280;padding:6px 0;">Department</td><td id="tv-department" style="text-align:right;"></td></tr>
                <tr><td style="color:#6b7280;padding:6px 0;">Priority</td><td id="tv-priority" style="text-align:right;"></td></tr>
                <tr><td style="color:#6b7280;padding:6px 0;">Status</td><td id="tv-status" style="text-align:right;"></td></tr>
                <tr><td style="color:#6b7280;padding:6px 0;">Est. wait</td><td id="tv-wait" style="text-align:right;"></td></tr>
                <tr><td style="color:#6b7280;padding:6px 0;">Created</td><td id="tv-created" style="text-align:right;"></td></tr>
            </tbody>
        </table>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeToken()">Close</button>
        </div>
    </div>
</div>

<div id="toast" class="toast" role="status" aria-live="polite" hidden></div>

<script>
(function () {
    const modal = document.getElementById('tokenModal');
    let lastTrigger = null;

    function openModal() {
        modal.hidden = false;
        document.body.classList.add('modal-open');
        const first = modal.querySelector('button, [href], input, select, textarea');
        if (first) first.focus();
    }
    function closeModal() {
        if (modal.hidden) return;
        modal.hidden = true;
        document.body.classList.remove('modal-open');
        if (lastTrigger && document.body.contains(lastTrigger)) lastTrigger.focus();
    }

    window.showToken = function (t, trigger) {
        lastTrigger = trigger || document.activeElement;
        document.getElementById('tv-number').textContent = t.token_number;
        document.getElementById('tv-patient').textContent = t.patient;
        document.getElementById('tv-phone').textContent = t.phone;
        document.getElementById('tv-department').textContent = t.department;
        document.getElementById('tv-priority').textContent = t.priority;
        document.getElementById('tv-status').textContent = t.status;
        document.getElementById('tv-wait').textContent = (t.wait === null || t.wait === undefined) ? 'N/A' : t.wait + ' min';
        document.getElementById('tv-created').textContent = t.created;
        openModal();
    };
    window.closeToken = closeModal;
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

    const flash = <?php echo json_encode($flash ?: null); ?>;
    if (flash) {
        const toast = document.getElementById('toast');
        toast.textContent = flash.message;
        toast.className = 'toast toast-' + (flash.type || 'success');
        toast.hidden = false;
        setTimeout(function () { toast.hidden = true; }, 4000);
    }
})();
</script>

<style>
.page-head { margin-bottom: 22px; }
.page-sub { color: #8fa8ba; font-size: 13px; margin-top: 6px; }
.filters-bar { display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 18px; }
.filter-group { display: flex; flex-direction: column; gap: 6px; min-width: 140px; }
.filter-group.grow { flex: 1 1 200px; }
.filter-group label { font-size: 12px; font-weight: 600; color: #2c3e50; text-transform: uppercase; letter-spacing: .3px; }
.filter-group input, .filter-group select { padding: 9px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; }
.filter-actions { display: flex; gap: 10px; }
.table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow-x: auto; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
.row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.action-btn { padding: 6px 10px; border: 1px solid #d1d5db; background: #f9fafb; border-radius: 5px; cursor: pointer; font-size: 12px; font-weight: 600; color: #2c3e50; }
.action-primary { border-color: #bcd3f7; background: #eaf2fe; color: #0d47a1; }
.action-success { border-color: #bfe3cd; background: #e9f7ef; color: #1e7e34; }
.action-danger { border-color: #f5c6cb; background: #fdecea; color: #b02a37; }
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: flex; align-items: center; justify-content: center; z-index: 2000; padding: 16px; }
.modal-box { background: #fff; border-radius: 10px; max-width: 520px; width: 100%; padding: 26px; max-height: 92vh; overflow-y: auto; box-shadow: 0 20px 50px rgba(0,0,0,.3); }
.modal-box-sm { max-width: 440px; }
.modal-box h3 { margin-bottom: 16px; }
.modal-actions { display: flex; gap: 10px; margin-top: 20px; }
.toast { position: fixed; right: 24px; bottom: 24px; z-index: 3000; padding: 14px 18px; border-radius: 8px; color: #fff; font-size: 14px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,.25); max-width: 360px; }
.toast-success { background: #27ae60; }
.toast-warning { background: #f39c12; }
.toast-error { background: #e74c3c; }
@media (max-width: 640px) { .filter-group { flex: 1 1 100%; } .toast { left: 16px; right: 16px; } }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
