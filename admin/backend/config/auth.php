<?php
/**
 * Admin Backend - Authentication Configuration / Helpers
 * ------------------------------------------------------
 * Lightweight helpers used across the admin panel. They build on the
 * native PHP session (admin_id / admin_role / hospital_id).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('admin_is_logged_in')) {
    function admin_is_logged_in() {
        return isset($_SESSION['admin_id']);
    }
}

if (!function_exists('admin_is_super')) {
    function admin_is_super() {
        return isset($_SESSION['admin_role'])
            && strtolower($_SESSION['admin_role']) === 'superadmin';
    }
}

if (!function_exists('admin_hospital_id')) {
    function admin_hospital_id() {
        return $_SESSION['hospital_id'] ?? null;
    }
}

if (!function_exists('require_admin')) {
    function require_admin() {
        if (!admin_is_logged_in()) {
            header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
            exit;
        }
    }
}

if (!function_exists('require_hospital_admin')) {
    function require_hospital_admin() {
        if (!admin_is_logged_in()) {
            header('Location: /smarthealth_nepal/admin/hospital/login.php');
            exit;
        }
    }
}
?>
