<?php
/**
 * Admin User Model
 * Handles user-related data operations for admins
 */

class AdminUserModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all users
     */
    public function getAllUsers($limit = 20, $offset = 0) {
        $sql = "SELECT id, phone, email, name, language, created_at 
                 FROM users 
                 ORDER BY created_at DESC 
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        $stmt->close();
        return $users;
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($user_id) {
        $sql = "SELECT id, phone, email, name, language, created_at FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    
    /**
     * Search users
     */
    public function searchUsers($query, $limit = 20) {
        $search_term = "%$query%";
        
        $sql = "SELECT id, phone, email, name, created_at FROM users 
                 WHERE phone LIKE ? OR email LIKE ? OR name LIKE ? 
                 LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("sssi", $search_term, $search_term, $search_term, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        $stmt->close();
        return $users;
    }
    
    /**
     * Update user
     */
    public function updateUser($user_id, $phone = null, $email = null, $name = null, $language = null) {
        $updates = [];
        $params = [];
        $types = '';
        
        if ($phone !== null) {
            $updates[] = 'phone = ?';
            $params[] = $phone;
            $types .= 's';
        }
        
        if ($email !== null) {
            $updates[] = 'email = ?';
            $params[] = $email;
            $types .= 's';
        }
        
        if ($name !== null) {
            $updates[] = 'name = ?';
            $params[] = $name;
            $types .= 's';
        }
        
        if ($language !== null) {
            $updates[] = 'language = ?';
            $params[] = $language;
            $types .= 's';
        }
        
        if (empty($updates)) {
            return ['success' => false];
        }
        
        $params[] = $user_id;
        $types .= 'i';
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        
        $stmt->close();
        return ['success' => false];
    }
    
    /**
     * Get user count
     */
    public function getUserCount() {
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    
    /**
     * Get users registered today
     */
    public function getUsersRegisteredToday() {
        $sql = "SELECT count(*) as count FROM users WHERE DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    
    /**
     * Get user tokens history
     */
    public function getUserTokensHistory($user_id, $limit = 10) {
        $sql = "SELECT t.id, t.token_number, t.priority, t.status, d.dept_name, t.created_at, t.completed_at
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 WHERE t.user_id = ?
                 ORDER BY t.created_at DESC
                 LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tokens = [];
        while ($row = $result->fetch_assoc()) {
            $tokens[] = $row;
        }
        
        $stmt->close();
        return $tokens;
    }
    
    /**
     * Get user health records
     */
    public function getUserHealthRecords($user_id) {
        $sql = "SELECT cd.*, 
                 DATEDIFF(CURDATE(), cd.next_followup) as days_overdue
                 FROM chronic_diseases cd
                 WHERE cd.user_id = ?
                 ORDER BY cd.next_followup ASC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        $stmt->close();
        return $records;
    }
    
    /**
     * Get female users with maternal records
     */
    public function getFemaleUsersWithMaternal() {
        $sql = "SELECT DISTINCT u.id, u.name, u.phone, mh.due_date, mh.antenatal_visits
                 FROM users u
                 JOIN maternal_health mh ON u.id = mh.user_id
                 ORDER BY mh.due_date ASC";
        
        $result = $this->db->query($sql);
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    /**
     * Get statistics
     */
    public function getUserStats() {
        $stats = [];
        
        // Total users
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = $this->db->query($sql);
        $stats['total'] = $result->fetch_assoc()['count'];
        
        // Users today
        $sql = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        $stats['today'] = $result->fetch_assoc()['count'];
        
        // Users with chronic diseases
        $sql = "SELECT COUNT(DISTINCT user_id) as count FROM chronic_diseases";
        $result = $this->db->query($sql);
        $stats['chronic'] = $result->fetch_assoc()['count'];
        
        // Maternal health patients
        $sql = "SELECT COUNT(DISTINCT user_id) as count FROM maternal_health";
        $result = $this->db->query($sql);
        $stats['maternal'] = $result->fetch_assoc()['count'];
        
        return $stats;
    }
}
?>
