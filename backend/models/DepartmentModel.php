<?php
/**
 * Department Model
 */

class DepartmentModel {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    /**
     * Get all active departments
     */
    public function getAll() {
        $query = "SELECT * FROM departments WHERE is_active = TRUE ORDER BY name_en ASC";
        $result = $this->db->query($query);
        $departments = [];
        
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        
        return $departments;
    }
    
    /**
     * Get department by ID
     */
    public function getById($departmentId) {
        $departmentId = (int)$departmentId;
        $result = $this->db->query("SELECT * FROM departments WHERE id = $departmentId");
        return $result->fetch_assoc();
    }
    
    /**
     * Get department load status
     */
    public function getDepartmentStatus($departmentId) {
        $departmentId = (int)$departmentId;
        
        $result = $this->db->query(
            "SELECT 
                d.*,
                COUNT(CASE WHEN t.status = 'Active' AND DATE(t.created_at) = CURDATE() THEN 1 END) as active_tokens,
                COUNT(CASE WHEN t.status = 'Called' AND DATE(t.created_at) = CURDATE() THEN 1 END) as called_tokens
             FROM departments d
             LEFT JOIN tokens t ON d.id = t.department_id
             WHERE d.id = $departmentId
             GROUP BY d.id"
        );
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get load indicator (Low/Moderate/High)
     */
    public function getLoadIndicator($departmentId) {
        $departmentId = (int)$departmentId;
        
        $result = $this->db->query(
            "SELECT 
                COUNT(CASE WHEN t.status = 'Active' AND DATE(t.created_at) = CURDATE() THEN 1 END) as active_count,
                d.max_capacity
             FROM departments d
             LEFT JOIN tokens t ON d.id = t.department_id
             WHERE d.id = $departmentId
             GROUP BY d.id"
        );
        
        $data = $result->fetch_assoc();
        if (!$data) return 'Low';
        
        $percentage = ($data['active_count'] / $data['max_capacity']) * 100;
        
        if ($percentage >= 80) return 'High';
        if ($percentage >= 50) return 'Moderate';
        return 'Low';
    }
    
    /**
     * Can book new token in department
     */
    public function canBookToken($departmentId) {
        $status = $this->getDepartmentStatus($departmentId);
        $active = $status['active_tokens'] ?? 0;
        $capacity = $status['max_capacity'] ?? 50;
        
        return $active < $capacity;
    }
    
    /**
     * Get next available emergency department
     */
    public function getNextEmergency() {
        $query = "SELECT d.* FROM departments d
                  WHERE d.is_active = TRUE AND d.name_en = 'Emergency'
                  LIMIT 1";
        
        $result = $this->db->query($query);
        return $result->fetch_assoc();
    }
}

?>
