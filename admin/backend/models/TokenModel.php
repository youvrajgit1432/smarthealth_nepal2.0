<?php
/**
 * Admin Token Model
 * Handles admin-specific token operations
 */

class TokenModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get token by ID
     */
    public function getTokenById($token_id) {
        $sql = "SELECT t.*, u.name, u.phone, d.dept_name 
                 FROM tokens t
                 LEFT JOIN users u ON t.user_id = u.id
                 LEFT JOIN departments d ON t.department_id = d.id
                 WHERE t.id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("i", $token_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }
        
        $token = $result->fetch_assoc();
        $stmt->close();
        return $token;
    }
    
    /**
     * Get active tokens for a department
     */
    public function getActiveTokensByDepartment($dept_id) {
        $sql = "SELECT t.id, t.token_number, t.priority, t.status, u.name, u.phone,
                 TIMESTAMPDIFF(MINUTE, t.created_at, NOW()) as wait_time
                 FROM tokens t
                 JOIN users u ON t.user_id = u.id
                 WHERE t.department_id = ? AND t.status IN ('Active', 'Called')
                 ORDER BY t.priority ASC, t.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $dept_id);
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
     * Update token status
     */
    public function updateTokenStatus($token_id, $status) {
        $sql = "UPDATE tokens SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("si", $status, $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        
        $stmt->close();
        return ['success' => false];
    }
    
    /**
     * Update token priority
     */
    public function updateTokenPriority($token_id, $priority) {
        $sql = "UPDATE tokens SET priority = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("si", $priority, $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        
        $stmt->close();
        return ['success' => false];
    }
    
    /**
     * Reassign token to different department
     */
    public function reassignToken($token_id, $department_id) {
        $sql = "UPDATE tokens SET department_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("ii", $department_id, $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        
        $stmt->close();
        return ['success' => false];
    }
    
    /**
     * Reschedule token
     */
    public function rescheduleToken($token_id, $new_date) {
        $sql = "UPDATE tokens SET created_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("si", $new_date, $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        
        $stmt->close();
        return ['success' => false];
    }
    
    /**
     * Complete token
     */
    public function completeToken($token_id) {
        $sql = "UPDATE tokens SET status = 'Completed', completed_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("i", $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        
        $stmt->close();
        return ['success' => false];
    }
    
    /**
     * Miss token
     */
    public function missToken($token_id) {
        $sql = "UPDATE tokens SET status = 'Missed' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("i", $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        
        $stmt->close();
        return ['success' => false];
    }
    
    /**
     * Get tokens by date
     */
    public function getTokensByDate($date) {
        $sql = "SELECT t.id, t.token_number, t.priority, t.status, d.dept_name, u.name, u.phone
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 JOIN users u ON t.user_id = u.id
                 WHERE DATE(t.created_at) = ?
                 ORDER BY t.priority ASC, t.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("s", $date);
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
     * Get missed tokens
     */
    public function getMissedTokens($limit = 10) {
        $sql = "SELECT t.id, t.token_number, d.dept_name, u.name, u.phone, t.created_at
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 JOIN users u ON t.user_id = u.id
                 WHERE t.status = 'Missed'
                 ORDER BY t.created_at DESC
                 LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $limit);
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
     * Get statistics
     */
    public function getTokenStats() {
        $stats = [];
        
        // Total tokens today
        $sql = "SELECT COUNT(*) as count FROM tokens WHERE DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        $stats['total_today'] = $result->fetch_assoc()['count'];
        
        // Completed today
        $sql = "SELECT COUNT(*) as count FROM tokens WHERE status = 'Completed' AND DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        $stats['completed_today'] = $result->fetch_assoc()['count'];
        
        // Missed today
        $sql = "SELECT COUNT(*) as count FROM tokens WHERE status = 'Missed' AND DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        $stats['missed_today'] = $result->fetch_assoc()['count'];
        
        // Active now
        $sql = "SELECT COUNT(*) as count FROM tokens WHERE status IN ('Active', 'Called')";
        $result = $this->db->query($sql);
        $stats['active_now'] = $result->fetch_assoc()['count'];
        
        return $stats;
    }
    
    /**
     * Get queue position for token
     */
    public function getQueuePosition($token_id) {
        // Get token first
        $token = $this->getTokenById($token_id);
        if (!$token) {
            return 0;
        }
        
        // Count how many tokens are ahead
        $sql = "SELECT COUNT(*) as position FROM tokens 
                 WHERE department_id = ? AND priority < ? 
                 AND status IN ('Active', 'Called')
                 AND created_at < ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return 0;
        }
        
        $stmt->bind_param("iss", $token['department_id'], $token['priority'], $token['created_at']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['position'] ?? 0;
    }
}
?>
