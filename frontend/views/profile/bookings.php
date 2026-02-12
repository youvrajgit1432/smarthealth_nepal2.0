<?php
/**
 * My Bookings Page
 * Shows user's booking history and status
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
$user = $authController->getCurrentUser();
$bookingHistory = $profileController->getBookingHistory($userId, 50);

$pageTitle = $lang['my_bookings'] ?? 'My Bookings';
$activePage = 'profile';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><i class="fas fa-clipboard-list text-info"></i> <?php echo $lang['my_bookings'] ?? 'My Bookings'; ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></p>
        </div>
    </div>
    
    <!-- Back Button & Navigation -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php"><?php echo $lang['profile'] ?? 'Profile'; ?></a></li>
                    <li class="breadcrumb-item active"><?php echo $lang['my_bookings'] ?? 'My Bookings'; ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <?php if (empty($bookingHistory)): ?>
    <!-- Empty State -->
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm text-center">
                <div class="card-body py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5><?php echo $lang['no_bookings'] ?? 'No Bookings'; ?></h5>
                    <p class="text-muted mb-3"><?php echo $lang['start_booking'] ?? 'You haven\'t booked any tokens yet'; ?></p>
                    <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Book Your First Token
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    
    <!-- Bookings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th><?php echo $lang['token'] ?? 'Token'; ?></th>
                                    <th><?php echo $lang['department'] ?? 'Department'; ?></th>
                                    <th><?php echo $lang['hospital'] ?? 'Hospital'; ?></th>
                                    <th><?php echo $lang['booking_date'] ?? 'Booking Date'; ?></th>
                                    <th><?php echo $lang['status'] ?? 'Status'; ?></th>
                                    <th><?php echo $lang['action'] ?? 'Action'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookingHistory as $booking): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary font-monospace">
                                            <?php echo htmlspecialchars($booking['token_number'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-hospital-user text-secondary"></i>
                                        <?php echo htmlspecialchars($booking['department_name'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($booking['hospital_name'] ?? 'General Hospital'); ?></small>
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
                                        ?>">
                                            <?php 
                                                $statusLabels = [
                                                    'Pending' => $lang['pending'] ?? 'Pending',
                                                    'Visited' => $lang['visited'] ?? 'Visited',
                                                    'Completed' => $lang['completed'] ?? 'Completed',
                                                    'Cancelled' => $lang['cancelled'] ?? 'Cancelled'
                                                ];
                                                echo $statusLabels[$booking['status']] ?? ucfirst($booking['status']);
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/smarthealth_nepal/frontend/views/token/status.php?token=<?php echo htmlspecialchars($booking['token_number']); ?>" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
    
    <!-- Actions -->
    <div class="row mt-4 mb-4">
        <div class="col-12 text-center">
            <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Book New Token
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
