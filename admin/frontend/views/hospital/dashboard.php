<?php
/**
 * Hospital Admin Dashboard
 */

session_start();

// Check if admin is logged in and has hospital access
if (!isset($_SESSION['hospital_admin_id']) || !isset($_SESSION['hospital_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/hospital_login.php');
    exit;
}

$admin_id = $_SESSION['hospital_admin_id'];
$hospital_id = $_SESSION['hospital_id'];
$role = $_SESSION['admin_role'] ?? 'Staff';

// Verify hospital admin or super admin
if ($role !== 'HospitalAdmin' && $role !== 'SuperAdmin') {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/hospital_login.php');
    exit;
}

require_once __DIR__ . '/../../../backend/controllers/HospitalDashboardController.php';
require_once __DIR__ . '/../../../backend/helpers/TimeHelper.php';
$controller = new \App\Controllers\HospitalDashboardController();

// Get dashboard data
$dashboard_data = $controller->getDashboardSummary($hospital_id);
$today_tokens = $controller->getTodayTokens($hospital_id);
$assisted_bookings = $controller->getTodayAssistedBookings($hospital_id, 10);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard - SmartHealth Nepal</title>
    <link rel="stylesheet" href="/smarthealth_nepal/admin/frontend/css/admin.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
        }

        .header .info {
            text-align: right;
        }

        .header button {
            background: rgba(255,255,255,0.2);
            border: 1px solid white;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
            transition: all 0.3s ease;
        }

        .header button:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: #667eea;
        }

        .sidebar a.active {
            background: rgba(102, 126, 234, 0.1);
            border-left-color: #667eea;
            color: #667eea;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 4px solid #667eea;
        }

        .stat-card h3 {
            color: #999;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-card.urgent {
            border-top-color: #e74c3c;
        }

        .stat-card.urgent .number {
            color: #e74c3c;
        }

        .stat-card.success {
            border-top-color: #27ae60;
        }

        .stat-card.success .number {
            color: #27ae60;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #2c3e50;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: #ecf0f1;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #bdc3c7;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
        }

        .table tbody tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge.completed {
            background: #d4edda;
            color: #155724;
        }

        .badge.emergency {
            background: #f8d7da;
            color: #721c24;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn.secondary {
            background: #95a5a6;
        }

        .btn.secondary:hover {
            background: #7f8c8d;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .alert.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert.danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                display: flex;
                overflow-x: auto;
            }

            .sidebar a {
                border-left: none;
                border-bottom: 3px solid transparent;
                white-space: nowrap;
                padding: 12px 15px;
            }

            .sidebar a:hover {
                border-bottom-color: #667eea;
            }

            .sidebar a.active {
                border-bottom-color: #667eea;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>🏥 <?php echo htmlspecialchars($_SESSION['hospital_name'] ?? 'Hospital'); ?> Dashboard</h1>
        </div>
        <div class="info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
            <button onclick="goToProfile()">Profile</button>
            <button onclick="logout()">Logout</button>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <a href="#dashboard" class="active">📊 Dashboard</a>
            <a href="token_management.php">🎫 Token Management</a>
            <a href="assisted_bookings.php">📱 Assisted Bookings</a>
            <a href="departments.php">🏢 Departments</a>
            <a href="staff.php">👥 Staff Management</a>
            <a href="patients.php">🛏️ Patients</a>
            <a href="reports.php">📈 Reports</a>
            <a href="settings.php">⚙️ Settings</a>
        </div>

        <div class="main-content">
            <h2 style="margin-bottom: 20px;">Dashboard Overview</h2>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Today's Tokens</h3>
                    <div class="number"><?php echo $dashboard_data['total_tokens_today'] ?? 0; ?></div>
                </div>

                <div class="stat-card">
                    <h3>Pending Tokens</h3>
                    <div class="number urgent"><?php echo $dashboard_data['pending_tokens'] ?? 0; ?></div>
                </div>

                <div class="stat-card">
                    <h3>Called/In Progress</h3>
                    <div class="number"><?php echo $dashboard_data['called_tokens'] ?? 0; ?></div>
                </div>

                <div class="stat-card success">
                    <h3>Completed Today</h3>
                    <div class="number"><?php echo $dashboard_data['completed_tokens'] ?? 0; ?></div>
                </div>

                <div class="stat-card">
                    <h3>Assisted Bookings</h3>
                    <div class="number"><?php echo $dashboard_data['assisted_bookings_today'] ?? 0; ?></div>
                </div>
            </div>

            <!-- Today's Tokens -->
            <div class="section">
                <h2>Today's Tokens</h2>
                <?php if (!empty($today_tokens)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Token #</th>
                                <th>Patient</th>
                                <th>Department</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Wait Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($today_tokens as $token): ?>
                                <tr>
                                    <td><strong><?php echo $token['token_number']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($token['patient_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($token['department_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo $token['priority'] ?? 'Normal'; ?></td>
                                    <td>
                                        <span class="badge <?php echo str_replace(' ', '-', strtolower($token['status'] ?? 'pending')); ?>">
                                            <?php echo $token['status'] ?? 'Pending'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo TimeHelper::formatWaitTime($token['estimated_wait_time'] ?? 0); ?></td>
                                    <td>
                                        <a href="token_details.php?id=<?php echo $token['id']; ?>" class="btn" style="font-size: 12px;">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #999;">No tokens for today yet.</p>
                <?php endif; ?>
            </div>

            <!-- Recent Assisted Bookings -->
            <div class="section">
                <h2>Recent Assisted Bookings</h2>
                <a href="assisted_bookings.php" class="btn" style="margin-bottom: 15px;">+ New Assisted Booking</a>
                
                <?php if (!empty($assisted_bookings)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Patient Name</th>
                                <th>Phone</th>
                                <th>Department</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assisted_bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo htmlspecialchars($booking['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['patient_phone']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['department_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo $booking['booking_date'] . ' ' . $booking['booking_time']; ?></td>
                                    <td>
                                        <span class="badge <?php echo strtolower($booking['status']); ?>">
                                            <?php echo $booking['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn" style="font-size: 12px;">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p style="text-align: center; margin-top: 10px;">
                        <a href="assisted_bookings.php" style="color: #667eea; text-decoration: none;">View all bookings →</a>
                    </p>
                <?php else: ?>
                    <p style="color: #999;">No assisted bookings yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function goToProfile() {
            window.location.href = '/smarthealth_nepal/admin/frontend/views/hospital/profile.php';
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '/smarthealth_nepal/admin/backend/api/hospital_logout.php';
            }
        }
    </script>
</body>
</html>
