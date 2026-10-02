<?php
/**
 * Legacy public assisted-booking entry.
 *
 * Assisted booking is handled by /smarthealth_nepal/public/hospital-detail.php,
 * which also renders the hospital's booking form. The old copy relied on a
 * controller API that no longer exists, so forward visitors to the working page.
 */

$hospital_id = isset($_GET['hospital_id']) ? intval($_GET['hospital_id']) : 0;

if ($hospital_id > 0) {
    header('Location: /smarthealth_nepal/public/hospital-detail.php?id=' . $hospital_id);
} else {
    header('Location: /smarthealth_nepal/public/hospitals.php');
}
exit;
?>
