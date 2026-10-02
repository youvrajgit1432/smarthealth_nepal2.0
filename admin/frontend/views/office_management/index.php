<?php
/**
 * Admin - Department / Office Management (landing)
 * Overview + links to the list and add views.
 */

require_once __DIR__ . '/../../../backend/init.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

if (!isset($lang)) {
    $lang = [];
}

$deptCount = 0;
if ($r = $db->query("SELECT COUNT(*) c FROM departments WHERE is_active = 1")) {
    $deptCount = (int) ($r->fetch_assoc()['c'] ?? 0);
}
$hospitalCount = 0;
if ($r = $db->query("SELECT COUNT(*) c FROM hospital_locations WHERE is_active = 1")) {
    $hospitalCount = (int) ($r->fetch_assoc()['c'] ?? 0);
}

$pageTitle = $lang['department_management'] ?? 'Departments & Offices';
$activePage = 'office_management';
?>

<?php require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content-inner" style="padding: 0;">
    <div class="page-header" style="margin-bottom: 1.5rem;">
        <h2 style="color: #0056b3; font-weight: 600;"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p style="color: #6c757d;">Manage the department catalog and hospital locations.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div style="background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:18px;border-left:4px solid #0d6efd;">
                <div style="font-size:11px;text-transform:uppercase;color:#6c757d;font-weight:700;">Active Departments</div>
                <div style="font-size:30px;font-weight:700;color:#212529;"><?php echo number_format($deptCount); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div style="background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:18px;border-left:4px solid #198754;">
                <div style="font-size:11px;text-transform:uppercase;color:#6c757d;font-weight:700;">Hospitals</div>
                <div style="font-size:30px;font-weight:700;color:#212529;"><?php echo number_format($hospitalCount); ?></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <a href="/smarthealth_nepal/admin/frontend/views/office_management/list.php"
               style="display:block;background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:20px;text-decoration:none;height:100%;border-top:4px solid #0d6efd;">
                <h5 style="color:#212529;margin-bottom:8px;">View Departments</h5>
                <p style="color:#6c757d;font-size:.9rem;margin:0;">Capacity, current load and status for every department.</p>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="/smarthealth_nepal/admin/frontend/views/office_management/add.php"
               style="display:block;background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:20px;text-decoration:none;height:100%;border-top:4px solid #198754;">
                <h5 style="color:#212529;margin-bottom:8px;">Add Department</h5>
                <p style="color:#6c757d;font-size:.9rem;margin:0;">Create a new department in the catalog.</p>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
