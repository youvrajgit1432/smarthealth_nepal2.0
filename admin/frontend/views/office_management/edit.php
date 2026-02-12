<?php
/**
 * Admin - Edit Department
 * Form to edit department information
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
$dept_id = $_GET['id'] ?? null;
$message = '';

if (!$dept_id) {
    header('Location: list.php');
    exit;
}

$dept_result = $officeController->getDepartmentById($dept_id);
if (!$dept_result['success']) {
    $message = '<div class="alert alert-danger">Department not found</div>';
    $dept = null;
} else {
    $dept = $dept_result['department'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dept) {
    $dept_name = $_POST['dept_name'] ?? '';
    $capacity = $_POST['capacity'] ?? 0;
    $avg_service_time = $_POST['avg_service_time'] ?? 15;
    
    $updateData = [
        'dept_name' => $dept_name,
        'capacity' => $capacity,
        'avg_service_time' => $avg_service_time
    ];
    
    $result = $officeController->updateDepartment($dept_id, $updateData);
    
    if ($result['success']) {
        $message = '<div class="alert alert-success alert-dismissible fade show">
                      Department updated successfully
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
        // Refresh department data
        $dept_result = $officeController->getDepartmentById($dept_id);
        $dept = $dept_result['department'];
    } else {
        $message = '<div class="alert alert-danger alert-dismissible fade show">
                      Failed to update department
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

$pageTitle = $lang['edit_department'] ?? 'Edit Department';
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> <?php echo $lang['edit_department'] ?? 'Edit Department'; ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <?php if ($dept): ?>
                    
                    <!-- Department Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><?php echo $lang['current_status'] ?? 'Current Status'; ?></h6>
                            <dl class="row">
                                <dt class="col-sm-6"><?php echo $lang['capacity'] ?? 'Capacity'; ?></dt>
                                <dd class="col-sm-6"><strong><?php echo $dept['capacity']; ?></strong></dd>
                                
                                <dt class="col-sm-6"><?php echo $lang['current_load'] ?? 'Load'; ?></dt>
                                <dd class="col-sm-6"><strong><?php echo $dept['current_load']; ?></strong></dd>
                                
                                <dt class="col-sm-6"><?php echo $lang['utilization'] ?? 'Util'; ?></dt>
                                <dd class="col-sm-6">
                                    <?php 
                                    $util = round(($dept['current_load'] / $dept['capacity']) * 100, 2);
                                    ?>
                                    <strong><?php echo $util; ?>%</strong>
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6><?php echo $lang['performance'] ?? 'Performance'; ?></h6>
                            <dl class="row">
                                <dt class="col-sm-6"><?php echo $lang['avg_service_time'] ?? 'Avg Time'; ?></dt>
                                <dd class="col-sm-6"><strong><?php echo $dept['avg_service_time']; ?> min</strong></dd>
                            </dl>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Edit Form -->
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['department_name'] ?? 'Department Name'; ?> *</label>
                            <input type="text" name="dept_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($dept['dept_name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['capacity'] ?? 'Capacity'; ?> *</label>
                            <input type="number" name="capacity" class="form-control" 
                                   value="<?php echo $dept['capacity']; ?>" required min="1">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['avg_service_time'] ?? 'Avg Service Time (minutes)'; ?></label>
                            <input type="number" name="avg_service_time" class="form-control" 
                                   value="<?php echo $dept['avg_service_time']; ?>" min="5" max="120">
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $lang['save_changes'] ?? 'Save Changes'; ?>
                            </button>
                            <a href="list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> <?php echo $lang['back'] ?? 'Back'; ?>
                            </a>
                        </div>
                    </form>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
