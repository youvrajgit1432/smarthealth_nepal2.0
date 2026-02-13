<?php
/**
 * Admin Dashboard
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
$is_superadmin = strtolower($_SESSION['admin_role']) === 'superadmin' || $_SESSION['admin_role'] === 'SuperAdmin';
$hospital_id = $_SESSION['hospital_id'] ?? null;

// Get dashboard statistics
// Count Active Tokens today
$activeTokensQuery = "SELECT COUNT(*) as count FROM tokens t 
                      WHERE t.status = 'Active' AND DATE(t.created_at) = CURDATE()";
if (!$is_superadmin && $hospital_id) {
    $activeTokensQuery .= " AND t.hospital_id = " . (int)$hospital_id;
}
$activeTokensResult = $db->query($activeTokensQuery);
$activeTokens = $activeTokensResult ? $activeTokensResult->fetch_assoc()['count'] ?? 0 : 0;

// Count Total Patients (users with tokens)
$totalPatientsQuery = "SELECT COUNT(DISTINCT u.id) as count FROM users u 
                      INNER JOIN tokens t ON u.id = t.user_id";
if (!$is_superadmin && $hospital_id) {
    $totalPatientsQuery .= " WHERE t.hospital_id = " . (int)$hospital_id;
}
$totalPatientsResult = $db->query($totalPatientsQuery);
$totalPatients = $totalPatientsResult ? $totalPatientsResult->fetch_assoc()['count'] ?? 0 : 0;

// Count Completed Tokens today
$completedTokensQuery = "SELECT COUNT(*) as count FROM tokens t 
                        WHERE t.status = 'Completed' AND DATE(t.created_at) = CURDATE()";
if (!$is_superadmin && $hospital_id) {
    $completedTokensQuery .= " AND t.hospital_id = " . (int)$hospital_id;
}
$completedTokensResult = $db->query($completedTokensQuery);
$completedTokens = $completedTokensResult ? $completedTokensResult->fetch_assoc()['count'] ?? 0 : 0;

// Get department loads
if ($is_superadmin) {
    // SuperAdmin sees all department stats across all hospitals
    $departmentsQuery = "SELECT d.id, d.name_en, d.name_ne, d.max_capacity, d.current_load,
                        COUNT(t.id) as active_count
                        FROM departments d 
                        LEFT JOIN tokens t ON d.id = t.department_id AND t.status = 'Active' AND DATE(t.created_at) = CURDATE()
                        GROUP BY d.id 
                        ORDER BY active_count DESC LIMIT 5";
} else if ($hospital_id) {
    // Hospital admin sees only their hospital's departments
    // First try to get from hospital_departments mapping
    $departmentsQuery = "SELECT DISTINCT d.id, d.name_en, d.name_ne, d.max_capacity, d.current_load,
                        COUNT(t.id) as active_count, 
                        COALESCE(hd.max_tokens_per_day, d.max_capacity) as max_tokens,
                        COALESCE(hd.current_daily_tokens, 0) as daily_tokens
                        FROM hospital_departments hd
                        INNER JOIN departments d ON hd.department_id = d.id
                        LEFT JOIN tokens t ON d.id = t.department_id AND t.hospital_id = " . (int)$hospital_id . 
                        " AND t.status = 'Active' AND DATE(t.created_at) = CURDATE()
                        WHERE hd.hospital_id = " . (int)$hospital_id . "
                        GROUP BY d.id 
                        ORDER BY active_count DESC LIMIT 5";
} else {
    // Admin without hospital_id - show all departments
    $departmentsQuery = "SELECT d.id, d.name_en, d.name_ne, d.max_capacity, d.current_load,
                        COUNT(t.id) as active_count
                        FROM departments d 
                        LEFT JOIN tokens t ON d.id = t.department_id AND t.status = 'Active' AND DATE(t.created_at) = CURDATE()
                        GROUP BY d.id 
                        ORDER BY active_count DESC LIMIT 5";
}
$departments = $db->query($departmentsQuery);

// If hospital departments query returned no results, fallback to general query
if ($departments && $departments->num_rows === 0 && !$is_superadmin && $hospital_id) {
    $departmentsQuery = "SELECT d.id, d.name_en, d.name_ne, d.max_capacity, d.current_load,
                        COUNT(t.id) as active_count
                        FROM departments d 
                        LEFT JOIN tokens t ON d.id = t.department_id AND t.status = 'Active' AND DATE(t.created_at) = CURDATE()
                        GROUP BY d.id 
                        ORDER BY active_count DESC LIMIT 5";
    $departments = $db->query($departmentsQuery);
}

$pageTitle = $lang['welcome_admin'] ?? 'Admin Dashboard';
$activePage = 'dashboard';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4"><?php echo $lang['welcome_admin'] ?? 'Dashboard'; ?></h2>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted"><?php echo $lang['active_tokens'] ?? 'Active Tokens'; ?></h6>
                    <h2 class="text-primary"><?php echo $activeTokens; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted"><?php echo $lang['total_patients'] ?? 'Total Patients'; ?></h6>
                    <h2 class="text-success"><?php echo $totalPatients; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Completed Today</h6>
                    <h2 class="text-info"><?php echo $completedTokens; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted"><?php echo $lang['queue_status'] ?? 'Queue Status'; ?></h6>
                    <h5><span class="badge bg-success">Operational</span></h5>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Department Status -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Department Load Status</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Active Tokens</th>
                                <th>Capacity</th>
                                <th>Load %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($departments && $departments->num_rows > 0): ?>
                                <?php while ($dept = $departments->fetch_assoc()): 
                                    $active = $dept['active_count'] ?? 0;
                                    $max_cap = $dept['max_capacity'] ?? 50;
                                    $load_percent = ($max_cap > 0) ? round(($active / $max_cap) * 100, 1) : 0;
                                    $dept_name = $dept['name_en'] ?? 'Department';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dept_name); ?></td>
                                    <td><?php echo $active; ?></td>
                                    <td><?php echo $max_cap; ?></td>
                                    <td>
                                        <div class="progress" style="width: 100px;">
                                            <div class="progress-bar <?php echo $load_percent > 80 ? 'bg-danger' : ($load_percent > 50 ? 'bg-warning' : 'bg-success'); ?>" 
                                                 style="width: <?php echo $load_percent; ?>%"></div>
                                        </div>
                                        <small><?php echo $load_percent; ?>%</small>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No department data available</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/smarthealth_nepal/admin/frontend/views/token_management/active.php" class="btn btn-primary">
                            <i class="fas fa-ticket-alt"></i> Manage Tokens
                        </a>
                        <a href="/smarthealth_nepal/admin/frontend/views/user_management/index.php" class="btn btn-success">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                        <a href="/smarthealth_nepal/admin/frontend/views/service_management/approve.php" class="btn btn-warning">
                            <i class="fas fa-tasks"></i> Approve Services
                        </a>
                        <a href="/smarthealth_nepal/admin/frontend/views/office_management/list.php" class="btn btn-danger">
                            <i class="fas fa-building"></i> Manage Offices
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
