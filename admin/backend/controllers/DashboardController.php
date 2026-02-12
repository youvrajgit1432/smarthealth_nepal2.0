<?php
/**
 * Admin Dashboard Controller
 * Handles real-time statistics, queue metrics, and admin dashboard data
 */

class DashboardController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get dashboard statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Active tokens count
        $sql = "SELECT COUNT(*) as count FROM tokens WHERE status = 'Active' AND DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        $stats['active_tokens'] = $result->fetch_assoc()['count'];
        
        // Completed tokens today
        $sql = "SELECT COUNT(*) as count FROM tokens WHERE status = 'Completed' AND DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        $stats['completed_today'] = $result->fetch_assoc()['count'];
        
        // Missed tokens
        $sql = "SELECT COUNT(*) as count FROM tokens WHERE status = 'Missed' AND DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        $stats['missed_today'] = $result->fetch_assoc()['count'];
        
        // Total registered users
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = $this->db->query($sql);
        $stats['total_users'] = $result->fetch_assoc()['count'];
        
        // Emergency alerts
        $sql = "SELECT COUNT(*) as count FROM tokens WHERE priority = 'Emergency' AND status = 'Active'";
        $result = $this->db->query($sql);
        $stats['emergency_count'] = $result->fetch_assoc()['count'];
        
        // Average wait time today
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_wait FROM tokens WHERE status = 'Completed' AND DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql);
        $wait = $result->fetch_assoc()['avg_wait'];
        $stats['avg_wait_time'] = $wait ? round($wait) : 0;
        
        return $stats;
    }
    
    /**
     * Get active queue
     */
    public function getActiveQueue($limit = 10) {
        $sql = "SELECT t.id, t.token_number, t.priority, t.status, d.dept_name, 
                 TIMESTAMPDIFF(MINUTE, t.created_at, NOW()) as wait_time,
                 u.name, u.phone
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 JOIN users u ON t.user_id = u.id
                 WHERE t.status IN ('Active', 'Called')
                 ORDER BY t.priority ASC, t.created_at ASC
                 LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $queue = [];
        while ($row = $result->fetch_assoc()) {
            $queue[] = $row;
        }
        
        $stmt->close();
        return $queue;
    }
    
    /**
     * Get department load status
     */
    public function getDepartmentLoads() {
        $sql = "SELECT id, dept_name, capacity, current_load, 
                 ROUND((current_load / capacity) * 100, 2) as load_percentage
                 FROM departments
                 ORDER BY load_percentage DESC";
        
        $result = $this->db->query($sql);
        
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $load_pct = $row['load_percentage'];
            if ($load_pct >= 80) {
                $row['load_status'] = 'High';
                $row['status_color'] = 'danger';
            } elseif ($load_pct >= 50) {
                $row['load_status'] = 'Moderate';
                $row['status_color'] = 'warning';
            } else {
                $row['load_status'] = 'Low';
                $row['status_color'] = 'success';
            }
            $departments[] = $row;
        }
        
        return $departments;
    }
    
    /**
     * Get missed appointments
     */
    public function getMissedAppointments($limit = 5) {
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
        
        $missed = [];
        while ($row = $result->fetch_assoc()) {
            $missed[] = $row;
        }
        
        $stmt->close();
        return $missed;
    }
    
    /**
     * Get emergency tokens
     */
    public function getEmergencyTokens() {
        $sql = "SELECT t.id, t.token_number, d.dept_name, u.name, u.phone, 
                 TIMESTAMPDIFF(MINUTE, t.created_at, NOW()) as wait_time
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 JOIN users u ON t.user_id = u.id
                 WHERE t.priority = 'Emergency' AND t.status = 'Active'
                 ORDER BY t.created_at ASC";
        
        $result = $this->db->query($sql);
        
        $emergencies = [];
        while ($row = $result->fetch_assoc()) {
            $emergencies[] = $row;
        }
        
        return $emergencies;
    }
    
    /**
     * Call next token
     */
    public function callNextToken($department_id) {
        // Get next token in queue
        $sql = "SELECT id FROM tokens 
                 WHERE department_id = ? AND status = 'Active'
                 ORDER BY priority ASC, created_at ASC
                 LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'No active tokens to call'];
        }
        
        $token = $result->fetch_assoc();
        $token_id = $token['id'];
        $stmt->close();
        
        // Update token status to "Called"
        $sql = "UPDATE tokens SET status = 'Called' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Token called successfully', 'token_id' => $token_id];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to call token'];
    }
    
    /**
     * Mark token as completed
     */
    public function completeToken($token_id) {
        $sql = "UPDATE tokens SET status = 'Completed', completed_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Token marked as completed'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to mark token as completed'];
    }
    
    /**
     * Mark token as missed
     */
    public function missToken($token_id) {
        $sql = "UPDATE tokens SET status = 'Missed' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $token_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Token marked as missed'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to mark token as missed'];
    }
    
    /**
     * Get today's summary report
     */
    public function getTodaysSummary() {
        $date = date('Y-m-d');
        
        $sql = "SELECT 
                 SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                 SUM(CASE WHEN status = 'Missed' THEN 1 ELSE 0 END) as missed,
                 SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
                 SUM(CASE WHEN status = 'Called' THEN 1 ELSE 0 END) as called,
                 COUNT(*) as total
                 FROM tokens
                 WHERE DATE(created_at) = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $summary = $result->fetch_assoc();
        $stmt->close();
        
        return ['success' => true, 'summary' => $summary];
    }
    
    /**
     * Get priority distribution
     */
    public function getPriorityDistribution() {
        $date = date('Y-m-d');
        
        $sql = "SELECT priority, COUNT(*) as count FROM tokens 
                 WHERE DATE(created_at) = ?
                 GROUP BY priority";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $distribution = [];
        while ($row = $result->fetch_assoc()) {
            $distribution[$row['priority']] = $row['count'];
        }
        
        $stmt->close();
        return $distribution;
    }
}
?>
