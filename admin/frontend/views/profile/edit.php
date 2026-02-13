<?php
/**
 * Admin - Edit Profile
 * Edit admin profile information
 */

require_once __DIR__ . '/../../../backend/init.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

global $db;
$message = '';
$error = '';

// Fetch current admin details
$stmt = $db->prepare("SELECT id, username, email, phone FROM admins WHERE id = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($email !== $admin['email']) {
        // Check if email already exists
        $check = $db->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $_SESSION['admin_id']);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Email already in use';
        }
    }
    
    if (empty($error)) {
        // Update admin profile
        $stmt = $db->prepare("UPDATE admins SET email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssi", $email, $phone, $_SESSION['admin_id']);
        
        if ($stmt->execute()) {
            $message = 'Profile updated successfully!';
            // Refresh admin data
            $admin['email'] = $email;
            $admin['phone'] = $phone;
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

$pageTitle = 'Edit Profile';
$activePage = 'profile';
?>

<?php require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<style>
    .form-section {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        max-width: 600px;
        margin: 0 auto;
    }
    
    .form-section h3 {
        color: #0056b3;
        margin: 0 0 2rem 0;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
        font-weight: 600;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }
    
    .form-group input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #0056b3;
        box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
    }
    
    .form-group input:disabled {
        background-color: #f8f9fa;
        color: #666;
    }
    
    .form-help {
        font-size: 0.85rem;
        color: #666;
        margin-top: 0.3rem;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .btn-submit {
        padding: 0.75rem 2rem;
        background: #0056b3;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-submit:hover {
        background: #003d99;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 86, 179, 0.3);
    }
    
    .btn-cancel {
        padding: 0.75rem 2rem;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-cancel:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .form-section {
            padding: 1.5rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn-submit, .btn-cancel {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-edit"></i> Edit Profile</h2>
</div>

<div class="form-section">
    <?php if ($message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <h3><i class="fas fa-user-edit"></i> Edit Your Profile Information</h3>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                value="<?php echo htmlspecialchars($admin['username']); ?>" 
                disabled
            >
            <div class="form-help">Username cannot be changed</div>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address <span style="color: #dc3545;">*</span></label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?php echo htmlspecialchars($admin['email']); ?>" 
                required
            >
            <div class="form-help">We'll use this to send important notifications</div>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input 
                type="tel" 
                id="phone" 
                name="phone" 
                value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>" 
                placeholder="+977 9xxxxxxxxx"
            >
            <div class="form-help">Optional: Your contact phone number</div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="/smarthealth_nepal/admin/frontend/views/profile/view.php" class="btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
