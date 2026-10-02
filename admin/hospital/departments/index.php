<?php
/**
 * Hospital Departments Management
 * -------------------------------
 * Real, database-backed CRUD for the departments a hospital offers.
 *
 * Data model
 * ----------
 *  - `departments`         : global catalog of departments (name / desc / capacity)
 *  - `hospital_departments` : link table (which departments a hospital offers,
 *                             plus per-hospital max tokens and availability)
 *
 * Everything is scoped to the signed-in hospital admin's hospital.
 */

// Central hospital-context resolution (auth + hospital/role rehydration).
// Super admins may switch hospital explicitly (validated + persisted there);
// hospital admins are locked to their own hospital.
require_once __DIR__ . '/../includes/context.php';
require_once __DIR__ . '/../../../backend/helpers/CsrfHelper.php';

// Defaults
$pageTitle = 'Departments Management';
$activePage = 'departments';

$error = '';
$flash = $_SESSION['dept_flash'] ?? null;
unset($_SESSION['dept_flash']);

if (!isset($db) || $db->connect_error) {
    $error = 'Database connection failed.';
    $emergency_departments = [];
} else {
    // ------------------------------------------------------------------
    // Handle state-changing actions
    // ------------------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        try {
            if (!csrf_verify()) {
                throw new Exception('Your session expired. Please try again.');
            }
            if (!$hospital_id) {
                throw new Exception('No hospital is associated with your account.');
            }

            $action = $_POST['action'];

            if ($action === 'add') {
                $nameEn = trim($_POST['name_en'] ?? '');
                if ($nameEn === '') {
                    throw new Exception('Department name (English) is required.');
                }
                $nameNe    = trim($_POST['name_ne'] ?? '');
                $descEn    = trim($_POST['description_en'] ?? '');
                $descNe    = trim($_POST['description_ne'] ?? '');
                $capacity  = max(1, (int) ($_POST['max_capacity'] ?? 50));
                $service   = max(1, (int) ($_POST['avg_service_time'] ?? 30));
                $isActive  = !empty($_POST['is_active']) ? 1 : 0;
                $attachId  = (int) ($_POST['attach_department_id'] ?? 0);

                $db->begin_transaction();

                if ($attachId > 0) {
                    // Attaching an existing catalog department
                    $check = $db->prepare("SELECT id FROM departments WHERE id = ? LIMIT 1");
                    $check->bind_param('i', $attachId);
                    $check->execute();
                    if ($check->get_result()->num_rows === 0) {
                        throw new Exception('Selected department no longer exists.');
                    }
                    $check->close();
                    $deptId = $attachId;
                } else {
                    // Reuse an existing catalog row with the same English name, else create it
                    $find = $db->prepare("SELECT id FROM departments WHERE name_en = ? LIMIT 1");
                    $find->bind_param('s', $nameEn);
                    $find->execute();
                    $existing = $find->get_result()->fetch_assoc();
                    $find->close();

                    if ($existing) {
                        $deptId = (int) $existing['id'];
                    } else {
                        $ins = $db->prepare(
                            "INSERT INTO departments
                                (name_en, name_ne, description_en, description_ne, max_capacity, avg_service_time, is_active)
                             VALUES (?, ?, ?, ?, ?, ?, ?)"
                        );
                        $ins->bind_param('ssssiii', $nameEn, $nameNe, $descEn, $descNe, $capacity, $service, $isActive);
                        $ins->execute();
                        $deptId = $ins->insert_id;
                        $ins->close();
                    }
                }

                // Link to this hospital (unique per hospital+department)
                $link = $db->prepare(
                    "INSERT INTO hospital_departments
                        (hospital_id, department_id, max_tokens_per_day, available, is_active)
                     VALUES (?, ?, ?, ?, 1)
                     ON DUPLICATE KEY UPDATE
                        max_tokens_per_day = VALUES(max_tokens_per_day),
                        available = VALUES(available),
                        is_active = 1"
                );
                $link->bind_param('iiii', $hospital_id, $deptId, $capacity, $isActive);
                $link->execute();
                $link->close();

                $db->commit();
                $_SESSION['dept_flash'] = ['type' => 'success', 'message' => 'Department added successfully.'];
            } elseif ($action === 'edit') {
                $hdId     = (int) ($_POST['hospital_department_id'] ?? 0);
                $deptId   = (int) ($_POST['department_id'] ?? 0);
                $nameEn   = trim($_POST['name_en'] ?? '');
                if ($nameEn === '' || $hdId <= 0 || $deptId <= 0) {
                    throw new Exception('Missing required department fields.');
                }
                $nameNe   = trim($_POST['name_ne'] ?? '');
                $descEn   = trim($_POST['description_en'] ?? '');
                $descNe   = trim($_POST['description_ne'] ?? '');
                $capacity = max(1, (int) ($_POST['max_capacity'] ?? 50));
                $service  = max(1, (int) ($_POST['avg_service_time'] ?? 30));
                $isActive = !empty($_POST['is_active']) ? 1 : 0;

                $db->begin_transaction();

                // Only update the catalog row if this hospital "owns" the link
                $own = $db->prepare("SELECT id FROM hospital_departments WHERE id = ? AND hospital_id = ? LIMIT 1");
                $own->bind_param('ii', $hdId, $hospital_id);
                $own->execute();
                if ($own->get_result()->num_rows === 0) {
                    $own->close();
                    throw new Exception('Department not found for your hospital.');
                }
                $own->close();

                $u1 = $db->prepare(
                    "UPDATE departments
                        SET name_en = ?, name_ne = ?, description_en = ?, description_ne = ?,
                            max_capacity = ?, avg_service_time = ?, is_active = ?
                      WHERE id = ?"
                );
                $u1->bind_param('ssssiiii', $nameEn, $nameNe, $descEn, $descNe, $capacity, $service, $isActive, $deptId);
                $u1->execute();
                $u1->close();

                $u2 = $db->prepare(
                    "UPDATE hospital_departments
                        SET max_tokens_per_day = ?, available = ?
                      WHERE id = ? AND hospital_id = ?"
                );
                $u2->bind_param('iiii', $capacity, $isActive, $hdId, $hospital_id);
                $u2->execute();
                $u2->close();

                $db->commit();
                $_SESSION['dept_flash'] = ['type' => 'success', 'message' => 'Department updated successfully.'];
            } elseif ($action === 'toggle') {
                $hdId = (int) ($_POST['hospital_department_id'] ?? 0);
                if ($hdId <= 0) {
                    throw new Exception('Invalid department.');
                }
                $t = $db->prepare(
                    "UPDATE hospital_departments
                        SET available = IF(available = 1, 0, 1)
                      WHERE id = ? AND hospital_id = ?"
                );
                $t->bind_param('ii', $hdId, $hospital_id);
                $t->execute();
                if ($t->affected_rows < 1) {
                    throw new Exception('Department not found for your hospital.');
                }
                $t->close();
                $_SESSION['dept_flash'] = ['type' => 'success', 'message' => 'Availability updated.'];
            } elseif ($action === 'delete') {
                $hdId = (int) ($_POST['hospital_department_id'] ?? 0);
                if ($hdId <= 0) {
                    throw new Exception('Invalid department.');
                }

                $row = $db->prepare(
                    "SELECT hd.department_id
                       FROM hospital_departments hd
                      WHERE hd.id = ? AND hd.hospital_id = ? LIMIT 1"
                );
                $row->bind_param('ii', $hdId, $hospital_id);
                $row->execute();
                $link = $row->get_result()->fetch_assoc();
                $row->close();

                if (!$link) {
                    throw new Exception('Department not found for your hospital.');
                }
                $deptId = (int) $link['department_id'];

                // Count real usage that would break referential integrity / clinical history
                $inUse = 0;
                $countSql = [
                    "SELECT COUNT(*) c FROM tokens WHERE department_id = ?",
                    "SELECT COUNT(*) c FROM hospital_staff WHERE department_id = ?",
                    "SELECT COUNT(*) c FROM assisted_bookings WHERE department_id = ?",
                    "SELECT COUNT(*) c FROM admins WHERE department_id = ?",
                    "SELECT COUNT(*) c FROM referrals WHERE from_department_id = ? OR to_department_id = ?",
                ];
                foreach ($countSql as $i => $sql) {
                    $st = $db->prepare($sql);
                    if ($i === 4) {
                        $st->bind_param('ii', $deptId, $deptId);
                    } else {
                        $st->bind_param('i', $deptId);
                    }
                    $st->execute();
                    $inUse += (int) ($st->get_result()->fetch_assoc()['c'] ?? 0);
                    $st->close();
                }
                // Other hospitals still offering this department?
                $others = $db->prepare("SELECT COUNT(*) c FROM hospital_departments WHERE department_id = ? AND hospital_id <> ?");
                $others->bind_param('ii', $deptId, $hospital_id);
                $others->execute();
                $usedElsewhere = (int) ($others->get_result()->fetch_assoc()['c'] ?? 0);
                $others->close();

                $db->begin_transaction();
                if ($inUse > 0 || $usedElsewhere > 0) {
                    // Safe route: detach from this hospital only, keep history intact
                    $del = $db->prepare("DELETE FROM hospital_departments WHERE id = ? AND hospital_id = ?");
                    $del->bind_param('ii', $hdId, $hospital_id);
                    $del->execute();
                    $del->close();
                    if ($usedElsewhere === 0 && $inUse === 0) {
                        // nothing else to do
                    }
                    $db->commit();
                    $_SESSION['dept_flash'] = [
                        'type' => 'warning',
                        'message' => 'Department removed from your hospital. Existing tokens and records were preserved for history.'
                    ];
                } else {
                    $del = $db->prepare("DELETE FROM hospital_departments WHERE id = ? AND hospital_id = ?");
                    $del->bind_param('ii', $hdId, $hospital_id);
                    $del->execute();
                    $del->close();

                    $delDept = $db->prepare("DELETE FROM departments WHERE id = ?");
                    $delDept->bind_param('i', $deptId);
                    $delDept->execute();
                    $delDept->close();

                    $db->commit();
                    $_SESSION['dept_flash'] = ['type' => 'success', 'message' => 'Department deleted successfully.'];
                }
            }

            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;

        } catch (Exception $e) {
            if (isset($db) && $db->errno === 0) {
                // best-effort rollback if a transaction is open
                @$db->rollback();
            }
            $error = $e->getMessage();
        }
    }

    // ------------------------------------------------------------------
    // Load departments for display
    // ------------------------------------------------------------------
    $departments = [];
    if ($hospital_id) {
        $sql = "SELECT hd.id AS hospital_department_id,
                       hd.max_tokens_per_day,
                       hd.available,
                       hd.is_active AS link_active,
                       d.id AS department_id,
                       d.name_en, d.name_ne,
                       d.description_en, d.description_ne,
                       d.max_capacity, d.avg_service_time, d.is_active AS dept_active,
                       (SELECT COUNT(*) FROM tokens t
                         WHERE t.department_id = d.id
                           AND t.hospital_id = hd.hospital_id
                           AND DATE(t.created_at) = CURDATE()) AS tokens_today
                  FROM hospital_departments hd
                  JOIN departments d ON hd.department_id = d.id
                 WHERE hd.hospital_id = ?
                 ORDER BY d.name_en";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $hospital_id);
        $stmt->execute();
        $departments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Catalog departments that could still be attached to this hospital
    $linkable = [];
    if ($hospital_id) {
        $sql = "SELECT d.id, d.name_en, d.name_ne
                  FROM departments d
                 WHERE d.id NOT IN (
                        SELECT department_id FROM hospital_departments WHERE hospital_id = ?
                       )
                 ORDER BY d.name_en";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $hospital_id);
        $stmt->execute();
        $linkable = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="section">
    <div class="page-head">
        <div>
            <h2>Hospital Departments</h2>
            <p class="page-sub">Manage the departments your hospital offers, their capacity and availability.</p>
        </div>
        <button class="btn btn-primary" onclick="openAddModal(this)" type="button">
            <span aria-hidden="true">+</span> Add Department
        </button>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!$hospital_id): ?>
        <div class="alert alert-warning" role="alert">
            No hospital is associated with your account. Please contact a super administrator.
        </div>
    <?php elseif (empty($departments)): ?>
        <div class="empty-state">
            <p>No departments configured yet.</p>
            <button class="btn btn-primary" type="button" onclick="openAddModal(this)">Add your first department</button>
        </div>
    <?php else: ?>
        <div class="departments-grid">
            <?php foreach ($departments as $dept): ?>
                <?php
                    $available = (int) $dept['available'] === 1;
                    $linkActive = (int) $dept['link_active'] === 1;
                    $display = $available && $linkActive ? 'Available'
                             : ($linkActive ? 'Unavailable' : 'Hidden');
                    $badgeClass = $available && $linkActive ? 'active' : 'inactive';
                ?>
                <div class="department-card">
                    <div class="department-card-head">
                        <h4><?php echo htmlspecialchars($dept['name_en']); ?></h4>
                        <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $display; ?></span>
                    </div>
                    <?php if (!empty($dept['name_ne'])): ?>
                        <p class="dept-ne"><?php echo htmlspecialchars($dept['name_ne']); ?></p>
                    <?php endif; ?>
                    <p><strong>Max tokens / day:</strong> <?php echo (int) $dept['max_tokens_per_day']; ?></p>
                    <p><strong>Avg service time:</strong> <?php echo (int) $dept['avg_service_time']; ?> mins</p>
                    <p><strong>Tokens today:</strong> <?php echo (int) $dept['tokens_today']; ?></p>

                    <div class="department-actions">
                        <button type="button" class="btn btn-small btn-primary"
                            onclick='openEditModal(<?php echo json_encode([
                                "id" => (int) $dept["hospital_department_id"],
                                "department_id" => (int) $dept["department_id"],
                                "name_en" => $dept["name_en"],
                                "name_ne" => $dept["name_ne"],
                                "description_en" => $dept["description_en"],
                                "description_ne" => $dept["description_ne"],
                                "max_tokens_per_day" => (int) $dept["max_tokens_per_day"],
                                "avg_service_time" => (int) $dept["avg_service_time"],
                                "available" => (int) $dept["available"],
                            ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>, this)'>
                            Edit
                        </button>

                        <form method="POST" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="hospital_department_id" value="<?php echo (int) $dept['hospital_department_id']; ?>">
                            <button type="submit" class="btn btn-small btn-secondary">
                                <?php echo $available ? 'Set unavailable' : 'Set available'; ?>
                            </button>
                        </form>

                        <button type="button" class="btn btn-small btn-danger"
                            onclick="confirmDelete(<?php echo (int) $dept['hospital_department_id']; ?>, <?php echo htmlspecialchars(json_encode($dept['name_en'])); ?>, this)">
                            Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add / Edit Department Modal -->
