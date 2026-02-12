<?php
/**
 * Admin Model
 * Handles admin-specific database operations
 */

class AdminModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get admin by email
     */
    public function getAdminByEmail($email) {
        $sql = "SELECT id, email, password_hash, role, hospital_id, created_at FROM admins WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }
        
        $admin = $result->fetch_assoc();
        $stmt->close();
        return $admin;
    }
    
    /**
     * Get admin by ID
     */
    public function getAdminById($admin_id) {
        $sql = "SELECT id, email, role, hospital_id, created_at FROM admins WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }
        
        $admin = $result->fetch_assoc();
        $stmt->close();
        return $admin;
    }
    
    /**
     * Create new admin
     */
    public function createAdmin($email, $password, $role, $hospital_id = 1) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }
        
        // Check if email already exists
        if ($this->getAdminByEmail($email)) {
            return ['success' => false, 'message' => 'Email already exists'];
        }
        
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO admins (email, password_hash, role, hospital_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("sssi", $email, $password_hash, $role, $hospital_id);
        
        if ($stmt->execute()) {
            $admin_id = $this->db->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Admin created successfully', 'admin_id' => $admin_id];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to create admin'];
    }
    
    /**
     * Verify admin password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Update admin profile
     */
    public function updateAdminProfile($admin_id, $email) {
        if (empty($admin_id) || empty($email)) {
            return ['success' => false, 'message' => 'Admin ID and email are required'];
        }
        
        $sql = "UPDATE admins SET email = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("si", $email, $admin_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Profile updated'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to update profile'];
    }
    
    /**
     * Update admin password
     */
    public function updatePassword($admin_id, $new_password) {
        if (empty($admin_id) || empty($new_password)) {
            return ['success' => false, 'message' => 'Admin ID and password are required'];
        }
        
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        $sql = "UPDATE admins SET password_hash = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("si", $password_hash, $admin_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Password updated'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to update password'];
    }
    
    /**
     * Delete admin
     */
    public function deleteAdmin($admin_id) {
        if (empty($admin_id)) {
            return ['success' => false, 'message' => 'Admin ID is required'];
        }
        
        $sql = "DELETE FROM admins WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $admin_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Admin deleted'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to delete admin'];
    }
    
    /**
     * Get all admins
     */
    public function getAllAdmins() {
        $sql = "SELECT id, email, role, hospital_id, created_at FROM admins ORDER BY created_at DESC";
        $result = $this->db->query($sql);
        
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
        
        return $admins;
    }
    
    /**
     * Get admin count
     */
    public function getAdminCount() {
        $sql = "SELECT COUNT(*) as count FROM admins";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    
    /**
     * Get admins by hospital
     */
    public function getAdminsByHospital($hospital_id) {
        $sql = "SELECT id, email, role, created_at FROM admins WHERE hospital_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
        
        $stmt->close();
        return $admins;
    }
}
?>
