<?php
/**
 * Hospital Tokens Management
 */

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

$hospital_id = $_GET['hospital_id'] ?? $_SESSION['hospital_id'] ?? null;
$error = '';
$success = '';
$tokens = [];
$filter_status = $_GET['status'] ?? '';
$filter_date = $_GET['date'] ?? date('Y-m-d');
$pageTitle = 'Manage Tokens';
$activePage = 'tokens';

try {
    require_once __DIR__ . '/../../backend/config/database.php';
    
    // Check connection
    if (!isset($db) || $db->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update_status') {
            $query = "UPDATE tokens SET status = ?, updated_at = NOW() WHERE id = ? AND hospital_id = ?";
            $stmt = $db->prepare($query);
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            $stmt->bind_param('sii', $_POST['status'], $_POST['token_id'], $hospital_id);
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception('Update failed: ' . $stmt->error);
            }
            $stmt->close();
            $success = 'Token status updated successfully!';
        }
    }

    // Fetch tokens
    if ($hospital_id) {
        $query = "SELECT t.*, d.name_en as department, u.full_name, u.phone_number
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 LEFT JOIN users u ON t.user_id = u.id
                 WHERE t.hospital_id = ?";
        
        $types = 'i';
        $bind_params = [&$hospital_id];

        if ($filter_status) {
            $query .= " AND t.status = ?";
            $types .= 's';
            $bind_params[] = &$filter_status;
        }

        $query .= " AND DATE(t.created_at) = ? ORDER BY t.token_number DESC";
        $types .= 's';
        $bind_params[] = &$filter_date;

        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception('Database error: ' . $db->error);
        }
        
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $bind_params));
        $stmt->execute();
        $result = $stmt->get_result();
        $tokens = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}

// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

        <h1>Token Management</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <div class="filter-group">
                    <label for="date">Select Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>">
                </div>

                <div class="filter-group">
                    <label for="status">Filter by Status:</label>
                    <select id="status" name="status">
                        <option value="">All</option>
                        <option value="Pending" <?php echo $filter_status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Running" <?php echo $filter_status === 'Running' ? 'selected' : ''; ?>>Running</option>
                        <option value="Completed" <?php echo $filter_status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo $filter_status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <button type="submit" style="padding: 8px 15px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Filter
                </button>
            </form>
        </div>

        <!-- Tokens Table -->
        <div class="tokens-table">
            <?php if (empty($tokens)): ?>
                <div class="empty-state">
                    <p>No tokens found for the selected date and filters.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Token #</th>
                            <th>Patient Name</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tokens as $token): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($token['token_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($token['full_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($token['phone_number'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($token['department']); ?></td>
                                <td>
                                    <span class="priority-badge <?php echo strtolower($token['priority']); ?>">
                                        <?php echo htmlspecialchars($token['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($token['status']); ?>">
                                        <?php echo htmlspecialchars($token['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('H:i', strtotime($token['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="action-select">
                                            <option value="<?php echo $token['status']; ?>" selected>Update...</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Running">Running</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </form>
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
