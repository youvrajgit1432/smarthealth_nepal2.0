<?php
/**
 * Admin Header Layout
 */

if (!isset($_SESSION['admin_language'])) {
    $_SESSION['admin_language'] = 'en';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['admin_language']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'SmartHealth Admin'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/smarthealth_nepal/admin/public/assets/css/admin.css">
    
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding-top: 70px;
        }
        
        .toggle-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            display: none;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            line-height: 1;
        }
        
        .toggle-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        @media (max-width: 992px) {
            .toggle-btn {
                display: flex;
            }
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #003d99 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
            height: 70px;
        }
        
        .admin-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.3rem;
            white-space: nowrap;
        }
        
        .admin-header i {
            margin-right: 0.5rem;
        }
        
        .header-center {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        
        .search-box {
            width: 100%;
            max-width: 400px;
        }
        
        .search-box input {
            border-radius: 25px;
            padding: 0.5rem 1.5rem 0.5rem 2.5rem;
            border: none;
            background: rgba(255, 255, 255, 0.95);
            color: #333;
        }
        
        .search-box input::placeholder {
            color: #999;
        }
        
        .search-box input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            color: #999;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            white-space: nowrap;
        }
        
        .profile-dropdown {
            position: relative;
        }
        
        .profile-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .profile-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .profile-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            color: #333;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            margin-top: 0.5rem;
            display: none;
            z-index: 1000;
            overflow: hidden;
        }
        
        .profile-dropdown-menu.show {
            display: block;
        }
        
        .profile-dropdown-menu a,
        .profile-dropdown-menu form {
            display: block;
            width: 100%;
        }
        
        .profile-dropdown-menu a,
        .profile-dropdown-menu button {
            padding: 0.75rem 1.5rem;
            border: none;
            background: none;
            text-align: left;
            color: #333;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.95rem;
            text-decoration: none;
        }
        
        .profile-dropdown-menu a:hover,
        .profile-dropdown-menu button:hover {
            background-color: #f0f0f0;
            color: var(--primary-color);
            padding-left: 1.75rem;
        }
        
        .profile-dropdown-menu a i,
        .profile-dropdown-menu button i {
            margin-right: 0.75rem;
            width: 18px;
            text-align: center;
        }
        
        .profile-dropdown-menu hr {
            margin: 0.5rem 0;
            border: none;
            border-top: 1px solid #eee;
        }
        
        .admin-container {
            display: flex;
            min-height: calc(100vh - 70px);
        }
        
        .sidebar {
            width: 250px;
            background-color: #f8f9fa;
            border-right: 1px solid #ddd;
            padding: 1.5rem 0;
            position: fixed;
            height: calc(100vh - 70px);
            overflow-y: auto;
            z-index: 100;
        }
        
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 2rem;
        }
        
        .sidebar nav a {
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar nav a i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .sidebar nav a:hover {
            background-color: #e9ecef;
            border-left-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .sidebar nav a.active {
            background-color: var(--primary-color);
            color: white;
            border-left-color: white;
            font-weight: 600;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            border-radius: 8px 8px 0 0;
            border: none;
            padding: 1.25rem;
            font-weight: 600;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead {
            background-color: #f8f9fa;
            border-top: 1px solid #ddd;
        }
        
        .table tbody tr {
            border-bottom: 1px solid #ddd;
        }
        
        .table tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .btn-group-sm .btn {
            padding: 0.35rem 0.6rem;
            font-size: 0.8rem;
            margin: 0 2px;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
        }
        
        footer {
            background-color: #f8f9fa;
            border-top: 1px solid #ddd;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <button class="toggle-btn" onclick="toggleSidebar()" title="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h5 class="mb-0"><i class="fas fa-hospital"></i> SmartHealth Admin</h5>
        
        <div class="header-center">
            <div class="search-box position-relative">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control" id="headerSearch" placeholder="Search patients, tokens, departments...">
            </div>
        </div>
        
        <div class="header-right">
            <form method="GET" style="display: inline;" class="me-2">
                <select name="lang" onchange="this.form.submit()" class="form-select form-select-sm" style="width: 110px; padding: 0.35rem 0.5rem;">
                    <option value="en" <?php echo $_SESSION['admin_language'] === 'en' ? 'selected' : ''; ?>>English</option>
                    <option value="ne" <?php echo $_SESSION['admin_language'] === 'ne' ? 'selected' : ''; ?>>नेपाली</option>
                </select>
            </form>
            
            <div class="profile-dropdown">
                <button class="profile-btn" onclick="toggleProfileMenu()">
                    <i class="fas fa-user-circle fa-lg"></i>
                    <span>Profile</span>
                    <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                </button>
                
                <div class="profile-dropdown-menu" id="profileMenu">
                    <a href="/smarthealth_nepal/admin/frontend/views/profile/view.php">
                        <i class="fas fa-id-card"></i> View Profile
                    </a>
                    <a href="/smarthealth_nepal/admin/frontend/views/profile/edit.php">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                    <a href="/smarthealth_nepal/admin/frontend/views/profile/change_password.php">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                    <a href="/smarthealth_nepal/admin/frontend/views/settings/account.php">
                        <i class="fas fa-cog"></i> Account Settings
                    </a>
                    <hr>
                    <a href="?logout=true" class="text-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="admin-container">
