<?php
/**
 * Admin - Service Management (landing)
 * Overview + links to approvals, forwarding and referrals.
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

// Small, real summaries (fail soft if a table is empty)
$pendingReferrals = 0;
if ($r = $db->query("SELECT COUNT(*) c FROM referrals WHERE status = 'Pending'")) {
    $pendingReferrals = (int) ($r->fetch_assoc()['c'] ?? 0);
}
$totalReferrals = 0;
if ($r = $db->query("SELECT COUNT(*) c FROM referrals")) {
    $totalReferrals = (int) ($r->fetch_assoc()['c'] ?? 0);
}
$activeServices = 0;
if ($r = $db->query("SELECT COUNT(*) c FROM services WHERE is_active = 1")) {
    $activeServices = (int) ($r->fetch_assoc()['c'] ?? 0);
}

$pageTitle = $lang['service_management'] ?? 'Service Management';
$activePage = 'service_management';
?>

<?php require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content-inner" style="padding: 0;">
    <div class="page-header" style="margin-bottom: 1.5rem;">
        <h2 style="color: #0056b3; font-weight: 600;"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p style="color: #6c757d;">Approve services, forward patients and manage referrals.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div style="background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:18px;border-left:4px solid #fd7e14;">
                <div style="font-size:11px;text-transform:uppercase;color:#6c757d;font-weight:700;">Pending Referrals</div>
                <div style="font-size:30px;font-weight:700;color:#212529;"><?php echo number_format($pendingReferrals); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div style="background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:18px;border-left:4px solid #0d6efd;">
                <div style="font-size:11px;text-transform:uppercase;color:#6c757d;font-weight:700;">Total Referrals</div>
                <div style="font-size:30px;font-weight:700;color:#212529;"><?php echo number_format($totalReferrals); ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div style="background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:18px;border-left:4px solid #198754;">
                <div style="font-size:11px;text-transform:uppercase;color:#6c757d;font-weight:700;">Active Services</div>
                <div style="font-size:30px;font-weight:700;color:#212529;"><?php echo number_format($activeServices); ?></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <?php
        $links = [
            ['approve.php', 'Approve Services', 'Review and approve submitted service requests.', '#198754'],
            ['forward.php', 'Forward Requests', 'Forward a case to another department or hospital.', '#0d6efd'],
            ['referral.php', 'Referrals', 'Track and act on patient referrals.', '#fd7e14'],
        ];
        foreach ($links as [$href, $title, $desc, $color]): ?>
            <div class="col-12 col-md-4">
                <a href="/smarthealth_nepal/admin/frontend/views/service_management/<?php echo $href; ?>"
                   style="display:block;background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:20px;text-decoration:none;height:100%;border-top:4px solid <?php echo $color; ?>;">
                    <h5 style="color:#212529;margin-bottom:8px;"><?php echo $title; ?></h5>
                    <p style="color:#6c757d;font-size:.9rem;margin:0;"><?php echo $desc; ?></p>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
