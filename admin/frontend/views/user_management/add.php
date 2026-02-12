<?php
/**
 * Admin - Add User
 * Form to register new users in the system
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../admin/backend/controllers/UserController.php';

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

$userController = new UserController($db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $language = $_POST['language'] ?? 'en';
    
    $result = $userController->createUser($phone, $name, $email, $language);
    
    if ($result['success']) {
        $message = '<div class="alert alert-success alert-dismissible fade show">
                      ' . $result['message'] . '
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
        // Clear form
        $phone = $name = $email = '';
    } else {
        $message = '<div class="alert alert-danger alert-dismissible fade show">
                      ' . $result['message'] . '
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

$pageTitle = $lang['add_user'] ?? 'Add User';
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
                        <i class="fas fa-user-plus"></i> <?php echo $lang['add_user'] ?? 'Add User'; ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['phone'] ?? 'Phone'; ?> *</label>
                            <input type="text" name="phone" class="form-control" 
                                   placeholder="9841234567" required
                                   pattern="^\d{10}$" title="10 digit phone number">
                            <small class="text-muted"><?php echo $lang['enter_10_digits'] ?? 'Enter 10 digit phone number'; ?></small>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['name'] ?? 'Name'; ?> *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['email'] ?? 'Email'; ?></label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['language'] ?? 'Language'; ?></label>
                            <select name="language" class="form-select">
                                <option value="en"><?php echo $lang['english'] ?? 'English'; ?></option>
                                <option value="ne"><?php echo $lang['nepali'] ?? 'Nepali'; ?></option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $lang['save'] ?? 'Save'; ?>
                            </button>
                            <a href="index.php" class="btn btn-secondary">
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
