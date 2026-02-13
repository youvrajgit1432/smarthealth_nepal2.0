<?php
/**
 * Hospital Staff Management
 */

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

$hospital_id = $_GET['hospital_id'] ?? $_SESSION['hospital_id'] ?? null;
$error = '';
$success = '';
$staff = [];
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
        if ($_POST['action'] === 'add_staff') {
            $query = "INSERT INTO hospital_staff 
                     (hospital_id, name, position, department_id, email, phone, admin_id, status, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $db->prepare($query);
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            
            $status = $_POST['status'] ?? 'Active';
            $dept_id = $_POST['department_id'] ?? null;
            $email = $_POST['email'] ?? null;
            $phone = $_POST['phone'] ?? null;
            
            $stmt->bind_param('issiiisss', 
                $hospital_id, 
                $_POST['name'], 
                $_POST['position'], 
                $dept_id,
                $email,
                $phone,
                $_SESSION['admin_id'],
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
            
            $dept_id = $_POST['department_id'] ?? null;
            $email = $_POST['email'] ?? null;
            $phone = $_POST['phone'] ?? null;
            $staff_id = $_POST['staff_id'];
            
            $stmt->bind_param('ssissiis', 
                $_POST['name'],
                $_POST['position'],
                $dept_id,
                $email,
                $phone,
                $_POST['status'],
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
            $staff_id = $_POST['staff_id'];
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

        <!-- Add Staff Form -->
        <div class="form-card">
            <h2 style="margin-bottom: 15px;">Add New Staff Member</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_staff">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="position">Position *</label>
                        <input type="text" id="position" name="position" placeholder="e.g., Doctor, Nurse" required>
                    </div>

                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id">
                            <option value="">Select Department</option>
                            <option value="1">General Medicine</option>
                            <option value="2">Emergency</option>
                            <option value="3">Maternal Health</option>
                            <option value="7">Cardiology</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Leave">Leave</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Add Staff Member</button>
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
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_staff">
                                            <input type="hidden" name="staff_id" value="<?php echo $member['id']; ?>">
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
