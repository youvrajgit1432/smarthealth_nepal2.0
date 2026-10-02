<?php
/**
 * Legacy hospital dashboard (superseded).
 *
 * The maintained hospital portal lives at /smarthealth_nepal/admin/hospital/.
 * Forward to the canonical dashboard.
 */

$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: /smarthealth_nepal/admin/hospital/dashboard/' . ($query !== '' ? '?' . $query : ''));
exit;
