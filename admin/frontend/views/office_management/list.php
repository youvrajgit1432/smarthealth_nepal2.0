<?php
/**
 * Admin - Department Management
 * List all departments with capacity and load information
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/OfficeController.php';

// Check admin login
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /smarthealth_nepal/admin/public/index.php?page=login');
    exit;
}

// Load admin language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

$officeController = new OfficeController($db);
$departments = $officeController->getAllDepartments();
$stats = $officeController->getDepartmentStats();

$pageTitle = $lang['office_management'] ?? 'Office Management';
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang['total_departments'] ?? 'Departments'; ?></h5>
                            <h2 class="text-primary"><?php echo $stats['total_departments']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang['total_capacity'] ?? 'Capacity'; ?></h5>
                            <h2 class="text-success"><?php echo $stats['total_capacity']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang['current_load'] ?? 'Load'; ?></h5>
                            <h2 class="text-warning"><?php echo $stats['total_load']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $lang['utilization'] ?? 'Util'; ?></h5>
                            <h2 class="text-danger"><?php echo $stats['utilization_percent']; ?>%</h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Departments Table -->
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-hospital"></i> <?php echo $lang['departments'] ?? 'Departments'; ?>
                    </h5>
                    <a href="add.php" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> <?php echo $lang['add_department'] ?? 'Add Department'; ?>
                    </a>
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($departments)): ?>
                    <div class="alert alert-info m-3">
                        <?php echo $lang['no_departments'] ?? 'No departments defined'; ?>
                    </div>
                    <?php else: ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
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
                                <?php foreach ($departments as $dept): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($dept['dept_name']); ?></strong>
                                    </td>
                                    <td><?php echo $dept['capacity']; ?></td>
                                    <td><?php echo $dept['current_load']; ?></td>
                                    <td>
                                        <div class="progress" style="width: 100px; height: 20px;">
                                            <div class="progress-bar bg-<?php echo $dept['load_color']; ?>" 
                                                 style="width: <?php echo $dept['load_percentage']; ?>%">
                                                <?php echo $dept['load_percentage']; ?>%
                                            </div>
                                        </div>
                                        <small><?php echo $dept['load_status']; ?></small>
                                    </td>
                                    <td><?php echo $dept['avg_service_time']; ?> min</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit.php?id=<?php echo $dept['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            
                                            <form method="POST" action="/smarthealth_nepal/admin/api/delete_department.php" 
                                                  style="display:inline;">
                                                <input type="hidden" name="dept_id" value="<?php echo $dept['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Delete this department?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
