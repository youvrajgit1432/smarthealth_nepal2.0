<?php
/**
 * Admin - Add Department
 * Form to create new departments
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../admin/backend/controllers/OfficeController.php';

// Check admin login
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /smarthealth_nepal/admin/public/index.php?page=login');
    exit;
}

// Load admin language
$lang_file = __DIR__ . '/../../../admin/backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

$officeController = new OfficeController($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dept_name = $_POST['dept_name'] ?? '';
    $capacity = $_POST['capacity'] ?? 0;
    $avg_service_time = $_POST['avg_service_time'] ?? 15;
    
    $result = $officeController->createDepartment($dept_name, $capacity, $avg_service_time);
    
    if ($result['success']) {
        $message = '<div class="alert alert-success alert-dismissible fade show">
                      Department created successfully
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
        $dept_name = '';
    } else {
        $message = '<div class="alert alert-danger alert-dismissible fade show">
                      ' . ($result['message'] ?? 'Failed to create department') . '
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

$pageTitle = $lang['add_department'] ?? 'Add Department';
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
                        <i class="fas fa-plus-circle"></i> <?php echo $lang['add_department'] ?? 'Add Department'; ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['department_name'] ?? 'Department Name'; ?> *</label>
                            <input type="text" name="dept_name" class="form-control" required
                                   placeholder="e.g., General Ward, ICU, OPD">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['capacity'] ?? 'Capacity'; ?> *</label>
                            <input type="number" name="capacity" class="form-control" required min="1"
                                   placeholder="e.g., 20">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['avg_service_time'] ?? 'Avg Service Time (minutes)'; ?></label>
                            <input type="number" name="avg_service_time" class="form-control" min="5" max="120"
                                   value="15" placeholder="e.g., 15">
                            <small class="text-muted"><?php echo $lang['average_time_per_patient'] ?? 'Average time per patient'; ?></small>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $lang['create_department'] ?? 'Create Department'; ?>
                            </button>
                            <a href="list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> <?php echo $lang['back'] ?? 'Back'; ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
