<?php
/**
 * User Profile Dashboard Page
 * Shows user's personal information, health statistics, and quick actions
 */

// Set language if switching
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load backend
require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/UserProfileController.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';

// Initialize controllers
$authController = new AuthController($db);
$profileController = new UserProfileController($db);
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
$profileData = $profileController->getUserProfile($userId);

if (!$profileData['success']) {
    die('Error loading profile');
}

$user = $profileData['user'];
$stats = $profileController->getHealthStatistics($userId);
$avatar = $profileController->getAvatar($user);

$pageTitle = $lang['my_profile'] ?? 'My Profile';
$activePage = 'profile';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4">
    <!-- Profile Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Avatar -->
                        <div class="col-auto">
                            <div class="avatar-large" style="background-color: #<?php 
                                $colors = ['0056b3', '28a745', '17a2b8', 'ffc107', 'dc3545'];
                                echo $colors[(int)$user['id'] % count($colors)];
                            ?>; width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <span style="color: white; font-size: 2.5rem; font-weight: bold;">
                                    <?php echo $avatar['initials']; ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- User Info -->
                        <div class="col">
                            <h2 class="mb-1"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></h2>
                            <p class="text-muted mb-2">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone_number']); ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php 
                                    $location = [];
                                    if ($user['ward']) $location[] = 'Ward ' . $user['ward'];
                                    if ($user['municipality']) $location[] = $user['municipality'];
                                    if ($user['district']) $location[] = $user['district'];
                                    echo htmlspecialchars(implode(', ', $location) ?: 'Not specified');
                                ?>
                            </p>
                        </div>
                        
                        <!-- Edit Button -->
                        <div class="col-auto">
                            <a href="edit.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Health Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center shadow-sm border-0 h-100">
                <div class="card-body">
                    <i class="fas fa-history fa-3x text-primary mb-3"></i>
                    <h5 class="card-title"><?php echo $stats['total_assessments']; ?></h5>
                    <p class="card-text text-muted"><?php echo $lang['assessments'] ?? 'Health Assessments'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center shadow-sm border-0 h-100">
                <div class="card-body">
                    <i class="fas fa-notes-medical fa-3x text-success mb-3"></i>
                    <h5 class="card-title"><?php echo $stats['active_conditions']; ?></h5>
                    <p class="card-text text-muted"><?php echo $lang['active_conditions'] ?? 'Active Conditions'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center shadow-sm border-0 h-100">
                <div class="card-body">
                    <i class="fas fa-calendar-check fa-3x text-info mb-3"></i>
                    <h5 class="card-title"><?php echo $stats['total_bookings']; ?></h5>
                    <p class="card-text text-muted"><?php echo $lang['total_visits'] ?? 'Total Visits'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center shadow-sm border-0 h-100 <?php echo $stats['overdue_followups'] > 0 ? 'border-danger' : ''; ?>">
                <div class="card-body">
                    <i class="fas fa-exclamation-circle fa-3x <?php echo $stats['overdue_followups'] > 0 ? 'text-danger' : 'text-muted'; ?> mb-3"></i>
                    <h5 class="card-title"><?php echo $stats['overdue_followups']; ?></h5>
                    <p class="card-text text-muted"><?php echo $lang['overdue'] ?? 'Overdue Follow-ups'; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Links -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3"><?php echo $lang['quick_actions'] ?? 'Quick Actions'; ?></h5>
            <div class="btn-group d-flex flex-wrap gap-2" role="group">
                <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> <?php echo $lang['book_token'] ?? 'Book New Token'; ?>
                </a>
                <a href="/smarthealth_nepal/frontend/views/token/status.php" class="btn btn-info">
                    <i class="fas fa-search"></i> <?php echo $lang['track_status'] ?? 'Track Token'; ?>
                </a>
                <a href="health_history.php" class="btn btn-success">
                    <i class="fas fa-history"></i> <?php echo $lang['health_history'] ?? 'Health History'; ?>
                </a>
                <a href="diseases.php" class="btn btn-warning">
                    <i class="fas fa-notes-medical"></i> <?php echo $lang['my_conditions'] ?? 'My Conditions'; ?>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Recent Bookings -->
    <?php if (!empty($profileData['booking_history'])): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><?php echo $lang['recent_bookings'] ?? 'Recent Bookings'; ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo $lang['token'] ?? 'Token'; ?></th>
                                    <th><?php echo $lang['department'] ?? 'Department'; ?></th>
                                    <th><?php echo $lang['hospital'] ?? 'Hospital'; ?></th>
                                    <th><?php echo $lang['date'] ?? 'Date'; ?></th>
                                    <th><?php echo $lang['status'] ?? 'Status'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($profileData['booking_history'], 0, 5) as $booking): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary font-monospace" style="font-size: 0.85rem;">
                                            <?php echo htmlspecialchars($booking['token_number'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($booking['department_name'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <small>
                                            <i class="fas fa-hospital text-info"></i>
                                            <?php echo htmlspecialchars($booking['hospital_name'] ?? 'General Hospital'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($booking['status']) {
                                                'Pending' => 'warning',
                                                'Visited' => 'info',
                                                'Completed' => 'success',
                                                'Cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>" style="font-size: 0.8rem;">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="bookings.php" class="btn btn-link">
                            <?php echo $lang['view_all'] ?? 'View All Bookings'; ?> →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Available Hospitals Section -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="fas fa-hospital"></i> <?php echo $lang['available_hospitals'] ?? 'Available Hospitals'; ?></h5>
            <div class="row">
                <?php
                // Get list of available hospitals
                $hospitalResult = $db->query("SELECT DISTINCT 
                                              hl.id, 
                                              hl.hospital_name, 
                                              hl.district, 
                                              hl.municipality,
                                              hl.phone,
                                              hl.specialities
                                           FROM hospital_locations hl
                                           WHERE hl.is_active = 1
                                           ORDER BY hl.district, hl.hospital_name
                                           LIMIT 6");
                
                if ($hospitalResult && $hospitalResult->num_rows > 0):
                    while ($hospital = $hospitalResult->fetch_assoc()):
                        $specialities = is_string($hospital['specialities']) ? json_decode($hospital['specialities'], true) : ($hospital['specialities'] ?? []);
                ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card shadow-sm h-100 border-left-info" style="border-left: 4px solid #17a2b8;">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-hospital text-info"></i>
                                <?php echo htmlspecialchars($hospital['hospital_name']); ?>
                            </h6>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($hospital['district'] . ', ' . $hospital['municipality']); ?>
                                </small>
                            </div>
                            <?php if (!empty($specialities) && is_array($specialities)): ?>
                            <div class="mb-2">
                                <small class="text-muted"><strong>Services:</strong></small><br>
                                <div class="mt-1">
                                    <?php foreach (array_slice($specialities, 0, 2) as $spec): ?>
                                    <span class="badge bg-light text-dark" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($spec); ?>
                                    </span>
                                    <?php endforeach; ?>
                                    <?php if (count($specialities) > 2): ?>
                                    <span class="badge bg-light text-dark" style="font-size: 0.75rem;">
                                        +<?php echo count($specialities) - 2; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($hospital['phone'] ?? 'N/A'); ?>
                                </small>
                            </div>
                            <hr>
                            <div class="text-center">
                                <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-sm btn-info">
                                    <i class="fas fa-calendar-plus"></i> Book Here
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <?php echo $lang['no_hospitals'] ?? 'No hospitals available at the moment'; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Personal Information -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><?php echo $lang['personal_info'] ?? 'Personal Information'; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-5 text-muted"><?php echo $lang['phone'] ?? 'Phone'; ?>:</div>
                        <div class="col-7"><strong><?php echo htmlspecialchars($user['phone_number']); ?></strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted"><?php echo $lang['age'] ?? 'Age'; ?>:</div>
                        <div class="col-7"><strong><?php echo $user['age'] ?? 'Not specified'; ?></strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted"><?php echo $lang['gender'] ?? 'Gender'; ?>:</div>
                        <div class="col-7"><strong><?php echo $user['gender'] ?? 'Not specified'; ?></strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted"><?php echo $lang['blood_type'] ?? 'Blood Type'; ?>:</div>
                        <div class="col-7"><strong><?php echo $user['blood_type'] ?? 'Not specified'; ?></strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted"><?php echo $lang['email'] ?? 'Email'; ?>:</div>
                        <div class="col-7"><strong><?php echo $user['email'] ?? 'Not specified'; ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><?php echo $lang['health_info'] ?? 'Health Information'; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-5 text-muted"><?php echo $lang['allergies'] ?? 'Allergies'; ?>:</div>
                        <div class="col-7"><strong><?php echo $user['allergies'] ?? 'None known'; ?></strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted"><?php echo $lang['pregnant'] ?? 'Pregnant'; ?>:</div>
                        <div class="col-7">
                            <strong>
                                <span class="badge bg-<?php echo $user['is_pregnant'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $user['is_pregnant'] ? 'Yes' : 'No'; ?>
                                </span>
                            </strong>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted"><?php echo $lang['emergency_contact'] ?? 'Emergency Contact'; ?>:</div>
                        <div class="col-7"><strong><?php echo $user['emergency_contact'] ?? 'Not specified'; ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
 