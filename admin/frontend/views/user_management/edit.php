<?php
/**
 * Admin - Edit User
 * Form to edit user information
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/UserController.php';

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

$userController = new UserController($db);
$user_id = $_GET['id'] ?? null;
$message = '';

if (!$user_id) {
    header('Location: index.php');
    exit;
}

$user_result = $userController->getUserById($user_id);
if (!$user_result['success']) {
    $message = '<div class="alert alert-danger">User not found</div>';
    $user = null;
} else {
    $user = $user_result['user'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $language = $_POST['language'] ?? 'en';
    
    $result = $userController->updateUser($user_id, [
        'name' => $name,
        'email' => $email,
        'language' => $language
    ]);
    
    if ($result['success']) {
        $message = '<div class="alert alert-success alert-dismissible fade show">
                      User updated successfully
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
        // Refresh user data
        $user_result = $userController->getUserById($user_id);
        $user = $user_result['user'];
    } else {
        $message = '<div class="alert alert-danger alert-dismissible fade show">
                      Failed to update user
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

$pageTitle = $lang['edit_user'] ?? 'Edit User';
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
                        <i class="fas fa-edit"></i> <?php echo $lang['edit_user'] ?? 'Edit User'; ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <?php echo $message; ?>
                    
                    <?php if ($user): ?>
                    
                    <!-- User Info Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><?php echo $lang['user_information'] ?? 'User Information'; ?></h6>
                            <dl class="row">
                                <dt class="col-sm-4"><?php echo $lang['phone'] ?? 'Phone'; ?></dt>
                                <dd class="col-sm-8"><strong><?php echo htmlspecialchars($user['phone']); ?></strong></dd>
                                
                                <dt class="col-sm-4"><?php echo $lang['registered'] ?? 'Registered'; ?></dt>
                                <dd class="col-sm-8"><?php echo date('d M Y H:i', strtotime($user['created_at'])); ?></dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6><?php echo $lang['tokens'] ?? 'Tokens'; ?></h6>
                            <?php 
                            $tokens = $userController->getUserTokens($user_id);
                            ?>
                            <p class="mb-0">
                                <strong><?php echo count($tokens); ?></strong> 
                                <?php echo $lang['total_tokens'] ?? 'total tokens'; ?>
                            </p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Edit Form -->
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['name'] ?? 'Name'; ?> *</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['email'] ?? 'Email'; ?></label>
                            <input type="email" name="email" class="form-control"
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['language'] ?? 'Language'; ?></label>
                            <select name="language" class="form-select">
                                <option value="en" <?php echo $user['language'] === 'en' ? 'selected' : ''; ?>>
                                    <?php echo $lang['english'] ?? 'English'; ?>
                                </option>
                                <option value="ne" <?php echo $user['language'] === 'ne' ? 'selected' : ''; ?>>
                                    <?php echo $lang['nepali'] ?? 'Nepali'; ?>
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $lang['save_changes'] ?? 'Save Changes'; ?>
                            </button>
                            <a href="index.php" class="btn btn-secondary">
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
