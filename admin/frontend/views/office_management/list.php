<?php
/**
 * Admin - Department Management
 * List all departments with capacity and load information
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

// Get departments
$where_clause = "WHERE is_active = 1";
if (!$is_superadmin && $hospital_id) {
    $where_clause .= " AND hospital_id = " . (int)$hospital_id;
}

$sql = "SELECT * FROM departments " . $where_clause . " ORDER BY name_en ASC";
$result = $db->query($sql);
$departments = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

// Get statistics
$stats_result = $db->query("SELECT 
    COUNT(*) as total_departments,
    SUM(max_capacity) as total_capacity,
    SUM(current_load) as total_load
FROM departments " . $where_clause);
$stats = $stats_result->fetch_assoc();
$stats['utilization_percent'] = $stats['total_capacity'] > 0 ? round(($stats['total_load'] / $stats['total_capacity']) * 100) : 0;

$pageTitle = $lang['office_management'] ?? 'Department Management';
$activePage = 'office_management';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4"><?php echo $lang['departments'] ?? 'Department Management'; ?></h2>
    
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted"><?php echo $lang['total_departments'] ?? 'Departments'; ?></h6>
                    <h2 class="text-primary"><?php echo $stats['total_departments']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted"><?php echo $lang['total_capacity'] ?? 'Total Capacity'; ?></h6>
                    <h2 class="text-success"><?php echo $stats['total_capacity']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted"><?php echo $lang['current_load'] ?? 'Current Load'; ?></h6>
                    <h2 class="text-warning"><?php echo $stats['total_load']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted"><?php echo $lang['utilization'] ?? 'Utilization'; ?></h6>
                    <h2 class="text-danger"><?php echo $stats['utilization_percent']; ?>%</h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Departments Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-hospital"></i> <?php echo $lang['departments'] ?? 'Departments'; ?>
            </h5>
            <a href="add.php" class="btn btn-light btn-sm">
                <i class="fas fa-plus"></i> <?php echo $lang['add_department'] ?? 'Add'; ?>
            </a>
        </div>
        
        <div class="card-body">
            <?php if (empty($departments)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <?php echo $lang['no_departments'] ?? 'No departments defined'; ?>
            </div>
            <?php else: ?>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $lang['department'] ?? 'Department'; ?></th>
                            <th><?php echo $lang['capacity'] ?? 'Capacity'; ?></th>
                            <th><?php echo $lang['current_load'] ?? 'Load'; ?></th>
                            <th><?php echo $lang['utilization'] ?? 'Utilization'; ?></th>
                            <th><?php echo $lang['avg_service_time'] ?? 'Avg Time'; ?></th>
                            <th><?php echo $lang['actions'] ?? 'Actions'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $dept): 
                            $load_percent = $dept['max_capacity'] > 0 ? round(($dept['current_load'] / $dept['max_capacity']) * 100) : 0;
                            $load_color = $load_percent < 50 ? 'success' : ($load_percent < 80 ? 'warning' : 'danger');
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($dept['name_en']); ?></strong>
                            </td>
                            <td><?php echo $dept['max_capacity']; ?></td>
                            <td><?php echo $dept['current_load']; ?></td>
                            <td>
                                <div class="progress" style="width: 100px; height: 20px;">
                                    <div class="progress-bar bg-<?php echo $load_color; ?>" 
                                         style="width: <?php echo $load_percent; ?>%">
                                        <?php echo $load_percent; ?>%
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $dept['avg_service_time']; ?> min</td>
                            <td>
                                <a href="edit.php?id=<?php echo $dept['id']; ?>" class="btn btn-sm btn-warning">
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
