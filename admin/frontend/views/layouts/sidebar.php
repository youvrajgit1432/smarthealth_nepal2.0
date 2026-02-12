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

<sidebar class="sidebar">
    <nav>
        <a href="/smarthealth_nepal/admin/frontend/views/dashboard/" class="<?php echo isset($activePage) && $activePage === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> <?php echo $lang['dashboard'] ?? 'Dashboard'; ?>
        </a>
        
        <a href="/smarthealth_nepal/admin/frontend/views/token_management/active.php" class="<?php echo isset($activePage) && $activePage === 'tokens' ? 'active' : ''; ?>">
            <i class="fas fa-ticket-alt"></i> <?php echo $lang['token_management'] ?? 'Token Management'; ?>
        </a>
        
        <a href="/smarthealth_nepal/admin/frontend/views/user_management/index.php" class="<?php echo isset($activePage) && $activePage === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> <?php echo $lang['user_management'] ?? 'User Management'; ?>
        </a>
        
        <a href="/smarthealth_nepal/admin/frontend/views/service_management/approve.php" class="<?php echo isset($activePage) && $activePage === 'services' ? 'active' : ''; ?>">
            <i class="fas fa-tasks"></i> <?php echo $lang['service_management'] ?? 'Service Management'; ?>
        </a>
        
        <a href="/smarthealth_nepal/admin/frontend/views/office_management/list.php" class="<?php echo isset($activePage) && $activePage === 'office' ? 'active' : ''; ?>">
            <i class="fas fa-building"></i> <?php echo $lang['office_management'] ?? 'Office Management'; ?>
        </a>
    </nav>
</sidebar>

<main class="main-content">
