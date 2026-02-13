<?php
/**
 * Admin - User Management
 * List all registered users with search and management options
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

$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Determine if user is superadmin and set hospital filter
$is_superadmin = $_SESSION['admin_role'] === 'superadmin';
$hospital_id = $_SESSION['hospital_id'] ?? null;

// Get users
$sql = "SELECT * FROM users";
$where = [];

// If not superadmin, filter users by hospital
if (!$is_superadmin && $hospital_id) {
    $where[] = "id IN (SELECT DISTINCT u.id FROM users u 
                 JOIN tokens t ON u.id = t.user_id 
                 WHERE t.hospital_id = " . (int)$hospital_id . ")";
}

if (!empty($search)) {
    $search_term = $db->real_escape_string($search);
    $where[] = "(full_name LIKE '%$search_term%' OR phone_number LIKE '%$search_term%' OR email LIKE '%$search_term%')";
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

$result = $db->query($sql);
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users";
if (!empty($where)) {
    $count_sql .= " WHERE " . implode(" AND ", $where);
}
$count_result = $db->query($count_sql);
$total = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$pageTitle = $lang['user_management'] ?? 'User Management';
$activePage = 'user_management';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
 
// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users";
if (!empty($where)) {
    $count_sql .= " WHERE " . implode(" AND ", $where);
}
$count_result = $db->query($count_sql);
$total = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$pageTitle = $lang['user_management'] ?? 'User Management';
$activePage = 'user_management';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4"><?php echo $lang['user_management'] ?? 'User Management'; ?></h2>
    
    <!-- Search Box -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="mb-0">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="<?php echo $lang['search'] ?? 'Search by name, phone, or email'; ?>..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if ($search): ?>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-users"></i> Registered Users (<?php echo $total; ?> total)
            </h5>
        </div>
        
        <div class="card-body">
            <?php if (empty($users)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <?php echo $lang['no_users_found'] ?? 'No users found'; ?>
            </div>
            <?php else: ?>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th><?php echo $lang['name'] ?? 'Name'; ?></th>
                            <th><?php echo $lang['phone'] ?? 'Phone'; ?></th>
                            <th><?php echo $lang['email'] ?? 'Email'; ?></th>
                            <th><?php echo $lang['mpin'] ?? 'MPIN'; ?></th>
                            <th><?php echo $lang['registered'] ?? 'Registered'; ?></th>
                            <th><?php echo $lang['actions'] ?? 'Actions'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($users as $i => $user):
                        ?>
                        <tr>
                            <td><?php echo $offset + $i + 1; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></strong>
                            </td>
                            <td>
                                <code><?php echo htmlspecialchars($user['phone_number']); ?></code>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['email'] ?? '-'); ?>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    <?php echo htmlspecialchars($user['mpin'] ?? 'Not Set'); ?>
                                </span>
                            </td>
                            <td>
                                <small><?php echo date('d M Y', strtotime($user['created_at'])); ?></small>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php 
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($page > 1):
                    ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($p = $start; $p <= $end; $p++): ?>
                    <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $p; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            <?php echo $p; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php 
                    if ($page < $total_pages):
                    ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Last</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
