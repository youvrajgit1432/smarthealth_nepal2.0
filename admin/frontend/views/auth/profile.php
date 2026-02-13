<?php
/**
 * Admin Profile Page
 * View and edit admin profile information
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

// Get admin information
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT a.*, d.name_en as department_name FROM admins a 
        LEFT JOIN departments d ON a.department_id = d.id 
        WHERE a.id = $admin_id";

$result = $db->query($sql);
$admin = $result ? $result->fetch_assoc() : null;

if (!$admin) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

// Get hospital info if hospital_id exists
$hospital = null;
if (!empty($admin['hospital_id'])) {
    $hospital_result = $db->query("SELECT * FROM hospital_locations WHERE id = " . intval($admin['hospital_id']));
    $hospital = $hospital_result ? $hospital_result->fetch_assoc() : null;
}

$pageTitle = $lang['profile'] ?? 'My Profile';
$activePage = 'profile';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">
        <i class="fas fa-user"></i> <?php echo $lang['my_profile'] ?? 'My Profile'; ?>
    </h2>
    
    <div class="row">
        <!-- Profile Information -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="profile-avatar" style="width: 100px; height: 100px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 48px; color: white;">
                                <?php echo strtoupper(substr($admin['full_name'][0], 0, 1)); ?>
                            </span>
                        </div>
                    </div>
                    
                    <h5><?php echo htmlspecialchars($admin['full_name']); ?></h5>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($admin['role']); ?></p>
                    
                    <div class="mb-3">
                        <span class="badge bg-<?php echo $admin['is_active'] ? 'success' : 'danger'; ?>">
                            <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <div class="text-start">
                        <p class="mb-2">
                            <strong>Username:</strong><br>
                            <code><?php echo htmlspecialchars($admin['username']); ?></code>
                        </p>
                        <p class="mb-2">
                            <strong>Email:</strong><br>
                            <a href="mailto:<?php echo htmlspecialchars($admin['email']); ?>">
                                <?php echo htmlspecialchars($admin['email']); ?>
                            </a>
                        </p>
                        <p class="mb-0">
                            <strong>Member Since:</strong><br>
                            <?php echo date('F d, Y', strtotime($admin['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detailed Information -->
        <div class="col-md-8">
            <!-- Account Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Full Name</strong></label>
                            <p><?php echo htmlspecialchars($admin['full_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Username</strong></label>
                            <p><?php echo htmlspecialchars($admin['username']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Email</strong></label>
                            <p><?php echo htmlspecialchars($admin['email']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Role</strong></label>
                            <p>
                                <span class="badge bg-info">
                                    <?php echo htmlspecialchars($admin['role']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Status</strong></label>
                            <p>
                                <span class="badge bg-<?php echo $admin['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Last Login</strong></label>
                            <p>
                                <?php 
                                if ($admin['last_login']) {
                                    echo date('F d, Y H:i A', strtotime($admin['last_login']));
                                } else {
                                    echo 'Never';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Role & Permissions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt"></i> Role & Permissions
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($admin['role'] === 'SuperAdmin'): ?>
                        <p class="mb-2"><i class="fas fa-star text-warning"></i> <strong>Super Admin Access</strong></p>
                        <ul class="list-unstyled ms-3">
                            <li><i class="fas fa-check text-success"></i> Manage all hospitals</li>
                            <li><i class="fas fa-check text-success"></i> Manage all departments</li>
                            <li><i class="fas fa-check text-success"></i> Manage all users</li>
                            <li><i class="fas fa-check text-success"></i> Manage all tokens</li>
                            <li><i class="fas fa-check text-success"></i> System settings</li>
                            <li><i class="fas fa-check text-success"></i> View all reports</li>
                        </ul>
                    <?php elseif ($admin['role'] === 'Admin'): ?>
                        <p class="mb-2"><i class="fas fa-hospital text-info"></i> <strong>Hospital Admin Access</strong></p>
                        <p class="text-muted mb-3">Department: <?php echo $admin['department_name'] ?? 'N/A'; ?></p>
                        <ul class="list-unstyled ms-3">
                            <li><i class="fas fa-check text-success"></i> Manage hospital departments</li>
                            <li><i class="fas fa-check text-success"></i> Manage hospital staff</li>
                            <li><i class="fas fa-check text-success"></i> Manage department tokens</li>
                            <li><i class="fas fa-check text-success"></i> View department reports</li>
                        </ul>
                    <?php else: ?>
                        <p class="mb-2"><i class="fas fa-user-tie text-secondary"></i> <strong><?php echo htmlspecialchars($admin['role']); ?> Access</strong></p>
                        <p class="text-muted">Limited staff access to manage specific functions.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Hospital Assignment (if applicable) -->
            <?php if ($hospital): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-hospital"></i> Assigned Hospital
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Hospital Name</strong></label>
                            <p><?php echo htmlspecialchars($hospital['hospital_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Type</strong></label>
                            <p><span class="badge bg-secondary"><?php echo htmlspecialchars($hospital['type']); ?></span></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label"><strong>Location</strong></label>
                            <p>
                                <?php echo htmlspecialchars($hospital['address']); ?><br>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($hospital['ward']); ?>, 
                                    <?php echo htmlspecialchars($hospital['municipality']); ?>, 
                                    <?php echo htmlspecialchars($hospital['district']); ?>
                                </small>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Contact</strong></label>
                            <p>
                                <a href="tel:<?php echo htmlspecialchars($hospital['phone']); ?>">
                                    <?php echo htmlspecialchars($hospital['phone']); ?>
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Status</strong></label>
                            <p>
                                <span class="badge bg-<?php echo $hospital['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $hospital['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="mt-4">
                <a href="change_password.php" class="btn btn-warning">
                    <i class="fas fa-key"></i> Change Password
                </a>
                <a href="/smarthealth_nepal/admin/frontend/views/dashboard/" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
