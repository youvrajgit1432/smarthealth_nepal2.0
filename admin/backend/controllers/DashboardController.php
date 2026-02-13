<?php
/**
 * Admin Dashboard Controller
 * Handles real-time statistics, queue metrics, and admin dashboard data
 */

class DashboardController {
    private $db;
    private $hospital_id = null;
    private $is_superadmin = false;
    
    public function __construct($db) {
        $this->db = $db;
        // Check if user is superadmin (case-insensitive check)
        if (isset($_SESSION['admin_role']) && strtolower($_SESSION['admin_role']) === 'superadmin') {
            $this->is_superadmin = true;
        } else if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'SuperAdmin') {
            $this->is_superadmin = true;
        } else {
            // If not superadmin, set hospital_id from session
            $this->hospital_id = $_SESSION['hospital_id'] ?? null;
        }
    }
    
    /**
     * Get hospital_id filter for queries
     */
    private function getHospitalFilter() {
        if ($this->is_superadmin) {
            return '';
        }
        return $this->hospital_id ? "AND hd.hospital_id = " . (int)$this->hospital_id : "";
    }
    
    /**
     * Get dashboard statistics
     */
    public function getStatistics() {
        $stats = [];
        
        $filter = $this->is_superadmin ? '' : ($this->hospital_id ? " AND d.hospital_id = " . (int)$this->hospital_id : "");
        
        // Active tokens count
        if ($this->is_superadmin) {
            $sql = "SELECT COUNT(*) as count FROM tokens t 
                     WHERE t.status = 'Active' AND DATE(t.created_at) = CURDATE()";
        } else {
            $sql = "SELECT COUNT(*) as count FROM tokens t 
                     WHERE t.hospital_id = " . (int)$this->hospital_id . " AND t.status = 'Active' AND DATE(t.created_at) = CURDATE()";
        }
        $result = $this->db->query($sql);
        $stats['active_tokens'] = $result->fetch_assoc()['count'];
        
        // Completed tokens today
        if ($this->is_superadmin) {
            $sql = "SELECT COUNT(*) as count FROM tokens t 
                     WHERE t.status = 'Completed' AND DATE(t.created_at) = CURDATE()";
        } else {
            $sql = "SELECT COUNT(*) as count FROM tokens t 
                     WHERE t.hospital_id = " . (int)$this->hospital_id . " AND t.status = 'Completed' AND DATE(t.created_at) = CURDATE()";
        }
        $result = $this->db->query($sql);
        $stats['completed_today'] = $result->fetch_assoc()['count'];
        
        // Missed tokens
        if ($this->is_superadmin) {
            $sql = "SELECT COUNT(*) as count FROM tokens t 
                     WHERE t.status = 'Missed' AND DATE(t.created_at) = CURDATE()";
        } else {
            $sql = "SELECT COUNT(*) as count FROM tokens t 
                     WHERE t.hospital_id = " . (int)$this->hospital_id . " AND t.status = 'Missed' AND DATE(t.created_at) = CURDATE()";
        }
        $result = $this->db->query($sql);
        $stats['missed_today'] = $result->fetch_assoc()['count'];
        
        // Total registered users (superadmin sees all, hospital admin sees users from their hospital's tokens)
        if ($this->is_superadmin) {
            $sql = "SELECT COUNT(*) as count FROM users";
        } else {
            $sql = "SELECT COUNT(DISTINCT u.id) as count FROM users u 
                     JOIN tokens t ON u.id = t.user_id 
                     JOIN departments d ON t.department_id = d.id 
                     WHERE d.hospital_id = " . (int)$this->hospital_id;
        }
        $result = $this->db->query($sql);
        $stats['total_users'] = $result->fetch_assoc()['count'];
        
        // Emergency alerts
        if ($this->is_superadmin) {
            $sql = "SELECT COUNT(*) as count FROM tokens t 
                     WHERE t.priority = 'Emergency' AND t.status = 'Active'";
        } else {
            $sql = "SELECT COUNT(*) as count FROM tokens t 
                     WHERE t.hospital_id = " . (int)$this->hospital_id . " AND t.priority = 'Emergency' AND t.status = 'Active'";
        }
        $result = $this->db->query($sql);
        $stats['emergency_count'] = $result->fetch_assoc()['count'];
        
        // Average wait time today
        if ($this->is_superadmin) {
            $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.completed_at)) as avg_wait FROM tokens t 
                     WHERE t.status = 'Completed' AND DATE(t.created_at) = CURDATE()";
        } else {
            $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, t.created_at, t.completed_at)) as avg_wait FROM tokens t 
                     WHERE t.hospital_id = " . (int)$this->hospital_id . " AND t.status = 'Completed' AND DATE(t.created_at) = CURDATE()";
        }
        $result = $this->db->query($sql);
        $wait = $result->fetch_assoc()['avg_wait'];
        $stats['avg_wait_time'] = $wait ? round($wait) : 0;
        
        return $stats;
    }
    
    /**
     * Get active queue
     */
    public function getActiveQueue($limit = 10) {
        if ($this->is_superadmin) {
            $sql = "SELECT t.id, t.token_number, t.priority, t.status, d.name_en as dept_name, 
                     TIMESTAMPDIFF(MINUTE, t.created_at, NOW()) as wait_time,
                     u.full_name as name, u.phone_number as phone
                     FROM tokens t
                     LEFT JOIN departments d ON t.department_id = d.id
                     LEFT JOIN users u ON t.user_id = u.id
                     WHERE t.status IN ('Active', 'Called')
                     ORDER BY FIELD(t.priority, 'Emergency', 'Priority', 'Chronic', 'Normal'), t.created_at ASC
                     LIMIT ?";
        } else {
            $sql = "SELECT t.id, t.token_number, t.priority, t.status, d.name_en as dept_name, 
                     TIMESTAMPDIFF(MINUTE, t.created_at, NOW()) as wait_time,
                     u.full_name as name, u.phone_number as phone
                     FROM tokens t
                     LEFT JOIN departments d ON t.department_id = d.id
                     LEFT JOIN users u ON t.user_id = u.id
                     WHERE t.hospital_id = " . (int)$this->hospital_id . " AND t.status IN ('Active', 'Called')
                     ORDER BY FIELD(t.priority, 'Emergency', 'Priority', 'Chronic', 'Normal'), t.created_at ASC
                     LIMIT ?";
        }
        
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
        if ($this->is_superadmin) {
            $sql = "SELECT d.id, d.name_en as dept_name, d.max_capacity as capacity, d.current_load,
                     ROUND((d.current_load / d.max_capacity) * 100, 2) as load_percentage
                     FROM departments d
                     ORDER BY load_percentage DESC";
        } else {
            $sql = "SELECT d.id, d.name_en as dept_name, d.max_capacity as capacity, d.current_load,
                     ROUND((d.current_load / d.max_capacity) * 100, 2) as load_percentage
                     FROM hospital_departments hd
                     INNER JOIN departments d ON hd.department_id = d.id
                     WHERE hd.hospital_id = " . (int)$this->hospital_id . "
                     ORDER BY load_percentage DESC";
        }
        
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
        if ($this->is_superadmin) {
            $sql = "SELECT t.id, t.token_number, d.name_en as dept_name, u.full_name as name, u.phone_number as phone, t.created_at
                     FROM tokens t
                     LEFT JOIN departments d ON t.department_id = d.id
                     LEFT JOIN users u ON t.user_id = u.id
                     WHERE t.status = 'Missed'
                     ORDER BY t.created_at DESC
                     LIMIT ?";
        } else {
            $sql = "SELECT t.id, t.token_number, d.name_en as dept_name, u.full_name as name, u.phone_number as phone, t.created_at
                     FROM tokens t
                     LEFT JOIN departments d ON t.department_id = d.id
                     LEFT JOIN users u ON t.user_id = u.id
                     WHERE t.hospital_id = " . (int)$this->hospital_id . " AND t.status = 'Missed'
                     ORDER BY t.created_at DESC
                     LIMIT ?";
        }
        
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
        if ($this->is_superadmin) {
            $sql = "SELECT t.id, t.token_number, d.name_en as dept_name, u.full_name as name, u.phone_number as phone,
                     TIMESTAMPDIFF(MINUTE, t.created_at, NOW()) as wait_time
                     FROM tokens t
                     LEFT JOIN departments d ON t.department_id = d.id
                     LEFT JOIN users u ON t.user_id = u.id
                     WHERE t.priority = 'Emergency' AND t.status = 'Active'
                     ORDER BY t.created_at ASC
                     LIMIT 10";
        } else {
            $sql = "SELECT t.id, t.token_number, d.name_en as dept_name, u.full_name as name, u.phone_number as phone,
                     TIMESTAMPDIFF(MINUTE, t.created_at, NOW()) as wait_time
                     FROM tokens t
                     LEFT JOIN departments d ON t.department_id = d.id
                     LEFT JOIN users u ON t.user_id = u.id
                     WHERE t.hospital_id = " . (int)$this->hospital_id . " AND t.priority = 'Emergency' AND t.status = 'Active'
                     ORDER BY t.created_at ASC
                     LIMIT 10";
        }
        
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
        $filter = $this->is_superadmin ? '' : ($this->hospital_id ? " AND d.hospital_id = " . (int)$this->hospital_id : "");
        
        $sql = "SELECT 
                 SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) as completed,
                 SUM(CASE WHEN t.status = 'Missed' THEN 1 ELSE 0 END) as missed,
                 SUM(CASE WHEN t.status = 'Active' THEN 1 ELSE 0 END) as active,
                 SUM(CASE WHEN t.status = 'Called' THEN 1 ELSE 0 END) as called,
                 COUNT(*) as total
                 FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 WHERE DATE(t.created_at) = ?" . $filter;
        
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
        $filter = $this->is_superadmin ? '' : ($this->hospital_id ? " AND d.hospital_id = " . (int)$this->hospital_id : "");
        
        $sql = "SELECT t.priority, COUNT(*) as count FROM tokens t
                 JOIN departments d ON t.department_id = d.id
                 WHERE DATE(t.created_at) = ?" . $filter . "
                 GROUP BY t.priority";
        
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
