<?php
/**
 * Edit Profile Page
 * Allows users to update their personal and health information
 */

// Set language if switching
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load backend
require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/UserProfileController.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../../backend/helpers/SMSHelper.php';

// Initialize controllers
$authController = new AuthController($db);
$profileController = new UserProfileController($db);
$smsHelper = new SMSHelper();
$lang = [];

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Check login
if (!$authController->isLoggedIn()) {
    header('Location: /smarthealth_nepal/frontend/views/auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = $authController->getCurrentUser();
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateData = [];
    
    if (!empty($_POST['full_name'])) {
        $updateData['full_name'] = trim($_POST['full_name']);
    }
    
    if (!empty($_POST['age'])) {
        $updateData['age'] = (int)$_POST['age'];
    }
    
    if (!empty($_POST['gender'])) {
        $updateData['gender'] = trim($_POST['gender']);
    }
    
    if (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $updateData['email'] = trim($_POST['email']);
    }
    
    if (!empty($_POST['blood_type'])) {
        $updateData['blood_type'] = trim($_POST['blood_type']);
    }
    
    if (!empty($_POST['allergies'])) {
        $updateData['allergies'] = trim($_POST['allergies']);
    }
    
    if (!empty($_POST['emergency_contact'])) {
        $contact = trim($_POST['emergency_contact']);
        if ($smsHelper->isValidPhone($contact)) {
            $updateData['emergency_contact'] = $smsHelper->formatPhone($contact);
        }
    }
    
    if (!empty($_POST['emergency_contact_name'])) {
        $updateData['emergency_contact_name'] = trim($_POST['emergency_contact_name']);
    }
    
    if (!empty($_POST['district'])) {
        $updateData['district'] = trim($_POST['district']);
    }
    
    if (!empty($_POST['municipality'])) {
        $updateData['municipality'] = trim($_POST['municipality']);
    }
    
    if (!empty($_POST['ward'])) {
        $updateData['ward'] = trim($_POST['ward']);
    }
    
    if (!empty($updateData)) {
        $result = $profileController->updateProfile($userId, $updateData);
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            $user = $result['user'];
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    } else {
        $message = 'No changes to save';
        $messageType = 'info';
    }
}

$pageTitle = $lang['edit_profile'] ?? 'Edit Profile';
$activePage = 'profile';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><i class="fas fa-user-edit text-primary"></i> <?php echo $lang['edit_profile'] ?? 'Edit Profile'; ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></p>
        </div>
    </div>
    
    <!-- Back Button & Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php"><?php echo $lang['profile'] ?? 'Profile'; ?></a></li>
                    <li class="breadcrumb-item active"><?php echo $lang['edit_profile'] ?? 'Edit Profile'; ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Messages -->
    <?php if (!empty($message)): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Edit Form -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <form method="POST" class="card shadow-sm">
                <!-- Personal Information Section -->
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['full_name'] ?? 'Full Name'; ?></label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['phone'] ?? 'Phone'; ?></label>
                            <input type="text" class="form-control" disabled value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                            <small class="text-muted">Cannot change verified phone number</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label"><?php echo $lang['age'] ?? 'Age'; ?></label>
                            <input type="number" class="form-control" name="age" min="1" max="150" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" placeholder="e.g., 25">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?php echo $lang['gender'] ?? 'Gender'; ?></label>
                            <select class="form-select" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($user['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($user['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($user['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?php echo $lang['blood_type'] ?? 'Blood Type'; ?></label>
                            <select class="form-select" name="blood_type">
                                <option value="">Select Blood Type</option>
                                <option value="O+" <?php echo ($user['blood_type'] === 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo ($user['blood_type'] === 'O-') ? 'selected' : ''; ?>>O-</option>
                                <option value="A+" <?php echo ($user['blood_type'] === 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo ($user['blood_type'] === 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo ($user['blood_type'] === 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo ($user['blood_type'] === 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo ($user['blood_type'] === 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo ($user['blood_type'] === 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-0">
                        <div class="col-12">
                            <label class="form-label"><?php echo $lang['email'] ?? 'Email'; ?></label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="email@example.com">
                        </div>
                    </div>
                </div>
                
                <!-- Location Information -->
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Location</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label"><?php echo $lang['district'] ?? 'District'; ?></label>
                            <input type="text" class="form-control" name="district" value="<?php echo htmlspecialchars($user['district'] ?? ''); ?>" placeholder="e.g., Kathmandu">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?php echo $lang['municipality'] ?? 'Municipality'; ?></label>
                            <input type="text" class="form-control" name="municipality" value="<?php echo htmlspecialchars($user['municipality'] ?? ''); ?>" placeholder="e.g., Kathmandu Metropolitan">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?php echo $lang['ward'] ?? 'Ward'; ?></label>
                            <input type="text" class="form-control" name="ward" value="<?php echo htmlspecialchars($user['ward'] ?? ''); ?>" placeholder="e.g., 1-32">
                        </div>
                    </div>
                </div>
                
                <!-- Health Information -->
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-heart"></i> Health Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label"><?php echo $lang['allergies'] ?? 'Allergies'; ?></label>
                            <textarea class="form-control" name="allergies" rows="3" placeholder="List any known allergies or sensitivities..."><?php echo htmlspecialchars($user['allergies'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Emergency Contact -->
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-phone"></i> Emergency Contact</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['emergency_contact_name'] ?? 'Contact Name'; ?></label>
                            <input type="text" class="form-control" name="emergency_contact_name" value="<?php echo htmlspecialchars($user['emergency_contact_name'] ?? ''); ?>" placeholder="Name of person to contact">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?php echo $lang['emergency_contact'] ?? 'Contact Number'; ?></label>
                            <input type="tel" class="form-control" name="emergency_contact" value="<?php echo htmlspecialchars($user['emergency_contact'] ?? ''); ?>" placeholder="Mobile number">
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="card-footer bg-light d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="row mt-4 mb-4">
        <div class="col-12 text-center">
            <a href="index.php" class="btn btn-link">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