<div id="departmentModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modalTitle" hidden>
    <div class="modal-box">
        <h3 id="modalTitle">Add Department</h3>
        <form method="POST" id="departmentForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="hospital_department_id" id="fieldHdId" value="">
            <input type="hidden" name="department_id" id="fieldDeptId" value="">

            <div class="form-group" id="attachGroup">
                <label for="attach_department_id">Attach an existing department (optional)</label>
                <select id="attach_department_id" name="attach_department_id">
                    <option value="0">— Create a new department —</option>
                    <?php foreach ($linkable as $opt): ?>
                        <option value="<?php echo (int) $opt['id']; ?>">
                            <?php echo htmlspecialchars($opt['name_en']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="fieldNameEn">Department Name (English) *</label>
                <input type="text" id="fieldNameEn" name="name_en" maxlength="100" required>
                <small class="field-error" id="errNameEn"></small>
            </div>

            <div class="form-group">
                <label for="fieldNameNe">Department Name (Nepali)</label>
                <input type="text" id="fieldNameNe" name="name_ne" maxlength="100">
            </div>

            <div class="form-group">
                <label for="fieldDescEn">Description (English)</label>
                <textarea id="fieldDescEn" name="description_en" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label for="fieldDescNe">Description (Nepali)</label>
                <textarea id="fieldDescNe" name="description_ne" rows="2"></textarea>
            </div>

            <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="form-group">
                    <label for="fieldCapacity">Max Tokens / Day</label>
                    <input type="number" id="fieldCapacity" name="max_capacity" min="1" max="1000" value="50">
                </div>
                <div class="form-group">
                    <label for="fieldServiceTime">Avg Service Time (mins)</label>
                    <input type="number" id="fieldServiceTime" name="avg_service_time" min="1" max="480" value="30">
                </div>
            </div>

            <div class="form-group">
                <label for="fieldActive">Availability</label>
                <select id="fieldActive" name="is_active">
                    <option value="1">Available</option>
                    <option value="0">Unavailable</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary" id="modalSubmit">Save Department</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete confirmation modal -->
<div id="deleteModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="deleteTitle" hidden>
    <div class="modal-box modal-box-sm">
        <h3 id="deleteTitle">Delete department?</h3>
        <p id="deleteText">This will remove the department from your hospital.</p>
        <form method="POST" id="deleteForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="hospital_department_id" id="deleteHdId" value="">
            <div class="modal-actions">
                <button type="submit" class="btn btn-danger">Yes, delete</button>
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="toast" role="status" aria-live="polite" hidden></div>

<script>
(function () {
    const modal = document.getElementById('departmentModal');
    const deleteModal = document.getElementById('deleteModal');
    const form = document.getElementById('departmentForm');
    let addEditTrigger = null;
    let deleteTrigger = null;

    function showModal(m) {
        m.hidden = false;
        document.body.classList.add('modal-open');
    }
    function hideModal(m) {
        if (m.hidden) return;
        m.hidden = true;
        if (modal.hidden && deleteModal.hidden) document.body.classList.remove('modal-open');
    }

    window.openAddModal = function (trigger) {
        addEditTrigger = trigger || document.activeElement;
        document.getElementById('modalTitle').textContent = 'Add Department';
        document.getElementById('formAction').value = 'add';
        document.getElementById('fieldHdId').value = '';
        document.getElementById('fieldDeptId').value = '';
        form.reset();
        document.getElementById('attachGroup').style.display = '';
        document.getElementById('fieldCapacity').value = '50';
        document.getElementById('fieldServiceTime').value = '30';
        document.getElementById('errNameEn').textContent = '';
        showModal(modal);
        document.getElementById('fieldNameEn').focus();
    };

    window.openEditModal = function (d, trigger) {
        addEditTrigger = trigger || document.activeElement;
        document.getElementById('modalTitle').textContent = 'Edit Department';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('fieldHdId').value = d.id;
        document.getElementById('fieldDeptId').value = d.department_id;
        document.getElementById('fieldNameEn').value = d.name_en || '';
        document.getElementById('fieldNameNe').value = d.name_ne || '';
        document.getElementById('fieldDescEn').value = d.description_en || '';
        document.getElementById('fieldDescNe').value = d.description_ne || '';
        document.getElementById('fieldCapacity').value = d.max_tokens_per_day || 50;
        document.getElementById('fieldServiceTime').value = d.avg_service_time || 30;
        document.getElementById('fieldActive').value = d.available ? '1' : '0';
        document.getElementById('attachGroup').style.display = 'none';
        document.getElementById('errNameEn').textContent = '';
        showModal(modal);
        document.getElementById('fieldNameEn').focus();
    };

    window.closeModal = function () {
        hideModal(modal);
        if (addEditTrigger && document.body.contains(addEditTrigger)) addEditTrigger.focus();
    };
    window.closeDeleteModal = function () {
        hideModal(deleteModal);
        if (deleteTrigger && document.body.contains(deleteTrigger)) deleteTrigger.focus();
    };

    window.confirmDelete = function (id, name, trigger) {
        deleteTrigger = trigger || document.activeElement;
        document.getElementById('deleteHdId').value = id;
        document.getElementById('deleteText').textContent =
            'Delete "' + name + '" from your hospital? If it has existing tokens or records it will be detached but preserved.';
        showModal(deleteModal);
        const firstBtn = deleteModal.querySelector('button');
        if (firstBtn) firstBtn.focus();
    };

    form.addEventListener('submit', function (e) {
        const name = document.getElementById('fieldNameEn').value.trim();
        if (!name) {
            e.preventDefault();
            document.getElementById('errNameEn').textContent = 'Please enter a department name.';
            document.getElementById('fieldNameEn').focus();
        }
    });

    // Close on overlay click / Escape
    [modal, deleteModal].forEach(function (m) {
        m.addEventListener('click', function (e) {
            if (e.target !== m) return;
            if (m === deleteModal) { window.closeDeleteModal(); } else { window.closeModal(); }
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (!deleteModal.hidden) { window.closeDeleteModal(); }
        else if (!modal.hidden) { window.closeModal(); }
    });
})();
</script>

<script>
// Server-side flash feedback -> toast
(function () {
    const flash = <?php echo json_encode($flash ?: null); ?>;
    if (!flash) return;
    const toast = document.getElementById('toast');
    toast.textContent = flash.message;
    toast.className = 'toast toast-' + (flash.type || 'success');
    toast.hidden = false;
    setTimeout(function () { toast.hidden = true; }, 4000);
})();
</script>

<style>
.page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
.page-sub { color: #8fa8ba; font-size: 13px; margin-top: 6px; }
.departments-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; }
.department-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 18px; display: flex; flex-direction: column; gap: 4px; transition: box-shadow .2s ease, transform .2s ease; }
.department-card:hover { box-shadow: 0 6px 18px rgba(15, 23, 42, .08); transform: translateY(-2px); }
.department-card-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 6px; }
.department-card h4 { color: #2c3e50; font-size: 15px; font-weight: 600; margin: 0; }
.dept-ne { color: #8fa8ba; font-size: 12px; margin-bottom: 6px; }
.department-card p { font-size: 13px; color: #4b5563; margin: 2px 0; }
.department-card p strong { color: #2c3e50; }
.department-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.department-actions form { margin: 0; }
.modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, .5); display: flex; align-items: center; justify-content: center; z-index: 2000; padding: 16px; }
.modal-box { background: #fff; border-radius: 10px; max-width: 560px; width: 100%; padding: 26px; max-height: 92vh; overflow-y: auto; box-shadow: 0 20px 50px rgba(0,0,0,.3); }
.modal-box-sm { max-width: 420px; }
.modal-box h3 { margin-bottom: 18px; }
.modal-actions { display: flex; gap: 10px; margin-top: 20px; }
.field-error { color: #c0392b; font-size: 12px; margin-top: 4px; min-height: 14px; }
.toast { position: fixed; right: 24px; bottom: 24px; z-index: 3000; padding: 14px 18px; border-radius: 8px; color: #fff; font-size: 14px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,.25); max-width: 360px; }
.toast-success { background: #27ae60; }
.toast-warning { background: #f39c12; }
.toast-error { background: #e74c3c; }
@media (max-width: 640px) {
    .department-actions .btn { flex: 1 1 auto; }
    .toast { left: 16px; right: 16px; }
}
</style>

<?php
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
?>
