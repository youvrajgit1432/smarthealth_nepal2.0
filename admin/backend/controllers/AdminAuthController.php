<?php
/**
 * Admin Authentication Controller
 * Handles admin login, logout, and session management
 */

class AdminAuthController {
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    /**
     * Handle admin login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Invalid request method'];
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }
        
        // Check admin credentials
        $sql = "SELECT id, email, password_hash, role, hospital_id FROM admins WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows !== 1) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        $admin = $result->fetch_assoc();
        
        // Verify password
        if (!password_verify($password, $admin['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Create session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['hospital_id'] = $admin['hospital_id'];
        $_SESSION['is_admin'] = true;
        
        $stmt->close();
        
        return ['success' => true, 'message' => 'Login successful', 'admin_id' => $admin['id']];
    }
    
    /**
     * Handle admin logout
     */
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    /**
     * Check if admin is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
    
    /**
     * Check admin role
     */
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        return $_SESSION['admin_role'] === $role;
    }
    
    /**
     * Require admin login
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /smarthealth_nepal/admin/public/index.php?page=login');
            exit;
        }
    }
    
    /**
     * Update admin profile
     */
    public function updateProfile($data) {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'Not logged in'];
        }
        
        $admin_id = $_SESSION['admin_id'];
        $email = $data['email'] ?? '';
        
        if (empty($email)) {
            return ['success' => false, 'message' => 'Email is required'];
        }
        
        $sql = "UPDATE admins SET email = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("si", $email, $admin_id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_email'] = $email;
            $stmt->close();
            return ['success' => true, 'message' => 'Profile updated successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to update profile'];
    }
    
    /**
     * Change admin password
     */
    public function changePassword($old_password, $new_password) {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'Not logged in'];
        }
        
        if (empty($old_password) || empty($new_password)) {
            return ['success' => false, 'message' => 'Old and new passwords are required'];
        }
        
        $admin_id = $_SESSION['admin_id'];
        
        // Get current password hash
        $sql = "SELECT password_hash FROM admins WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();
        
        // Verify old password
        if (!password_verify($old_password, $admin['password_hash'])) {
            return ['success' => false, 'message' => 'Old password is incorrect'];
        }
        
        // Hash new password
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Update password
        $sql = "UPDATE admins SET password_hash = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("si", $new_hash, $admin_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Password changed successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to change password'];
    }
    
    /**
     * Get admin details
     */
    public function getAdminDetails() {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'Not logged in'];
        }
        
        $admin_id = $_SESSION['admin_id'];
        
        $sql = "SELECT id, email, role, hospital_id, created_at FROM admins WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows !== 1) {
            $stmt->close();
            return ['success' => false, 'message' => 'Admin not found'];
        }
        
        $admin = $result->fetch_assoc();
        $stmt->close();
        
        return ['success' => true, 'admin' => $admin];
    }
}
?>
