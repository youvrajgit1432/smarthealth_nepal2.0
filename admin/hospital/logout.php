<?php
/**
 * Logout Handler
 */

session_start();
session_destroy();

header('Location: /smarthealth_nepal/admin/hospital/login.php');
exit;
?>
