<?php
/**
 * Admin - Token Management (landing)
 * Overview + links to active / missed / reschedule views.
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

$is_superadmin = isset($_SESSION['admin_role']) && strtolower($_SESSION['admin_role']) === 'superadmin';
$hospital_id = $_SESSION['hospital_id'] ?? null;

// Today's token counts (scoped for hospital admins)
$counts = ['Active' => 0, 'Called' => 0, 'Completed' => 0, 'Missed' => 0];
$where = "DATE(created_at) = CURDATE()";
if (!$is_superadmin && $hospital_id) {
    $where .= " AND hospital_id = " . (int) $hospital_id;
}
$sql = "SELECT status, COUNT(*) c FROM tokens WHERE $where GROUP BY status";
if ($result = $db->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        if (array_key_exists($row['status'], $counts)) {
            $counts[$row['status']] = (int) $row['c'];
        }
    }
}

$pageTitle = $lang['token_management'] ?? 'Token Management';
$activePage = 'token_management';
?>

<?php require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="admin-content-inner" style="padding: 0;">
    <div class="page-header" style="margin-bottom: 1.5rem;">
        <h2 style="color: #0056b3; font-weight: 600;"><?php echo htmlspecialchars($pageTitle); ?></h2>
        <p style="color: #6c757d;">Review today's queue and manage tokens by status.</p>
    </div>

    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['Active', '#0d6efd'],
            ['Called', '#fd7e14'],
            ['Completed', '#198754'],
            ['Missed', '#dc3545'],
        ];
        foreach ($cards as [$label, $color]): ?>
            <div class="col-6 col-lg-3">
                <div style="background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:18px;border-left:4px solid <?php echo $color; ?>;">
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#6c757d;font-weight:700;"><?php echo $label; ?></div>
                    <div style="font-size:30px;font-weight:700;color:#212529;"><?php echo number_format($counts[$label]); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3">
        <?php
        $links = [
            ['active.php', 'Active Tokens', 'Call, complete or miss patients currently in the queue.', '#0d6efd'],
            ['missed.php', 'Missed Tokens', 'Review missed tokens and send follow-up SMS notifications.', '#dc3545'],
            ['reschedule.php', 'Reschedule Tokens', 'Move missed or delayed tokens to a new slot.', '#198754'],
        ];
        foreach ($links as [$href, $title, $desc, $color]): ?>
            <div class="col-12 col-md-4">
                <a href="/smarthealth_nepal/admin/frontend/views/token_management/<?php echo $href; ?>"
                   style="display:block;background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:20px;text-decoration:none;height:100%;border-top:4px solid <?php echo $color; ?>;">
                    <h5 style="color:#212529;margin-bottom:8px;"><?php echo $title; ?></h5>
                    <p style="color:#6c757d;font-size:.9rem;margin:0;"><?php echo $desc; ?></p>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
