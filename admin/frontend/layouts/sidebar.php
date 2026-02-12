<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-hospital"></i> SmartHealth
    </div>
    
    <ul class="sidebar-nav">
        <li>
            <a href="/smarthealth_nepal/admin/frontend/views/dashboard/" class="<?php echo ($activePage === 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> <?php echo $lang['dashboard'] ?? 'Dashboard'; ?>
            </a>
        </li>
        <li>
            <a href="/smarthealth_nepal/admin/frontend/views/token_management/" class="<?php echo ($activePage === 'token_management') ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i> <?php echo $lang['token_management'] ?? 'Token Management'; ?>
            </a>
            <ul class="list-unstyled ps-3" style="border-left: 3px solid #34495e;">
                <li><a href="/smarthealth_nepal/admin/frontend/views/token_management/active.php" style="font-size: 0.9em; padding: 8px 20px;">Active Tokens</a></li>
                <li><a href="/smarthealth_nepal/admin/frontend/views/token_management/missed.php" style="font-size: 0.9em; padding: 8px 20px;">Missed Tokens</a></li>
            </ul>
        </li>
        <li>
            <a href="/smarthealth_nepal/admin/frontend/views/user_management/" class="<?php echo ($activePage === 'user_management') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> <?php echo $lang['user_management'] ?? 'User Management'; ?>
            </a>
        </li>
        <li>
            <a href="/smarthealth_nepal/admin/frontend/views/service_management/" class="<?php echo ($activePage === 'service_management') ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> <?php echo $lang['service_management'] ?? 'Service Management'; ?>
            </a>
            <ul class="list-unstyled ps-3" style="border-left: 3px solid #34495e;">
                <li><a href="/smarthealth_nepal/admin/frontend/views/service_management/referral.php" style="font-size: 0.9em; padding: 8px 20px;">Referrals</a></li>
            </ul>
        </li>
        <li>
            <a href="/smarthealth_nepal/admin/frontend/views/office_management/" class="<?php echo ($activePage === 'office_management') ? 'active' : ''; ?>">
                <i class="fas fa-building"></i> <?php echo $lang['department_management'] ?? 'Departments'; ?>
            </a>
        </li>
        <li style="border-top: 2px solid #34495e; margin-top: 20px;">
            <a href="#" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> <?php echo $lang['logout'] ?? 'Logout'; ?>
            </a>
        </li>
    </ul>
</aside>

<div class="admin-main">
    <header class="admin-header">
        <h4 class="mb-0"><?php echo $pageTitle ?? 'SmartHealth'; ?></h4>
        <div>
            <span class="me-3">
                <i class="fas fa-user"></i> 
                <?php echo $_SESSION['admin_email'] ?? 'Admin'; ?>
            </span>
            <a href="/smarthealth_nepal/admin/frontend/views/auth/logout.php" class="btn btn-sm btn-outline-danger">
                <?php echo $lang['logout'] ?? 'Logout'; ?>
            </a>
        </div>
    </header>
    
    <div class="admin-content">
