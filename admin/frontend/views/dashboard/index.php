<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../../backend/init.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

// Load language
$lang_file = __DIR__ . '/../../backend/lang/' . ($_SESSION['admin_language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Get dashboard statistics
$activeTokens = $db->query("SELECT COUNT(*) as count FROM tokens WHERE status = 'Active' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0;
$totalPatients = $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] ?? 0;
$completedTokens = $db->query("SELECT COUNT(*) as count FROM tokens WHERE status = 'Completed' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0;

// Get department loads
$departments = $db->query("SELECT * FROM departments ORDER BY current_load DESC LIMIT 5");

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
                                <th>Load</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($dept = $departments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $dept['name_en']; ?></td>
                                <td><?php echo $dept['current_load']; ?></td>
                                <td><?php echo $dept['max_capacity']; ?></td>
                                <td>
                                    <div class="progress" style="width: 100px;">
                                        <div class="progress-bar" style="width: <?php echo ($dept['current_load'] / $dept['max_capacity']) * 100; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
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
