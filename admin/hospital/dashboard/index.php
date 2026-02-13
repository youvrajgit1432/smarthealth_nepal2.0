<?php
/**
 * Hospital Admin Dashboard
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set default session variables if missing
if (!isset($_SESSION['access_type'])) {
    $_SESSION['access_type'] = 'hospital';
}
if (!isset($_SESSION['hospital_id'])) {
    $_SESSION['hospital_id'] = null;
}
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = $_SESSION['admin_username'] ?? 'Admin';
}

// Hospital admin can only see their hospital
$hospital_id = $_GET['hospital_id'] ?? $_SESSION['hospital_id'] ?? null;

// For super admin, allow hospital selection
$access_type = $_SESSION['access_type'] ?? 'hospital';
if ($access_type !== 'super' && $hospital_id != $_SESSION['hospital_id']) {
    die('Access denied');
}

$error = '';
$dashboard_data = [];
$available_hospitals = [];
$pageTitle = 'Hospital Dashboard';
$activePage = 'dashboard';

try {
    // Include main database connection
    require_once __DIR__ . '/../../backend/config/database.php';
    
    // Check if connection is available
    if (!isset($db) || $db->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Fetch available hospitals for selection
    if (!$hospital_id) {
        if ($access_type === 'super') {
            // SuperAdmin can see all hospitals
            $hospitalsQuery = "SELECT id, hospital_name FROM hospital_locations ORDER BY hospital_name";
        } else {
            // Hospital admin can only see their assigned hospital
            $hospitalsQuery = "SELECT id, hospital_name FROM hospital_locations WHERE id = " . (int)$_SESSION['hospital_id'];
        }
        $hospitalsResult = $db->query($hospitalsQuery);
        $available_hospitals = $hospitalsResult ? $hospitalsResult->fetch_all(MYSQLI_ASSOC) : [];
        
        // If there's only one hospital, auto-select it
        if (count($available_hospitals) === 1) {
            $hospital_id = $available_hospitals[0]['id'];
        }
    }

    // Fetch hospital overview data directly
    if ($hospital_id) {
        // Get hospital info
        $hospitalQuery = "SELECT * FROM hospital_locations WHERE id = " . (int)$hospital_id;
        $hospitalResult = $db->query($hospitalQuery);
        $hospital = $hospitalResult ? $hospitalResult->fetch_assoc() : null;
        $pageTitle = !empty($hospital) ? htmlspecialchars($hospital['hospital_name']) : 'Dashboard';

        // Get today's token counts by status
        $tokensQuery = "SELECT 
                        COUNT(*) as total_tokens,
                        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status IN ('Active', 'Called') THEN 1 ELSE 0 END) as running,
                        SUM(CASE WHEN status IN ('Active', 'Rescheduled') THEN 1 ELSE 0 END) as pending
                    FROM tokens 
                    WHERE hospital_id = " . (int)$hospital_id . " 
                    AND DATE(created_at) = CURDATE()";
        $tokensResult = $db->query($tokensQuery);
        $tokens_data = $tokensResult ? $tokensResult->fetch_assoc() : ['total_tokens' => 0, 'completed' => 0, 'running' => 0, 'pending' => 0];

        // Get department status - using hospital_departments junction table
        $departmentsQuery = "SELECT d.*, 
                           hd.hospital_id,
                           hd.max_tokens_per_day,
                           hd.available as status,
                           hd.is_active,
                           COUNT(t.id) as current_daily_tokens
                        FROM hospital_departments hd
                        INNER JOIN departments d ON hd.department_id = d.id
                        LEFT JOIN tokens t ON d.id = t.department_id 
                        AND t.hospital_id = hd.hospital_id
                        AND DATE(t.created_at) = CURDATE()
                        WHERE hd.hospital_id = " . (int)$hospital_id . "
                        GROUP BY d.id, hd.id
                        LIMIT 10";
        $departmentsResult = $db->query($departmentsQuery);
        $departments = $departmentsResult ? $departmentsResult->fetch_all(MYSQLI_ASSOC) : [];

        // Get staff count
        $staffQuery = "SELECT COUNT(*) as count FROM hospital_staff WHERE hospital_id = " . (int)$hospital_id;
        $staffResult = $db->query($staffQuery);
        $staff_data = $staffResult ? $staffResult->fetch_assoc() : ['count' => 0];

        // Get assisted bookings count
        $bookingsQuery = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as assigned
                    FROM assisted_bookings 
                    WHERE hospital_id = " . (int)$hospital_id . " 
                    AND DATE(booking_date) = CURDATE()";
        $bookingsResult = $db->query($bookingsQuery);
        $bookings_data = $bookingsResult ? $bookingsResult->fetch_assoc() : ['total' => 0, 'pending' => 0, 'assigned' => 0];

        $dashboard_data = [
            'success' => true,
            'hospital' => $hospital,
            'tokens_today' => $tokens_data,
            'departments' => $departments,
            'staff_count' => $staff_data['count'],
            'assisted_bookings' => $bookings_data
        ];
    } else {
        $dashboard_data = ['success' => false];
    }
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    $dashboard_data = ['success' => false];
}

// Include header
require_once __DIR__ . '/../layouts/header.php';
?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!empty($dashboard_data['success'])): ?>
                    <!-- Statistics Cards -->
                    <div class="dashboard-grid">
                        <div class="stat-card">
                            <h3>Today's Tokens</h3>
                            <div class="number"><?php echo $dashboard_data['tokens_today']['total_tokens'] ?? 0; ?></div>
                        </div>
                        <div class="stat-card success">
                            <h3>Completed</h3>
                            <div class="number"><?php echo $dashboard_data['tokens_today']['completed'] ?? 0; ?></div>
                        </div>
                        <div class="stat-card warning">
                            <h3>Pending</h3>
                            <div class="number"><?php echo $dashboard_data['tokens_today']['pending'] ?? 0; ?></div>
                        </div>
                        <div class="stat-card emergency">
                            <h3>Running</h3>
                            <div class="number"><?php echo $dashboard_data['tokens_today']['running'] ?? 0; ?></div>
                        </div>
                    </div>

                    <!-- Hospital Info -->
                    <div class="section">
                        <div class="section-title">Hospital Information</div>
                        <table>
                            <tr>
                                <td><strong>Hospital Name:</strong></td>
                                <td><?php echo htmlspecialchars($dashboard_data['hospital']['hospital_name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td><?php echo htmlspecialchars($dashboard_data['hospital']['address']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td><?php echo htmlspecialchars($dashboard_data['hospital']['phone']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td><?php echo htmlspecialchars($dashboard_data['hospital']['type']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Emergency 24/7:</strong></td>
                                <td><?php echo $dashboard_data['hospital']['emergency_24_7'] ? 'Yes' : 'No'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Staff Count:</strong></td>
                                <td><?php echo $dashboard_data['staff_count']; ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Departments -->
                    <div class="section">
                        <div class="section-title">Active Departments</div>
                        <div class="departments-grid">
                            <?php foreach ($dashboard_data['departments'] as $dept): ?>
                                <div class="department-card">
                                    <h4><?php echo htmlspecialchars($dept['name_en']); ?></h4>
                                    <p><strong>Tokens Today:</strong> <?php echo $dept['current_daily_tokens']; ?>/<?php echo $dept['max_tokens_per_day']; ?></p>
                                    <p><strong>Avg Wait:</strong> <?php echo $dept['avg_service_time']; ?> mins</p>
                                    <span class="status-badge <?php echo $dept['available'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $dept['available'] ? 'Available' : 'Unavailable'; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Assisted Bookings -->
                    <div class="section">
                        <div class="section-title">Today's Assisted Bookings</div>
                        <p style="margin-bottom: 15px;">
                            <strong>Total:</strong> <?php echo $dashboard_data['assisted_bookings']['total'] ?? 0; ?> |
                            <strong>Pending:</strong> <?php echo $dashboard_data['assisted_bookings']['pending'] ?? 0; ?> |
                            <strong>Assigned:</strong> <?php echo $dashboard_data['assisted_bookings']['assigned'] ?? 0; ?>
                        </p>
                        <a href="/smarthealth_nepal/admin/hospital/assisted-bookings/" style="color: #667eea; text-decoration: none;">View All Assisted Bookings →</a>
                    </div>

                <?php elseif (!empty($available_hospitals)): ?>
                    <!-- Hospital Selection -->
                    <div class="section">
                        <div class="section-title">Select a Hospital</div>
                        <div style="padding: 20px;">
                            <?php if (count($available_hospitals) > 0): ?>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
                                    <?php foreach ($available_hospitals as $h): ?>
                                        <a href="/smarthealth_nepal/admin/hospital/dashboard/?hospital_id=<?php echo (int)$h['id']; ?>" style="text-decoration: none;">
                                            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; transition: all 0.3s; background: #f9f9f9;">
                                                <h3 style="margin: 0 0 10px 0; color: #333;"><?php echo htmlspecialchars($h['hospital_name']); ?></h3>
                                                <p style="margin: 0; color: #667eea; font-weight: 500;">Click to view dashboard →</p>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p>No hospitals available.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="alert alert-error">
                        Unable to load dashboard data. Please select a hospital.
                    </div>
                <?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
