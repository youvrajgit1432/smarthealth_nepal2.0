<?php
/**
 * Hospital Admin Layout Header
 */

// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

// Get page title from variable
$pageTitle = $pageTitle ?? 'Hospital Admin - SmartHealth Nepal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../../frontend/css/admin.css">
    <style>
        /* ===== RESET & NORMALIZATION ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            line-height: 1.6;
            font-size: 14px;
        }

        /* ===== MAIN LAYOUT ===== */
        .admin-container {
            display: flex;
            height: 100vh;
            background: #f5f5f5;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            padding: 20px 0;
            overflow-y: auto;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.12);
            position: relative;
            z-index: 100;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .sidebar-header {
            padding: 0 20px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .sidebar-header p {
            font-size: 12px;
            color: #a3b6c9;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 13px 20px;
            color: #8fa8ba;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #fff;
            border-left-color: #667eea;
            padding-left: 22px;
        }

        .sidebar-menu a.active {
            background: rgba(102, 126, 234, 0.3);
            color: #ffffff;
            border-left-color: #667eea;
            font-weight: 600;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ===== TOP BAR ===== */
        .top-bar {
            background: white;
            border-bottom: 1px solid #e0e3e8;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            z-index: 50;
        }

        .top-bar-title {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: -0.5px;
        }

        .top-bar-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #f5f7fa 0%, #eff2f7 100%);
            border-radius: 6px;
            border: 1px solid #e0e3e8;
        }

        .user-info strong {
            color: #2c3e50;
            font-weight: 600;
        }

        .user-info span {
            font-size: 12px;
            color: #8fa8ba;
            font-weight: 500;
        }

        .logout-btn {
            padding: 10px 18px;
            background: linear-gradient(135deg, #e74c3c 0%, #d63031 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(231, 76, 60, 0.2);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        .logout-btn:active {
            transform: translateY(0);
        }

        /* ===== USER ACCOUNT MENU ===== */
        .user-account-wrapper {
            position: relative;
        }

        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #f5f7fa 0%, #eff2f7 100%);
            border-radius: 6px;
            border: 1px solid #e0e3e8;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 13px;
            font-weight: 500;
            color: #2c3e50;
            font-family: inherit;
        }

        .user-profile-btn:hover {
            background: linear-gradient(135deg, #eff2f7 0%, #e0e3e8 100%);
            border-color: #667eea;
        }

        .user-profile-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        .user-profile-info {
            display: flex;
            flex-direction: column;
        }

        .user-profile-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 13px;
        }

        .user-profile-role {
            font-size: 11px;
            color: #8fa8ba;
        }

        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            min-width: 280px;
            margin-top: 10px;
            overflow: hidden;
            z-index: 1000;
            display: none;
        }

        .profile-dropdown.active {
            display: block;
            animation: slideDown 0.2s ease;
        }

        .dropdown-header {
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dropdown-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }

        .dropdown-header-info {
            flex: 1;
        }

        .dropdown-header-name {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .dropdown-header-email {
            font-size: 12px;
            opacity: 0.9;
        }

        .dropdown-divider {
            height: 1px;
            background: #e5e7eb;
        }

        .dropdown-section {
            padding: 8px 0;
        }

        .dropdown-section-title {
            padding: 8px 16px;
            font-size: 11px;
            font-weight: 700;
            color: #8fa8ba;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #2c3e50;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 13px;
            font-weight: 500;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            font-family: inherit;
        }

        .dropdown-item:hover {
            background: #f9fafb;
            color: #667eea;
            padding-left: 20px;
        }

        .dropdown-item-icon {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .dropdown-item.danger {
            color: #e74c3c;
        }

        .dropdown-item.danger:hover {
            background: #fadbd8;
            color: #c0392b;
        }

        /* ===== DASHBOARD CONTENT ===== */
        .dashboard-content {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .dashboard-content::-webkit-scrollbar {
            width: 8px;
        }

        .dashboard-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .dashboard-content::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        .dashboard-content::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.2);
        }

        /* ===== DASHBOARD GRID ===== */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
            cursor: default;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card.emergency {
            border-left-color: #e74c3c;
        }

        .stat-card.emergency:hover {
            border-left-color: #c0392b;
        }

        .stat-card.success {
            border-left-color: #27ae60;
        }

        .stat-card.success:hover {
            border-left-color: #229954;
        }

        .stat-card.warning {
            border-left-color: #f39c12;
        }

        .stat-card.warning:hover {
            border-left-color: #d68910;
        }

        .stat-card.info {
            border-left-color: #3498db;
        }

        .stat-card.info:hover {
            border-left-color: #2980b9;
        }

        .stat-card h3 {
            color: #8fa8ba;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .stat-card .subtitle {
            font-size: 12px;
            color: #8fa8ba;
        }

        /* ===== SECTIONS ===== */
        .section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 15px;
            letter-spacing: -0.3px;
        }

        .section-subtitle {
            font-size: 14px;
            color: #8fa8ba;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        /* ===== GRID LAYOUTS ===== */
        .departments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .department-card {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            padding: 16px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .department-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .department-card h4 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 14px;
        }

        .department-card p {
            font-size: 12px;
            color: #8fa8ba;
            margin: 6px 0;
        }

        .department-card p strong {
            color: #2c3e50;
            font-weight: 600;
        }

        /* ===== STATUS BADGES ===== */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.active {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .status-badge.inactive {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        .status-badge.pending {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
        }

        .status-badge.completed {
            background: linear-gradient(135deg, #d1e7dd 0%, #badbcc 100%);
            color: #0a3622;
        }

        .status-badge.running {
            background: linear-gradient(135deg, #cfe2ff 0%, #b6d4fe 100%);
            color: #084298;
        }

        .status-badge.cancelled {
            background: linear-gradient(135deg, #e2e3e5 0%, #d3d3d7 100%);
            color: #383d41;
        }

        .status-badge.assigned {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
        }

        .status-badge.visited {
            background: linear-gradient(135deg, #d1e7dd 0%, #badbcc 100%);
            color: #0a3622;
        }

        /* ===== PRIORITY BADGES ===== */
        .priority-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .priority-badge.normal {
            background: #e8f4f8;
            color: #0066cc;
        }

        .priority-badge.priority {
            background: #fef5e7;
            color: #f39c12;
        }

        .priority-badge.emergency {
            background: #fadbd8;
            color: #c0392b;
        }

        /* ===== TABLES ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
        }

        table th {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #2c3e50;
            border-bottom: 2px solid #d1d5db;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            color: #4b5563;
        }

        table tbody tr {
            transition: all 0.2s ease;
        }

        table tbody tr:hover {
            background: linear-gradient(90deg, #f9fafb 0%, #f3f4f6 100%);
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            border-left: 4px solid;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: linear-gradient(135deg, #fee 0%, #fdd 100%);
            color: #c33;
            border-left-color: #c33;
        }

        .alert-success {
            background: linear-gradient(135deg, #efe 0%, #dfd 100%);
            color: #3c3;
            border-left-color: #3c3;
        }

        .alert-warning {
            background: linear-gradient(135deg, #ffeaa7 0%, #ffde9a 100%);
            color: #856404;
            border-left-color: #f39c12;
        }

        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border-left-color: #17a2b8;
        }

        /* ===== FORMS ===== */
        .form-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            background: #ffffff;
            color: #2c3e50;
            transition: all 0.3s ease;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #9ca3af;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: #f9fafb;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* ===== BUTTONS ===== */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
            color: #2c3e50;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #d63031 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(231, 76, 60, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(39, 174, 96, 0.4);
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 12px;
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ===== HEADINGS ===== */
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        h3 {
            color: #2c3e50;
            font-size: 16px;
            font-weight: 600;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #8fa8ba;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        /* ===== LINKS ===== */
        a {
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        a:hover {
            text-decoration: underline;
            color: #764ba2;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                max-height: 60px;
                overflow-x: auto;
                overflow-y: hidden;
                padding: 0;
            }

            .sidebar-header,
            .sidebar-menu li {
                display: none;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .departments-grid {
                grid-template-columns: 1fr;
            }

            .top-bar {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .top-bar-actions {
                width: 100%;
                justify-content: space-between;
            }

            .dashboard-content {
                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .top-bar-title {
                font-size: 20px;
            }

            .stat-card .number {
                font-size: 28px;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>SmartHealth</h2>
                <p><?php echo $_SESSION['access_type'] === 'super' ? 'Super Admin' : 'Hospital Admin'; ?></p>
            </div>

            <ul class="sidebar-menu">
                <li><a href="/smarthealth_nepal/admin/hospital/dashboard/" <?php echo ($activePage === 'dashboard') ? 'class="active"' : ''; ?>>Dashboard</a></li>
                <li><a href="/smarthealth_nepal/admin/hospital/tokens/" <?php echo ($activePage === 'tokens') ? 'class="active"' : ''; ?>>Manage Tokens</a></li>
                <li><a href="/smarthealth_nepal/admin/hospital/assisted-bookings/" <?php echo ($activePage === 'assisted-bookings') ? 'class="active"' : ''; ?>>Assisted Bookings</a></li>
                <li><a href="/smarthealth_nepal/admin/hospital/departments/" <?php echo ($activePage === 'departments') ? 'class="active"' : ''; ?>>Departments</a></li>
                <li><a href="/smarthealth_nepal/admin/hospital/staff/" <?php echo ($activePage === 'staff') ? 'class="active"' : ''; ?>>Staff Management</a></li>
                <li><a href="/smarthealth_nepal/admin/hospital/settings/" <?php echo ($activePage === 'settings') ? 'class="active"' : ''; ?>>Hospital Settings</a></li>
                <li><a href="/smarthealth_nepal/admin/hospital/reports/" <?php echo ($activePage === 'reports') ? 'class="active"' : ''; ?>>Reports</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-title">
                    <?php 
                    if (!empty($pageTitle)) {
                        echo htmlspecialchars($pageTitle);
                    } else {
                        echo 'Dashboard';
                    }
                    ?>
                </div>
                <div class="top-bar-actions">
                    <div class="user-account-wrapper">
                        <button class="user-profile-btn" onclick="toggleProfileDropdown(event)">
                            <div class="user-profile-icon">
                                <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                            </div>
                            <div class="user-profile-info">
                                <div class="user-profile-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
                                <div class="user-profile-role"><?php echo ucfirst($_SESSION['access_type'] ?? 'hospital'); ?></div>
                            </div>
                        </button>

                        <div class="profile-dropdown" id="profileDropdown">
                            <!-- Header -->
                            <div class="dropdown-header">
                                <div class="dropdown-header-icon">
                                    <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                                </div>
                                <div class="dropdown-header-info">
                                    <div class="dropdown-header-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
                                    <div class="dropdown-header-email"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? $_SESSION['admin_username'] ?? 'admin@smarthealth.com'); ?></div>
                                </div>
                            </div>

                            <div class="dropdown-divider"></div>

                            <!-- Account Section -->
                            <div class="dropdown-section">
                                <div class="dropdown-section-title">Account</div>
                                <a href="/smarthealth_nepal/admin/hospital/profile/view.php" class="dropdown-item">
                                    <span class="dropdown-item-icon">👤</span>
                                    <span>View Profile</span>
                                </a>
                                <a href="/smarthealth_nepal/admin/hospital/profile/edit.php" class="dropdown-item">
                                    <span class="dropdown-item-icon">✏️</span>
                                    <span>Edit Profile</span>
                                </a>
                            </div>

                            <div class="dropdown-divider"></div>

                            <!-- Settings Section -->
                            <div class="dropdown-section">
                                <div class="dropdown-section-title">Settings</div>
                                <a href="/smarthealth_nepal/admin/hospital/profile/change-password.php" class="dropdown-item">
                                    <span class="dropdown-item-icon">🔐</span>
                                    <span>Change Password</span>
                                </a>
                                <a href="/smarthealth_nepal/admin/hospital/settings/account.php" class="dropdown-item">
                                    <span class="dropdown-item-icon">⚙️</span>
                                    <span>Account Settings</span>
                                </a>
                                <a href="/smarthealth_nepal/admin/hospital/settings/notifications.php" class="dropdown-item">
                                    <span class="dropdown-item-icon">🔔</span>
                                    <span>Notifications</span>
                                </a>
                                <a href="/smarthealth_nepal/admin/hospital/settings/security.php" class="dropdown-item">
                                    <span class="dropdown-item-icon">🛡️</span>
                                    <span>Security</span>
                                </a>
                            </div>

                            <div class="dropdown-divider"></div>

                            <!-- Support Section -->
                            <div class="dropdown-section">
                                <div class="dropdown-section-title">Support</div>
                                <a href="/smarthealth_nepal/admin/hospital/help.php" class="dropdown-item">
                                    <span class="dropdown-item-icon">❓</span>
                                    <span>Help & Support</span>
                                </a>
                                <a href="/smarthealth_nepal/admin/hospital/about.php" class="dropdown-item">
                                    <span class="dropdown-item-icon">ℹ️</span>
                                    <span>About</span>
                                </a>
                            </div>

                            <div class="dropdown-divider"></div>

                            <!-- Logout -->
                            <div class="dropdown-section">
                                <a href="/smarthealth_nepal/admin/hospital/logout.php" class="dropdown-item danger">
                                    <span class="dropdown-item-icon">🚪</span>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-content">

