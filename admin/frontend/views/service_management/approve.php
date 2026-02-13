<?php
/**
 * Admin - Service Approval
 * Approve or reject pending services
 */

require_once __DIR__ . '/../../../backend/init.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['admin_language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Determine if user is superadmin and set hospital filter
$is_superadmin = $_SESSION['admin_role'] === 'superadmin';
$hospital_id = $_SESSION['hospital_id'] ?? null;

// Build hospital filter for services
$hospital_filter = '';
if (!$is_superadmin && $hospital_id) {
    $hospital_filter = " AND d.hospital_id = " . (int)$hospital_id;
}

// Get services
$sql = "SELECT s.*, d.name_en as dept_name FROM services s 
        LEFT JOIN departments d ON s.department_id = d.id 
        WHERE s.is_active = 1" . $hospital_filter . "
        ORDER BY s.created_at DESC";

$result = $db->query($sql);
$services = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

$pageTitle = $lang['approve_services'] ?? 'Service Management';
$activePage = 'service_management';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4"><?php echo $lang['approve_services'] ?? 'Service Management'; ?></h2>
    
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-check-circle"></i> <?php echo $lang['active_services'] ?? 'Active Services'; ?>
            </h5>
        </div>
        
        <div class="card-body">
            <?php if (empty($services)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <?php echo $lang['no_services'] ?? 'No services available'; ?>
            </div>
            <?php else: ?>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $lang['service_name'] ?? 'Service'; ?></th>
                            <th><?php echo $lang['type'] ?? 'Type'; ?></th>
                            <th><?php echo $lang['description'] ?? 'Description'; ?></th>
                            <th><?php echo $lang['status'] ?? 'Status'; ?></th>
                            <th><?php echo $lang['actions'] ?? 'Actions'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($service['name_en'] ?? 'N/A'); ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo htmlspecialchars($service['type'] ?? 'Regular'); ?>
                                </span>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars(substr($service['description_en'] ?? '', 0, 50)); ?></small>
                            </td>
                            <td>
                                <?php if ($service['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
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

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
