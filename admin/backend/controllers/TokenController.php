<?php
/**
 * Admin Token Management Controller
 * Handles token reschedule, reassignment, and emergency override
 */

class TokenManagementController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all active tokens
     */
    public function getActiveTokens($department_id = null) {
        $sql = "SELECT t.id, t.token_number, t.priority, t.status, d.dept_name,
                 u.name, u.phone, TIMESTAMPDIFF(MINUTE, t.created_at, NOW()) as wait_time
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 JOIN users u ON t.user_id = u.id
                 WHERE t.status IN ('Active', 'Called')";
        
        if ($department_id) {
            $sql .= " AND t.department_id = " . intval($department_id);
        }
        
        $sql .= " ORDER BY t.priority ASC, t.created_at ASC";
        
        $result = $this->db->query($sql);
        
        $tokens = [];
        while ($row = $result->fetch_assoc()) {
            $tokens[] = $row;
        }
        
        return $tokens;
    }
    
    /**
     * Get token details
     */
    public function getTokenDetail($token_id) {
        $sql = "SELECT t.*, d.dept_name, d.capacity, u.name, u.phone, u.email
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 JOIN users u ON t.user_id = u.id
                 WHERE t.id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $token_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Token not found'];
        }
        
        $token = $result->fetch_assoc();
        $stmt->close();
        
        return ['success' => true, 'token' => $token];
    }
    
    /**
     * Reschedule token to different time slot
     */
    public function rescheduleToken($token_id, $new_date, $new_time) {
        if (!$this->validateDateTime($new_date, $new_time)) {
            return ['success' => false, 'message' => 'Invalid date or time'];
        }
        
        $new_datetime = $new_date . ' ' . $new_time . ':00';
        
        $sql = "UPDATE tokens SET created_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("si", $new_datetime, $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Token rescheduled successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to reschedule token'];
    }
    
    /**
     * Reassign token to different department
     */
    public function reassignToken($token_id, $new_department_id) {
        // Verify department exists
        $sql = "SELECT id FROM departments WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $new_department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Department not found'];
        }
        
        $stmt->close();
        
        // Update token department
        $sql = "UPDATE tokens SET department_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ii", $new_department_id, $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Token reassigned successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to reassign token'];
    }
    
    /**
     * Emergency priority override
     */
    public function setEmergencyPriority($token_id) {
        $sql = "UPDATE tokens SET priority = 'Emergency' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Emergency priority set'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to set emergency priority'];
    }
    
    /**
     * Get missed tokens
     */
    public function getMissedTokens($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $sql = "SELECT t.id, t.token_number, d.dept_name, u.name, u.phone, t.created_at
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 JOIN users u ON t.user_id = u.id
                 WHERE t.status = 'Missed' AND DATE(t.created_at) = ?
                 ORDER BY t.created_at DESC";
        
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
     * Create manual token for walk-in patient
     */
    public function createManualToken($phone, $name, $department_id, $priority = 'Normal') {
        if (empty($phone) || empty($name) || empty($department_id)) {
            return ['success' => false, 'message' => 'Phone, name, and department are required'];
        }
        
        // Check if user exists, if not create one
        $sql = "SELECT id FROM users WHERE phone = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Create new user
            $sql = "INSERT INTO users (phone, name, language) VALUES (?, ?, 'en')";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                return ['success' => false, 'message' => 'Database error'];
            }
            
            $stmt->bind_param("ss", $phone, $name);
            $stmt->execute();
            $user_id = $this->db->insert_id;
            $stmt->close();
        } else {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            $stmt->close();
        }
        
        // Create token
        $token_number = $this->generateTokenNumber($department_id);
        $triage_type = match($priority) {
            'Emergency' => 'emergency',
            'Priority' => 'priority',
            'Chronic' => 'chronic',
            default => 'normal'
        };
        
        $sql = "INSERT INTO tokens (user_id, token_number, priority, triage_type, department_id, status)
                 VALUES (?, ?, ?, ?, ?, 'Active')";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("isssi", $user_id, $token_number, $priority, $triage_type, $department_id);
        
        if ($stmt->execute()) {
            $token_id = $this->db->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Manual token created', 'token_id' => $token_id, 'token_number' => $token_number];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to create token'];
    }
    
    /**
     * Generate unique token number
     */
    private function generateTokenNumber($department_id) {
        $today = date('Ymd');
        
        $sql = "SELECT MAX(CAST(SUBSTRING(token_number, -4) AS UNSIGNED)) as max_num 
                 FROM tokens 
                 WHERE department_id = ? AND DATE(created_at) = CURDATE()";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return 'TKN' . $today . '0001';
        }
        
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $next_num = ($row['max_num'] ?? 0) + 1;
        return 'T' . $today . str_pad($next_num, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Validate date and time
     */
    private function validateDateTime($date, $time) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            return false;
        }
        
        // Check if date is in future
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Cancel token
     */
    public function cancelToken($token_id, $reason = '') {
        $sql = "UPDATE tokens SET status = 'Cancelled' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Token cancelled successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to cancel token'];
    }
}
?>
