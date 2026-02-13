<?php
/**
 * Admin - View Profile
 * Display admin profile information
 */

require_once __DIR__ . '/../../../backend/init.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

global $db;

// Fetch admin details
$stmt = $db->prepare("SELECT id, username, email, phone, created_at, last_login FROM admins WHERE id = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$pageTitle = 'View Profile';
$activePage = 'profile';
?>

<?php require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<style>
    .profile-header {
        background: linear-gradient(135deg, #0056b3 0%, #003d99 100%);
        color: white;
        padding: 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 2rem;
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.3);
        border: 3px solid white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
    }
    
    .profile-info h2 {
        margin: 0 0 0.5rem 0;
        font-size: 1.8rem;
        font-weight: 600;
    }
    
    .profile-info p {
        margin: 0.25rem 0;
        font-size: 0.95rem;
        opacity: 0.9;
    }
    
    .profile-section {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .profile-section h3 {
        color: #0056b3;
        margin: 0 0 1.5rem 0;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
        font-weight: 600;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        color: #333;
        font-size: 1.1rem;
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        border-left: 3px solid #0056b3;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .btn-custom {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    
    .btn-primary-custom {
        background: #0056b3;
        color: white;
    }
    
    .btn-primary-custom:hover {
        background: #003d99;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 86, 179, 0.3);
    }
    
    .btn-secondary-custom {
        background: #6c757d;
        color: white;
    }
    
    .btn-secondary-custom:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.2);
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: #d4edda;
        color: #155724;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            font-size: 2rem;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-custom {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-user-circle"></i> My Profile</h2>
</div>

<div class="profile-header">
    <div class="profile-avatar">
        <i class="fas fa-user"></i>
    </div>
    <div class="profile-info">
        <h2><?php echo htmlspecialchars($admin['username']); ?></h2>
        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($admin['email']); ?></p>
        <p class="mt-2"><span class="status-badge"><i class="fas fa-check-circle"></i> Active</span></p>
    </div>
</div>

<div class="profile-section">
    <h3><i class="fas fa-info-circle"></i> Personal Information</h3>
    
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Full Name / Username</span>
            <span class="info-value"><?php echo htmlspecialchars($admin['username']); ?></span>
        </div>
        
        <div class="info-item">
            <span class="info-label">Email Address</span>
            <span class="info-value"><?php echo htmlspecialchars($admin['email']); ?></span>
        </div>
        
        <div class="info-item">
            <span class="info-label">Phone Number</span>
            <span class="info-value"><?php echo !empty($admin['phone']) ? htmlspecialchars($admin['phone']) : 'Not provided'; ?></span>
        </div>
        
        <div class="info-item">
            <span class="info-label">Account Created</span>
            <span class="info-value"><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></span>
        </div>
        
        <div class="info-item">
            <span class="info-label">Last Login</span>
            <span class="info-value"><?php echo !empty($admin['last_login']) ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'First login'; ?></span>
        </div>
        
        <div class="info-item">
            <span class="info-label">Admin Role</span>
            <span class="info-value"><?php echo htmlspecialchars($_SESSION['admin_role']); ?></span>
        </div>
    </div>
    
    <div class="action-buttons">
        <a href="/smarthealth_nepal/admin/frontend/views/profile/edit.php" class="btn-custom btn-primary-custom">
            <i class="fas fa-edit"></i> Edit Profile
        </a>
        <a href="/smarthealth_nepal/admin/frontend/views/profile/change_password.php" class="btn-custom btn-secondary-custom">
            <i class="fas fa-key"></i> Change Password
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
