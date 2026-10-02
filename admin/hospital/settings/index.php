<?php
/**
 * Hospital Settings - Landing
 * ---------------------------
 * Provides a real entry point for /admin/hospital/settings/ (previously a
 * directory with no index and therefore an Apache 403).
 */

// Central hospital-context resolution (auth + hospital/role rehydration).
require_once __DIR__ . '/../includes/context.php';

$pageTitle = 'Hospital Settings';
$activePage = 'settings';

$hospital = null;
if (isset($db) && !$db->connect_error && $hospital_id) {
    $stmt = $db->prepare("SELECT hospital_name, district, municipality, ward, phone, type, emergency_24_7
                          FROM hospital_locations WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $hospital_id);
    $stmt->execute();
    $hospital = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="section">
    <div style="margin-bottom: 24px;">
        <h2 style="margin-bottom: 6px;">Hospital Settings</h2>
        <p style="color: #8fa8ba; font-size: 13px;">
            Manage your account, notifications and security preferences.
        </p>
    </div>

    <?php if ($hospital): ?>
        <div style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
            <h3 style="margin-bottom: 10px;"><?php echo htmlspecialchars($hospital['hospital_name']); ?></h3>
            <p style="color: #4b5563; font-size: 13px; margin: 2px 0;">
                <?php echo htmlspecialchars(trim(($hospital['municipality'] ?? '') . (!empty($hospital['ward']) ? ', Ward ' . $hospital['ward'] : '') . ', ' . ($hospital['district'] ?? ''), ', ')); ?>
            </p>
            <p style="color: #4b5563; font-size: 13px; margin: 2px 0;">
                <strong>Type:</strong> <?php echo htmlspecialchars($hospital['type'] ?? '-'); ?>
                &nbsp;|&nbsp;
                <strong>Phone:</strong> <?php echo htmlspecialchars($hospital['phone'] ?? '-'); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="departments-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px;">
        <?php
        $cards = [
            ['account.php', 'Account', 'Email notifications, language, timezone and display preferences.'],
            ['notifications.php', 'Notifications', 'Control SMS and in-app alerts for queue events.'],
            ['security.php', 'Security', 'Password, sessions and account security settings.'],
            ['../profile/view.php', 'Profile', 'View your hospital administrator profile.'],
            ['../profile/edit.php', 'Edit Profile', 'Update your name, email and contact details.'],
            ['../profile/change-password.php', 'Change Password', 'Update the password used to sign in.'],
        ];
        foreach ($cards as [$href, $title, $desc]): ?>
            <a href="<?php echo htmlspecialchars($href); ?>"
               style="display:block; background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:18px; text-decoration:none; transition: box-shadow .2s ease;">
                <h4 style="color:#2c3e50; font-size:15px; margin-bottom:8px;"><?php echo htmlspecialchars($title); ?></h4>
                <p style="color:#8fa8ba; font-size:12px; margin:0;"><?php echo htmlspecialchars($desc); ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
