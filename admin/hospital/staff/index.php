<?php
/**
 * Hospital Staff Management
 */

// Central hospital-context resolution (auth + hospital/role rehydration).
require_once __DIR__ . '/../includes/context.php';
require_once __DIR__ . '/../../../backend/helpers/CsrfHelper.php';

$error = '';
$success = '';
$staff = [];
$dept_options = [];
$editing = null;
$pageTitle = 'Staff Management';
$activePage = 'staff';

try {
    require_once __DIR__ . '/../../backend/config/database.php';
    
    // Check connection
    if (!isset($db) || $db->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if (!csrf_verify()) {
            throw new Exception('Your session expired. Please refresh the page and try again.');
        }
        if ($_POST['action'] === 'add_staff') {
            $query = "INSERT INTO hospital_staff 
                     (hospital_id, name, position, department_id, email, phone, admin_id, status, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $db->prepare($query);
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            
            $name     = trim($_POST['name'] ?? '');
            $position = trim($_POST['position'] ?? '');
            $status   = in_array($_POST['status'] ?? '', ['Active', 'Inactive', 'Leave'], true) ? $_POST['status'] : 'Active';
            $dept_id  = ($_POST['department_id'] ?? '') !== '' ? (int) $_POST['department_id'] : null;
            $email    = trim($_POST['email'] ?? '') !== '' ? trim($_POST['email']) : null;
            $phone    = trim($_POST['phone'] ?? '') !== '' ? trim($_POST['phone']) : null;
            if ($name === '' || $position === '') {
                throw new Exception('Staff name and position are required.');
            }
            $admin_id = (int) $_SESSION['admin_id'];

            $stmt->bind_param(
                'ississis',
                $hospital_id,
                $name,
                $position,
                $dept_id,
                $email,
                $phone,
                $admin_id,
                $status
            );
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception('Insert failed: ' . $stmt->error);
            }
            $stmt->close();
            $success = 'Staff added successfully!';
        } elseif ($_POST['action'] === 'update_staff') {
            $query = "UPDATE hospital_staff SET 
                     name = ?, position = ?, department_id = ?, 
                     email = ?, phone = ?, status = ?
                     WHERE id = ? AND hospital_id = ?";
            
            $stmt = $db->prepare($query);
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            
            $name     = trim($_POST['name'] ?? '');
            $position = trim($_POST['position'] ?? '');
            $status   = in_array($_POST['status'] ?? '', ['Active', 'Inactive', 'Leave'], true) ? $_POST['status'] : 'Active';
            $dept_id  = ($_POST['department_id'] ?? '') !== '' ? (int) $_POST['department_id'] : null;
            $email    = trim($_POST['email'] ?? '') !== '' ? trim($_POST['email']) : null;
            $phone    = trim($_POST['phone'] ?? '') !== '' ? trim($_POST['phone']) : null;
            $staff_id = (int) ($_POST['staff_id'] ?? 0);
            if ($staff_id <= 0 || $name === '' || $position === '') {
                throw new Exception('Please complete all required fields.');
            }

            $stmt->bind_param(
                'ssisssii',
                $name,
                $position,
                $dept_id,
                $email,
                $phone,
                $status,
                $staff_id,
                $hospital_id
            );
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception('Update failed: ' . $stmt->error);
            }
            $stmt->close();
            $success = 'Staff member updated successfully!';
        } elseif ($_POST['action'] === 'delete_staff') {
            $query = "UPDATE hospital_staff SET is_active = 0 WHERE id = ? AND hospital_id = ?";
            $stmt = $db->prepare($query);
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            $staff_id = (int) ($_POST['staff_id'] ?? 0);
            if ($staff_id <= 0) {
                throw new Exception('Invalid staff member.');
            }
            $stmt->bind_param('ii', $staff_id, $hospital_id);
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception('Delete failed: ' . $stmt->error);
            }
            $stmt->close();
            $success = 'Staff member removed!';
        }
    }

    // Fetch staff
    if ($hospital_id) {
        $query = "SELECT hs.*, d.name_en as department FROM hospital_staff hs
                 LEFT JOIN departments d ON hs.department_id = d.id
                 WHERE hs.hospital_id = ? AND hs.is_active = 1
                 ORDER BY hs.position, hs.name";
        
        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception('Database error: ' . $db->error);
        }
        $stmt->bind_param('i', $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Departments this hospital actually offers (for the dropdown)
        $dQuery = "SELECT d.id, d.name_en
                     FROM hospital_departments hd
                     JOIN departments d ON hd.department_id = d.id
                    WHERE hd.hospital_id = ? AND hd.is_active = 1
                    ORDER BY d.name_en";
        $dStmt = $db->prepare($dQuery);
        if ($dStmt) {
            $dStmt->bind_param('i', $hospital_id);
            $dStmt->execute();
            $dept_options = $dStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $dStmt->close();
        }
    }

    // Optional edit target (?edit=<id>) — scoped to staff of this hospital.
    $editId = $_SERVER['REQUEST_METHOD'] === 'GET' ? (int) ($_GET['edit'] ?? 0) : 0;
    if ($editId > 0) {
        foreach ($staff as $row) {
            if ((int) $row['id'] === $editId) {
                $editing = $row;
                break;
            }
        }
        if ($editing) {
            $pageTitle = 'Edit Staff';
        }
    }
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}

// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

        <h1>Hospital Staff Management</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Add / Edit Staff Form -->
        <div class="form-card">
            <h2 style="margin-bottom: 15px;"><?php echo $editing ? 'Edit Staff Member' : 'Add New Staff Member'; ?></h2>
            <form method="POST" action="">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="<?php echo $editing ? 'update_staff' : 'add_staff'; ?>">
                <?php if ($editing): ?>
                    <input type="hidden" name="staff_id" value="<?php echo (int) $editing['id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($editing['name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="position">Position *</label>
                        <input type="text" id="position" name="position" placeholder="e.g., Doctor, Nurse" required value="<?php echo htmlspecialchars($editing['position'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id">
                            <option value="">Select Department</option>
                            <?php foreach ($dept_options as $opt): ?>
                                <option value="<?php echo (int) $opt['id']; ?>" <?php echo ((int) ($editing['department_id'] ?? 0) === (int) $opt['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($opt['name_en']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($editing['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($editing['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach (['Active', 'Inactive', 'Leave'] as $st): ?>
                                <option value="<?php echo $st; ?>" <?php echo (($editing['status'] ?? 'Active') === $st) ? 'selected' : ''; ?>><?php echo $st; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Save Changes' : 'Add Staff Member'; ?></button>
                <?php if ($editing): ?>
                    <a href="?" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Staff List -->
        <div class="staff-table">
            <h2 style="padding: 20px; border-bottom: 1px solid #ddd; margin: 0;">Current Staff</h2>
            
            <?php if (empty($staff)): ?>
                <div class="empty-state">
                    <p>No staff members found.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff as $member): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($member['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($member['position']); ?></td>
                                <td><?php echo htmlspecialchars($member['department'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($member['email'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($member['phone'] ?? '-'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($member['status']); ?>">
                                        <?php echo htmlspecialchars($member['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a class="action-btn" href="?edit=<?php echo (int) $member['id']; ?>" style="display: inline-block; text-decoration: none;">Edit</a>
                                        <form method="POST" style="display: inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete_staff">
                                            <input type="hidden" name="staff_id" value="<?php echo (int) $member['id']; ?>">
                                            <button type="submit" class="action-btn" style="background: #f8d7da; color: #842029;" onclick="return confirm('Remove this staff member?')">Remove</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px;">
            <a href="/smarthealth_nepal/admin/hospital/dashboard/">← Back to Dashboard</a>
        </div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
