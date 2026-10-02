<?php
/**
 * Central hospital-context resolution
 * -----------------------------------
 * Every authenticated hospital-panel request funnels through here so the
 * active hospital is resolved from the database once, never from a stale or
 * missing session value.
 *
 *  - HospitalAdmin : the hospital is taken from `admins.hospital_id` and must
 *                    point at an active hospital. If it does not, the session
 *                    is invalidated and the user is sent back to login.
 *  - SuperAdmin    : may explicitly select a hospital via `hospital_id`
 *                    (GET/POST). The selection is validated against
 *                    `hospital_locations` and persisted in the session so it
 *                    survives navigation across every hospital page.
 *
 * Exposes: $hospital_context, $access_type, $hospital_id, $admin_role
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../backend/config/database.php';

if (!function_exists('smarthealth_resolve_hospital_context')) {
    /**
     * @param mysqli $db
     * @return array{ok:bool,reason?:string,access_type?:string,hospital_id?:int|null,admin?:array}
     */
    function smarthealth_resolve_hospital_context($db)
    {
        if (!isset($_SESSION['admin_id'])) {
            return ['ok' => false, 'reason' => 'unauthenticated'];
        }

        if (!isset($db) || $db->connect_error) {
            return ['ok' => false, 'reason' => 'db'];
        }

        $adminId = (int) $_SESSION['admin_id'];

        // Always re-read the account so a stale session self-heals instead of
        // silently querying `WHERE id = 0`.
        $stmt = $db->prepare(
            "SELECT id, username, full_name, email, role, hospital_id, is_active
               FROM admins WHERE id = ? LIMIT 1"
        );
        if (!$stmt) {
            return ['ok' => false, 'reason' => 'db'];
        }
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$admin || (int) $admin['is_active'] !== 1) {
            return ['ok' => false, 'reason' => 'invalid_account'];
        }

        // Rehydrate identity every request.
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name']     = $admin['full_name'] ?: $admin['username'];
        $_SESSION['admin_email']    = $admin['email'] ?? '';
        $_SESSION['admin_role']     = $admin['role'] ?? 'Admin';

        $accessType = strtolower($admin['role'] ?? '') === 'superadmin' ? 'super' : 'hospital';
        $_SESSION['access_type'] = $accessType;

        // ------------------------------------------------------------------
        // SuperAdmin: explicit, validated hospital selection.
        // ------------------------------------------------------------------
        if ($accessType === 'super') {
            $selected = 0;
            if (!empty($_GET['hospital_id'])) {
                $selected = (int) $_GET['hospital_id'];
            } elseif (!empty($_POST['hospital_id'])) {
                $selected = (int) $_POST['hospital_id'];
            } elseif (!empty($_SESSION['selected_hospital_id'])) {
                $selected = (int) $_SESSION['selected_hospital_id'];
            } elseif (!empty($admin['hospital_id'])) {
                $selected = (int) $admin['hospital_id'];
            }

            if ($selected > 0) {
                $chk = $db->prepare("SELECT id FROM hospital_locations WHERE id = ? AND is_active = 1 LIMIT 1");
                $chk->bind_param('i', $selected);
                $chk->execute();
                $valid = $chk->get_result()->fetch_assoc();
                $chk->close();

                if ($valid) {
                    $_SESSION['selected_hospital_id'] = $selected;
                    $_SESSION['hospital_id'] = $selected;
                    return ['ok' => true, 'access_type' => 'super', 'hospital_id' => $selected, 'admin' => $admin];
                }
                // Arbitrary/invalid id → no selection; the selector is shown.
                unset($_SESSION['selected_hospital_id']);
            }

            $_SESSION['hospital_id'] = null;
            return ['ok' => true, 'access_type' => 'super', 'hospital_id' => null, 'admin' => $admin];
        }

        // ------------------------------------------------------------------
        // HospitalAdmin: must resolve to a valid, active hospital.
        // ------------------------------------------------------------------
        $hospitalId = (int) ($admin['hospital_id'] ?? 0);
        if ($hospitalId > 0) {
            $chk = $db->prepare("SELECT id FROM hospital_locations WHERE id = ? AND is_active = 1 LIMIT 1");
            $chk->bind_param('i', $hospitalId);
            $chk->execute();
            $valid = $chk->get_result()->fetch_assoc();
            $chk->close();

            if ($valid) {
                $_SESSION['hospital_id'] = $hospitalId;
                $_SESSION['selected_hospital_id'] = $hospitalId;
                return ['ok' => true, 'access_type' => 'hospital', 'hospital_id' => $hospitalId, 'admin' => $admin];
            }

            return ['ok' => false, 'reason' => 'hospital_inactive'];
        }

        return ['ok' => false, 'reason' => 'no_hospital'];
    }
}

if (!function_exists('smarthealth_invalidate_hospital_session')) {
    function smarthealth_invalidate_hospital_session()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }
        session_destroy();
    }
}

$hospital_context = smarthealth_resolve_hospital_context($db);

if (empty($hospital_context['ok'])) {
    $reason = $hospital_context['reason'] ?? 'unknown';

    // A database outage is not the user's fault — don't destroy their session.
    if ($reason === 'db') {
        http_response_code(503);
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
            . '<title>Service unavailable - SmartHealth Nepal</title></head><body '
            . 'style="font-family:system-ui,sans-serif;padding:40px;background:#f4f6fa;color:#2c3e50;">'
            . '<h1>Service temporarily unavailable</h1>'
            . '<p>The hospital panel could not reach the database. Please try again shortly.</p>'
            . '<p><a href="/smarthealth_nepal/admin/hospital/login.php">Back to login</a></p></body></html>';
        exit;
    }

    $messages = [
        'invalid_account'   => 'Your admin account is no longer active. Please contact a super administrator.',
        'hospital_inactive' => 'Your assigned hospital is no longer active. Please contact a super administrator.',
        'no_hospital'       => 'No hospital is assigned to your account. Please contact a super administrator.',
    ];
    $message = $messages[$reason] ?? 'Your session is no longer valid. Please sign in again.';

    smarthealth_invalidate_hospital_session();
    header('Location: /smarthealth_nepal/admin/hospital/login.php?error=' . urlencode($message));
    exit;
}

$access_type = $hospital_context['access_type'];
$hospital_id = $hospital_context['hospital_id'];
$admin_role  = $_SESSION['admin_role'] ?? 'Admin';
