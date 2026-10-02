<?php
/**
 * Legacy public hospital listing.
 *
 * The maintained hospital directory lives at /smarthealth_nepal/public/hospitals.php.
 * This legacy copy depended on a controller API that no longer exists, so it now
 * forwards to the canonical page (preserving any filter query string).
 */

$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: /smarthealth_nepal/public/hospitals.php' . ($query !== '' ? '?' . $query : ''));
exit;
?>
