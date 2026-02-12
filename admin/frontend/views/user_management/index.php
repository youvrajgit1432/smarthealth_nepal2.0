<?php
/**
 * Admin - User Management
 * List all registered users with search and management options
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

$page = $_GET['page'] ?? 1;
$search = $_GET['search'] ?? '';
$limit = 20;

$result = $userController->getAllUsers($page, $limit, $search);

$pageTitle = $lang['user_management'] ?? 'User Management';
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> <?php echo $lang['user_management'] ?? 'User Management'; ?>
                    </h5>
                    <a href="add.php" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> <?php echo $lang['add_user'] ?? 'Add User'; ?>
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Search Box -->
                    <form method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="<?php echo $lang['search'] ?? 'Search'; ?>..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if ($search): ?>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <!-- Users Table -->
                    <?php if (empty($result['users'])): ?>
                    <div class="alert alert-info">
                        <?php echo $lang['no_users_found'] ?? 'No users found'; ?>
                    </div>
                    <?php else: ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th><?php echo $lang['phone'] ?? 'Phone'; ?></th>
                                    <th><?php echo $lang['email'] ?? 'Email'; ?></th>
                                    <th><?php echo $lang['name'] ?? 'Name'; ?></th>
                                    <th><?php echo $lang['language'] ?? 'Language'; ?></th>
                                    <th><?php echo $lang['registered'] ?? 'Registered'; ?></th>
                                    <th><?php echo $lang['actions'] ?? 'Actions'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $offset = ($page - 1) * $limit;
                                foreach ($result['users'] as $i => $user):
                                ?>
                                <tr>
                                    <td><?php echo $offset + $i + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['phone']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo strtoupper($user['language']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            
                                            <form method="POST" action="/smarthealth_nepal/admin/api/delete_user.php" 
                                                  style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Delete this user?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($result['pages'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                            <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $p; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $p; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
