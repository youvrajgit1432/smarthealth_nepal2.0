<?php
/**
 * Admin User Management Controller
 * Handles user CRUD operations, search, and management
 */

class UserController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all users with pagination
     */
    public function getAllUsers($page = 1, $limit = 20, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT id, phone, email, name, language, created_at FROM users";
        
        if (!empty($search)) {
            $search_term = "%$search%";
            $sql .= " WHERE phone LIKE ? OR email LIKE ? OR name LIKE ?";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        if (!empty($search)) {
            $stmt->bind_param("sssii", $search_term, $search_term, $search_term, $limit, $offset);
        } else {
            $stmt->bind_param("ii", $limit, $offset);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        $stmt->close();
        
        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM users";
        if (!empty($search)) {
            $count_sql .= " WHERE phone LIKE ? OR email LIKE ? OR name LIKE ?";
        }
        
        $count_stmt = $this->db->prepare($count_sql);
        if ($count_stmt) {
            if (!empty($search)) {
                $count_stmt->bind_param("sss", $search_term, $search_term, $search_term);
            }
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_row = $count_result->fetch_assoc();
            $total = $count_row['total'];
            $count_stmt->close();
        } else {
            $total = 0;
        }
        
        return [
            'success' => true,
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($user_id) {
        $sql = "SELECT id, phone, email, name, language, created_at FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'User not found'];
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Create new user
     */
    public function createUser($phone, $name, $email = '', $language = 'en') {
        if (empty($phone) || empty($name)) {
            return ['success' => false, 'message' => 'Phone and name are required'];
        }
        
        // Check if phone already exists
        $sql = "SELECT id FROM users WHERE phone = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Phone number already exists'];
        }
        
        $stmt->close();
        
        // Create user
        $sql = "INSERT INTO users (phone, name, email, language) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ssss", $phone, $name, $email, $language);
        
        if ($stmt->execute()) {
            $user_id = $this->db->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'User created successfully', 'user_id' => $user_id];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to create user'];
    }
    
    /**
     * Update user
     */
    public function updateUser($user_id, $data) {
        if (empty($user_id)) {
            return ['success' => false, 'message' => 'User ID is required'];
        }
        
        $updates = [];
        $params = [];
        $types = '';
        
        if (isset($data['name'])) {
            $updates[] = 'name = ?';
            $params[] = $data['name'];
            $types .= 's';
        }
        
        if (isset($data['email'])) {
            $updates[] = 'email = ?';
            $params[] = $data['email'];
            $types .= 's';
        }
        
        if (isset($data['language'])) {
            $updates[] = 'language = ?';
            $params[] = $data['language'];
            $types .= 's';
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }
        
        $params[] = $user_id;
        $types .= 'i';
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'User updated successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to update user'];
    }
    
    /**
     * Delete user
     */
    public function deleteUser($user_id) {
        if (empty($user_id)) {
            return ['success' => false, 'message' => 'User ID is required'];
        }
        
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'User deleted successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to delete user'];
    }
    
    /**
     * Get user tokens
     */
    public function getUserTokens($user_id) {
        $sql = "SELECT t.id, t.token_number, t.priority, t.status, d.dept_name, t.created_at
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 WHERE t.user_id = ?
                 ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $user_id);
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
     * Get user chronic diseases
     */
    public function getUserChronicDiseases($user_id) {
        $sql = "SELECT id, disease_type, diagnosis_date, last_visit, next_followup, status
                 FROM chronic_diseases
                 WHERE user_id = ?
                 ORDER BY diagnosis_date DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $diseases = [];
        while ($row = $result->fetch_assoc()) {
            $diseases[] = $row;
        }
        
        $stmt->close();
        return $diseases;
    }
    
    /**
     * Search users
     */
    public function searchUsers($query, $limit = 10) {
        $search_term = "%$query%";
        
        $sql = "SELECT id, phone, email, name FROM users 
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
     * Get user statistics
     */
    public function getUserStats() {
        $stats = [];
        
        // Total users
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = $this->db->query($sql);
        $stats['total_users'] = $result->fetch_assoc()['count'];
        
        // Users today
        $sql = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        $stats['users_today'] = $result->fetch_assoc()['count'];
        
        // Users with chronic diseases
        $sql = "SELECT COUNT(DISTINCT user_id) as count FROM chronic_diseases";
        $result = $this->db->query($sql);
        $stats['chronic_users'] = $result->fetch_assoc()['count'];
        
        // Users with maternal health
        $sql = "SELECT COUNT(DISTINCT user_id) as count FROM maternal_health";
        $result = $this->db->query($sql);
        $stats['maternal_users'] = $result->fetch_assoc()['count'];
        
        return $stats;
    }
}
?>
