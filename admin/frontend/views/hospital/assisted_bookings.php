<?php
/**
 * Legacy assisted bookings page (superseded).
 *
 * The maintained hospital portal lives at /smarthealth_nepal/admin/hospital/.
 * Forward to the canonical assisted bookings page.
 */

$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: /smarthealth_nepal/admin/hospital/assisted-bookings/' . ($query !== '' ? '?' . $query : ''));
exit;
