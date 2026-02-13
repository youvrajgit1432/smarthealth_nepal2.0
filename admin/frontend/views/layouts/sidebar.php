<?php
/**
 * Admin Sidebar Layout
 */

if (!isset($_SESSION['admin_language'])) {
    $_SESSION['admin_language'] = 'en';
}

$lang_file = __DIR__ . '/../../backend/lang/' . $_SESSION['admin_language'] . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}
?>

<style>
    .sidebar-wrapper {
        position: fixed;
        left: 0;
        top: 70px;
        width: 260px;
        height: calc(100vh - 70px);
        background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
        border-right: 1px solid #e0e0e0;
        overflow-y: auto;
        z-index: 100;
        padding-top: 0;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .sidebar-wrapper::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .sidebar-wrapper::-webkit-scrollbar-thumb {
        background: #bbb;
        border-radius: 3px;
    }
    
    .sidebar-wrapper::-webkit-scrollbar-thumb:hover {
        background: #888;
    }
    
    .sidebar-header {
        padding: 1.5rem 1.25rem 1rem;
        border-bottom: 1px solid #e0e0e0;
        background: linear-gradient(135deg, #0056b3 0%, #003d99 100%);
        color: white;
    }
    
    .sidebar-header h6 {
        margin: 0;
        font-weight: 600;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    
    .sidebar-menu {
        padding: 1rem 0;
        list-style: none;
        margin: 0;
    }
    
    .sidebar-menu-item {
        position: relative;
    }
    
    .sidebar-menu-link {
        color: #333;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 3px solid transparent;
        font-size: 0.95rem;
        font-weight: 500;
        position: relative;
    }
    
    .sidebar-menu-link i {
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: rgba(0, 86, 179, 0.1);
        transition: all 0.3s ease;
    }
    
    .sidebar-menu-link:hover {
        background-color: rgba(0, 86, 179, 0.08);
        border-left-color: #0056b3;
        padding-left: 1.5rem;
    }
    
    .sidebar-menu-link:hover i {
        background: rgba(0, 86, 179, 0.25);
        color: #0056b3;
    }
    
    .sidebar-menu-link.active {
        background: linear-gradient(90deg, rgba(0, 86, 179, 0.15) 0%, transparent 100%);
        border-left-color: #0056b3;
        color: #0056b3;
        font-weight: 600;
    }
    
    .sidebar-menu-link.active i {
        background: #0056b3;
        color: white;
    }
    
    .sidebar-menu-link.active::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 30%;
        background: #0056b3;
        border-radius: 3px 0 0 3px;
    }
    
    .sidebar-footer {
        border-top: 1px solid #e0e0e0;
        padding: 1rem 1.25rem;
        margin-top: auto;
        background: rgba(0, 86, 179, 0.05);
        font-size: 0.85rem;
        color: #666;
        text-align: center;
    }
    
    .sidebar-overlay {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        display: none;
        z-index: 98;
    }
    
    .sidebar-overlay.show {
        display: block;
    }
    
    @media (max-width: 992px) {
        .sidebar-overlay.show {
            display: block;
        }
    }
    
    .main-content-wrapper {
        margin-left: 260px;
        flex: 1;
        padding: 2rem;
        width: calc(100% - 260px);
    }
    
    @media (max-width: 992px) {
        .sidebar-wrapper {
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 260px;
            z-index: 999;
        }
        
        .sidebar-wrapper.show {
            transform: translateX(0);
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
        }
        
        .main-content-wrapper {
            margin-left: 0;
            width: 100%;
            padding: 1rem;
        }
    }
</style>

 
<div class="sidebar-wrapper" id="sidebarWrapper">
   
    
    <ul class="sidebar-menu">
        <li class="sidebar-menu-item">
            <a href="/smarthealth_nepal/admin/frontend/views/dashboard/" class="sidebar-menu-link <?php echo isset($activePage) && $activePage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> 
                <span><?php echo $lang['dashboard'] ?? 'Dashboard'; ?></span>
            </a>
        </li>
        
        <li class="sidebar-menu-item">
            <a href="/smarthealth_nepal/admin/frontend/views/token_management/active.php" class="sidebar-menu-link <?php echo isset($activePage) && $activePage === 'token_management' ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i> 
                <span><?php echo $lang['token_management'] ?? 'Token Management'; ?></span>
            </a>
        </li>
        
        <li class="sidebar-menu-item">
            <a href="/smarthealth_nepal/admin/frontend/views/user_management/index.php" class="sidebar-menu-link <?php echo isset($activePage) && $activePage === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> 
                <span><?php echo $lang['user_management'] ?? 'User Management'; ?></span>
            </a>
        </li>
        
        <li class="sidebar-menu-item">
            <a href="/smarthealth_nepal/admin/frontend/views/service_management/approve.php" class="sidebar-menu-link <?php echo isset($activePage) && $activePage === 'services' ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> 
                <span><?php echo $lang['service_management'] ?? 'Service Management'; ?></span>
            </a>
        </li>
        
        <li class="sidebar-menu-item">
            <a href="/smarthealth_nepal/admin/frontend/views/office_management/list.php" class="sidebar-menu-link <?php echo isset($activePage) && $activePage === 'office' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i> 
                <span><?php echo $lang['office_management'] ?? 'Office Management'; ?></span>
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <small><?php echo date('Y'); ?> SmartHealth</small>
    </div>
</div>

<div class="main-content-wrapper">
