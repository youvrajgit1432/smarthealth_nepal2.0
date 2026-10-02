<?php
/**
 * Hospital Admin Profile - landing redirect
 *
 * There is no standalone profile dashboard; route this directory to the
 * canonical profile view.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

header('Location: /smarthealth_nepal/admin/hospital/profile/view.php');
exit;
