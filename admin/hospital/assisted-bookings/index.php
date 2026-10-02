<?php
/**
 * Hospital Admin - Assisted Bookings
 * ----------------------------------
 * Create and manage appointments booked on behalf of walk-in patients.
 * Full workflow: create, edit, change status, cancel and delete.
 */

// Central hospital-context resolution (auth + hospital/role rehydration).
require_once __DIR__ . '/../includes/context.php';
require_once __DIR__ . '/../../../backend/helpers/CsrfHelper.php';

$pageTitle = 'Assisted Bookings';
$activePage = 'assisted-bookings';

$error = '';
$flash = $_SESSION['ab_flash'] ?? null;
unset($_SESSION['ab_flash']);

$bookings = [];
$departments = [];

if (!isset($db) || $db->connect_error) {
    $error = 'Database connection failed.';
} else {
    // Departments this hospital offers (for the dropdown)
    if ($hospital_id) {
        $dq = $db->prepare(
            "SELECT d.id, d.name_en
               FROM hospital_departments hd
               JOIN departments d ON hd.department_id = d.id
              WHERE hd.hospital_id = ? AND hd.is_active = 1
              ORDER BY d.name_en"
        );
        $dq->bind_param('i', $hospital_id);
        $dq->execute();
        $departments = $dq->get_result()->fetch_all(MYSQLI_ASSOC);
        $dq->close();
    }

    // --------------------------------------------------------------
    // Actions
    // --------------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        try {
            if (!csrf_verify()) {
                throw new Exception('Your session expired. Please try again.');
            }
            if (!$hospital_id) {
                throw new Exception('No hospital is associated with your account.');
            }

            $action = $_POST['action'];

            // Validate a department belongs to this hospital
            $validDept = function ($deptId) use ($db, $hospital_id) {
                $q = $db->prepare("SELECT id FROM hospital_departments WHERE hospital_id = ? AND department_id = ? AND is_active = 1 LIMIT 1");
                $q->bind_param('ii', $hospital_id, $deptId);
                $q->execute();
                $ok = $q->get_result()->num_rows > 0;
                $q->close();
                return $ok;
            };

            if ($action === 'create') {
                $name = trim($_POST['patient_name'] ?? '');
                $phone = trim($_POST['patient_phone'] ?? '');
                $deptId = (int) ($_POST['department_id'] ?? 0);
                $date = $_POST['booking_date'] ?? '';
                $time = $_POST['booking_time'] ?? '';

                if ($name === '' || $phone === '' || $deptId <= 0 || $date === '' || $time === '') {
                    throw new Exception('Patient name, phone, department, date and time are required.');
                }
                if (!$validDept($deptId)) {
                    throw new Exception('The selected department is not available at your hospital.');
                }
                $priority = in_array($_POST['priority'] ?? '', ['Emergency', 'Priority', 'Normal', 'Chronic'], true) ? $_POST['priority'] : 'Normal';
                $age = ($_POST['patient_age'] ?? '') !== '' ? (int) $_POST['patient_age'] : null;
                $gender = in_array($_POST['patient_gender'] ?? '', ['Male', 'Female', 'Other'], true) ? $_POST['patient_gender'] : null;
                $symptoms = trim($_POST['symptoms'] ?? '');
                $notes = trim($_POST['notes'] ?? '');
                $triage = $symptoms !== '' ? json_encode(['symptoms' => $symptoms]) : null;
                $adminId = (int) $_SESSION['admin_id'];

                $ins = $db->prepare(
                    "INSERT INTO assisted_bookings
                        (hospital_id, department_id, patient_name, patient_phone, patient_age, patient_gender,
                         triage_data, priority, booking_date, booking_time, booked_by, notes, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')"
                );
                $ins->bind_param(
                    'iississsssis',
                    $hospital_id, $deptId, $name, $phone, $age, $gender,
                    $triage, $priority, $date, $time, $adminId, $notes
                );
                $ins->execute();
                if ($ins->error) {
                    throw new Exception('Could not create booking: ' . $ins->error);
                }
                $ins->close();
                $_SESSION['ab_flash'] = ['type' => 'success', 'message' => 'Assisted booking created.'];
            } elseif ($action === 'update') {
                $id = (int) ($_POST['booking_id'] ?? 0);
                $name = trim($_POST['patient_name'] ?? '');
                $phone = trim($_POST['patient_phone'] ?? '');
                $deptId = (int) ($_POST['department_id'] ?? 0);
                $date = $_POST['booking_date'] ?? '';
                $time = $_POST['booking_time'] ?? '';
                if ($id <= 0 || $name === '' || $phone === '' || $deptId <= 0 || $date === '' || $time === '') {
                    throw new Exception('Please complete all required fields.');
                }
                if (!$validDept($deptId)) {
                    throw new Exception('The selected department is not available at your hospital.');
                }
                $priority = in_array($_POST['priority'] ?? '', ['Emergency', 'Priority', 'Normal', 'Chronic'], true) ? $_POST['priority'] : 'Normal';
                $age = ($_POST['patient_age'] ?? '') !== '' ? (int) $_POST['patient_age'] : null;
                $gender = in_array($_POST['patient_gender'] ?? '', ['Male', 'Female', 'Other'], true) ? $_POST['patient_gender'] : null;
                $notes = trim($_POST['notes'] ?? '');

                $upd = $db->prepare(
                    "UPDATE assisted_bookings
                        SET department_id = ?, patient_name = ?, patient_phone = ?, patient_age = ?,
                            patient_gender = ?, priority = ?, booking_date = ?, booking_time = ?, notes = ?
                      WHERE id = ? AND hospital_id = ?"
                );
                $upd->bind_param('ississsssii', $deptId, $name, $phone, $age, $gender, $priority, $date, $time, $notes, $id, $hospital_id);
                $upd->execute();
                if ($upd->affected_rows === 0 && $upd->errno === 0) {
                    // either no change or not found; verify existence
                    $chk = $db->prepare("SELECT id FROM assisted_bookings WHERE id = ? AND hospital_id = ?");
                    $chk->bind_param('ii', $id, $hospital_id);
                    $chk->execute();
                    if ($chk->get_result()->num_rows === 0) {
                        throw new Exception('Booking not found for your hospital.');
                    }
                    $chk->close();
                }
                $upd->close();
                $_SESSION['ab_flash'] = ['type' => 'success', 'message' => 'Booking updated.'];
            } elseif ($action === 'update_status') {
                $id = (int) ($_POST['booking_id'] ?? 0);
                $status = $_POST['status'] ?? '';
                if (!in_array($status, ['Pending', 'Assigned', 'Confirmed', 'Completed', 'Cancelled'], true)) {
                    throw new Exception('Invalid status.');
                }
                $upd = $db->prepare("UPDATE assisted_bookings SET status = ? WHERE id = ? AND hospital_id = ?");
                $upd->bind_param('sii', $status, $id, $hospital_id);
                $upd->execute();
                if ($upd->affected_rows < 1) {
                    throw new Exception('Booking not found for your hospital.');
                }
                $upd->close();
                $_SESSION['ab_flash'] = ['type' => 'success', 'message' => 'Status updated.'];
            } elseif ($action === 'delete') {
                $id = (int) ($_POST['booking_id'] ?? 0);
                // Only allow deleting bookings that are not completed (history kept otherwise)
                $del = $db->prepare("DELETE FROM assisted_bookings WHERE id = ? AND hospital_id = ? AND status <> 'Completed'");
                $del->bind_param('ii', $id, $hospital_id);
                $del->execute();
                if ($del->affected_rows < 1) {
                    throw new Exception('Only non-completed bookings can be deleted.');
                }
                $del->close();
                $_SESSION['ab_flash'] = ['type' => 'warning', 'message' => 'Booking deleted.'];
            }

            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // Load bookings
    if ($hospital_id) {
        $q = $db->prepare(
            "SELECT ab.*, d.name_en AS department, a.full_name AS booked_by_name
               FROM assisted_bookings ab
               LEFT JOIN departments d ON ab.department_id = d.id
               LEFT JOIN admins a ON ab.booked_by = a.id
              WHERE ab.hospital_id = ?
              ORDER BY ab.booking_date DESC, ab.booking_time DESC"
        );
        $q->bind_param('i', $hospital_id);
        $q->execute();
        $bookings = $q->get_result()->fetch_all(MYSQLI_ASSOC);
        $q->close();
    }
}

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="section">
    <div class="page-head">
        <div>
            <h2>Assisted Bookings</h2>
            <p class="page-sub">Create and manage appointments booked for walk-in / offline patients.</p>
        </div>
        <button class="btn btn-primary" type="button" onclick="openCreate(this)">+ New Booking</button>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!$hospital_id): ?>
        <div class="alert alert-warning">No hospital is associated with your account.</div>
    <?php else: ?>
        <div class="table-wrap">
            <?php if (empty($bookings)): ?>
                <div class="empty-state"><p>No assisted bookings yet.</p></div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th><th>Phone</th><th>Department</th><th>Date</th><th>Time</th>
                            <th>Priority</th><th>Status</th><th>Booked By</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($b['patient_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($b['patient_phone']); ?></td>
                                <td><?php echo htmlspecialchars($b['department'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($b['booking_date']))); ?></td>
                                <td><?php echo $b['booking_time'] ? htmlspecialchars(date('H:i', strtotime($b['booking_time']))) : '-'; ?></td>
                                <td><span class="priority-badge <?php echo strtolower($b['priority']); ?>"><?php echo htmlspecialchars($b['priority']); ?></span></td>
                                <td><span class="status-badge <?php echo strtolower($b['status']); ?>"><?php echo htmlspecialchars($b['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($b['booked_by_name'] ?? '-'); ?></td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button" class="action-btn" onclick='openEdit(<?php echo json_encode([
                                            "id" => (int) $b["id"],
                                            "patient_name" => $b["patient_name"],
                                            "patient_phone" => $b["patient_phone"],
                                            "patient_age" => $b["patient_age"],
                                            "patient_gender" => $b["patient_gender"],
                                            "department_id" => (int) $b["department_id"],
                                            "priority" => $b["priority"],
                                            "booking_date" => $b["booking_date"],
                                            "booking_time" => $b["booking_time"] ? substr($b["booking_time"], 0, 5) : "",
                                            "notes" => $b["notes"],
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>, this)'>Edit</button>

                                        <form method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="booking_id" value="<?php echo (int) $b['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="action-select" aria-label="Change status">
                                                <option value="<?php echo htmlspecialchars($b['status']); ?>" selected><?php echo htmlspecialchars($b['status']); ?></option>
                                                <?php foreach (['Pending', 'Assigned', 'Confirmed', 'Completed', 'Cancelled'] as $s): ?>
                                                    <?php if ($s !== $b['status']): ?>
                                                        <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>

                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this booking? This cannot be undone.');">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="booking_id" value="<?php echo (int) $b['id']; ?>">
                                            <button type="submit" class="action-btn action-danger">Delete</button>
                                        </form>
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

<!-- Create / Edit modal -->
<div id="bookingModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="bookingModalTitle" hidden>
    <div class="modal-box">
        <h3 id="bookingModalTitle">New Assisted Booking</h3>
        <form method="POST" id="bookingForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" id="bookingAction" value="create">
            <input type="hidden" name="booking_id" id="bookingId" value="">

            <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="form-group">
                    <label for="patient_name">Patient Name *</label>
                    <input type="text" id="patient_name" name="patient_name" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="patient_phone">Patient Phone *</label>
                    <input type="tel" id="patient_phone" name="patient_phone" required maxlength="20">
                </div>
                <div class="form-group">
                    <label for="patient_age">Age</label>
                    <input type="number" id="patient_age" name="patient_age" min="0" max="150">
                </div>
                <div class="form-group">
                    <label for="patient_gender">Gender</label>
                    <select id="patient_gender" name="patient_gender">
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="department_id">Department *</label>
                    <select id="department_id" name="department_id" required>
                        <option value="">Select department</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?php echo (int) $d['id']; ?>"><?php echo htmlspecialchars($d['name_en']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <option value="Normal">Normal</option>
                        <option value="Priority">Priority</option>
                        <option value="Emergency">Emergency</option>
                        <option value="Chronic">Chronic</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="booking_date">Booking Date *</label>
                    <input type="date" id="booking_date" name="booking_date" required>
                </div>
                <div class="form-group">
                    <label for="booking_time">Booking Time *</label>
                    <input type="time" id="booking_time" name="booking_time" required>
                </div>
            </div>

            <div class="form-group" id="symptomsGroup">
                <label for="symptoms">Symptoms / Reason for Visit</label>
                <textarea id="symptoms" name="symptoms" rows="2" placeholder="Describe symptoms or reason for visit"></textarea>
            </div>

            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea id="notes" name="notes" rows="2"></textarea>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary" id="bookingSubmit">Save Booking</button>
                <button type="button" class="btn btn-secondary" onclick="closeBooking()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="toast" class="toast" role="status" aria-live="polite" hidden></div>

<script>
(function () {
    const modal = document.getElementById('bookingModal');
    const form = document.getElementById('bookingForm');
    const today = new Date().toISOString().slice(0, 10);
    let lastTrigger = null;

    function openModal() {
        modal.hidden = false;
        document.body.classList.add('modal-open');
        document.getElementById('patient_name').focus();
    }
    function closeModal() {
        if (modal.hidden) return;
        modal.hidden = true;
        document.body.classList.remove('modal-open');
        if (lastTrigger && document.body.contains(lastTrigger)) lastTrigger.focus();
    }

    window.openCreate = function (trigger) {
        lastTrigger = trigger || document.activeElement;
        document.getElementById('bookingModalTitle').textContent = 'New Assisted Booking';
        document.getElementById('bookingAction').value = 'create';
        document.getElementById('bookingId').value = '';
        form.reset();
        document.getElementById('booking_date').value = today;
        document.getElementById('symptomsGroup').style.display = '';
        openModal();
    };

    window.openEdit = function (b, trigger) {
        lastTrigger = trigger || document.activeElement;
        document.getElementById('bookingModalTitle').textContent = 'Edit Assisted Booking';
        document.getElementById('bookingAction').value = 'update';
        document.getElementById('bookingId').value = b.id;
        document.getElementById('patient_name').value = b.patient_name || '';
        document.getElementById('patient_phone').value = b.patient_phone || '';
        document.getElementById('patient_age').value = b.patient_age ?? '';
        document.getElementById('patient_gender').value = b.patient_gender || '';
        document.getElementById('department_id').value = b.department_id || '';
        document.getElementById('priority').value = b.priority || 'Normal';
        document.getElementById('booking_date').value = b.booking_date || today;
        document.getElementById('booking_time').value = b.booking_time || '';
        document.getElementById('notes').value = b.notes || '';
        document.getElementById('symptomsGroup').style.display = 'none';
        openModal();
    };

    window.closeBooking = closeModal;

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
.page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
.page-sub { color: #8fa8ba; font-size: 13px; margin-top: 6px; }
.table-wrap { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow-x: auto; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
.row-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
.action-btn { padding: 6px 10px; border: 1px solid #d1d5db; background: #f9fafb; border-radius: 5px; cursor: pointer; font-size: 12px; font-weight: 600; color: #2c3e50; }
.action-btn:hover { border-color: #1565c0; color: #1565c0; }
.action-danger { border-color: #f5c6cb; background: #fdecea; color: #b02a37; }
.action-select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 5px; font-size: 12px; background: #fff; }
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: flex; align-items: center; justify-content: center; z-index: 2000; padding: 16px; }
.modal-box { background: #fff; border-radius: 10px; max-width: 620px; width: 100%; padding: 26px; max-height: 92vh; overflow-y: auto; box-shadow: 0 20px 50px rgba(0,0,0,.3); }
.modal-box h3 { margin-bottom: 18px; }
.modal-actions { display: flex; gap: 10px; margin-top: 20px; }
.toast { position: fixed; right: 24px; bottom: 24px; z-index: 3000; padding: 14px 18px; border-radius: 8px; color: #fff; font-size: 14px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,.25); max-width: 360px; }
.toast-success { background: #27ae60; }
.toast-warning { background: #f39c12; }
.toast-error { background: #e74c3c; }
@media (max-width: 640px) { .toast { left: 16px; right: 16px; } }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
