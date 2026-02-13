<?php
/**
 * Admin - View User Details
 * Show complete user information and login credentials
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

$user_id = intval($_GET['id'] ?? 0);
if (!$user_id) {
    header('Location: index.php');
    exit;
}

// Get user details
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $db->query($sql);
$user = $result ? $result->fetch_assoc() : null;

if (!$user) {
    header('Location: index.php');
    exit;
}

// Get user's booking history
$bookings_result = $db->query("SELECT * FROM booking_history WHERE user_id = $user_id ORDER BY booking_date DESC LIMIT 5");
$bookings = [];
if ($bookings_result) {
    while ($row = $bookings_result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

$pageTitle = 'View User: ' . htmlspecialchars($user['full_name'] ?? 'Unknown');
$activePage = 'user_management';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="mb-4">
        <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?>
        <a href="index.php" class="btn btn-sm btn-secondary float-end">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </h2>
    
    <div class="row">
        <!-- User Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div style="width: 100px; height: 100px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 48px; color: white;">
                                <?php echo strtoupper(substr($user['full_name'][0], 0, 1)); ?>
                            </span>
                        </div>
                    </div>
                    
                    <h5><?php echo htmlspecialchars($user['full_name'] ?? 'Unknown'); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($user['phone_number']); ?></p>
                    
                    <div class="mt-3">
                        <span class="badge bg-success me-2">
                            <?php echo $user['phone_verified'] ? 'Phone Verified' : 'Not Verified'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Credentials -->
        <div class="col-md-8">
            <!-- Login Credentials -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key"></i> Login Credentials
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> Share these credentials with the user securely. The MPIN is used for OTP verification.
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Phone Number (Login ID)</strong></label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('<?php echo htmlspecialchars($user['phone_number']); ?>')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>MPIN (Password)</strong></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="mpinInput" value="<?php echo htmlspecialchars($user['mpin']); ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleMPIN()">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('<?php echo htmlspecialchars($user['mpin']); ?>')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Personal Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Full Name</strong></label>
                            <p><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Email</strong></label>
                            <p><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Age</strong></label>
                            <p><?php echo htmlspecialchars($user['age'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Gender</strong></label>
                            <p><?php echo htmlspecialchars($user['gender'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Blood Type</strong></label>
                            <p>
                                <?php if($user['blood_type']): ?>
                                    <span class="badge bg-danger"><?php echo htmlspecialchars($user['blood_type']); ?></span>
                                <?php else: ?>
                                    Not Set
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Pregnant</strong></label>
                            <p><?php echo $user['is_pregnant'] ? 'Yes' : 'No'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Location Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-map-marker-alt"></i> Location Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>District</strong></label>
                            <p><?php echo htmlspecialchars($user['district'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Municipality</strong></label>
                            <p><?php echo htmlspecialchars($user['municipality'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Ward</strong></label>
                            <p><?php echo htmlspecialchars($user['ward'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Emergency Contact</strong></label>
                            <p><?php echo htmlspecialchars($user['emergency_contact'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Statistics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Account Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Total Bookings</strong></label>
                            <h3 class="text-primary"><?php echo $user['total_bookings']; ?></h3>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Registered Since</strong></label>
                            <p><?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label"><strong>Last Booking</strong></label>
                            <p>
                                <?php 
                                if ($user['last_booking_date']) {
                                    echo date('F d, Y H:i A', strtotime($user['last_booking_date']));
                                } else {
                                    echo 'No bookings yet';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <?php if (!empty($bookings)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i> Recent Bookings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Department</th>
                                    <th>Hospital</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($booking['department_id']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['hospital_id']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $booking['status'] === 'Completed' ? 'success' : 
                                                ($booking['status'] === 'Pending' ? 'warning' : 
                                                ($booking['status'] === 'Visited' ? 'info' : 'danger'));
                                        ?>">
                                            <?php echo htmlspecialchars($booking['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleMPIN() {
    const input = document.getElementById('mpinInput');
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    });
}
</script>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
