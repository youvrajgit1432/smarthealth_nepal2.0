<?php
/**
 * Admin Office/Department Model
 * Handles department-related data operations
 */

class OfficeModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all departments
     */
    public function getAllDepartments() {
        $sql = "SELECT id, hospital_id, dept_name, capacity, current_load, avg_service_time, created_at
                 FROM departments
                 ORDER BY dept_name ASC";
        
        $result = $this->db->query($sql);
        
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $row['load_percentage'] = round(($row['current_load'] / $row['capacity']) * 100, 2);
            $departments[] = $row;
        }
        
        return $departments;
    }
    
    /**
     * Get department by ID
     */
    public function getDepartmentById($dept_id) {
        $sql = "SELECT id, hospital_id, dept_name, capacity, current_load, avg_service_time, created_at
                 FROM departments
                 WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("i", $dept_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }
        
        $department = $result->fetch_assoc();
        $stmt->close();
        return $department;
    }
    
    /**
     * Create department
     */
    public function createDepartment($dept_name, $capacity, $avg_service_time, $hospital_id = 1) {
        $current_load = 0;
        
        $sql = "INSERT INTO departments (hospital_id, dept_name, capacity, current_load, avg_service_time)
                 VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("isiii", $hospital_id, $dept_name, $capacity, $current_load, $avg_service_time);
        
        if ($stmt->execute()) {
            $dept_id = $this->db->insert_id;
            $stmt->close();
            return ['success' => true, 'dept_id' => $dept_id];
        }
        
        $stmt->close();
        return ['success' => false];
    }
    
    /**
     * Update department
     */
    public function updateDepartment($dept_id, $dept_name = null, $capacity = null, $avg_service_time = null) {
        $updates = [];
        $params = [];
        $types = '';
        
        if ($dept_name !== null) {
            $updates[] = 'dept_name = ?';
            $params[] = $dept_name;
            $types .= 's';
        }
        
        if ($capacity !== null) {
            $updates[] = 'capacity = ?';
            $params[] = $capacity;
            $types .= 'i';
        }
        
        if ($avg_service_time !== null) {
            $updates[] = 'avg_service_time = ?';
            $params[] = $avg_service_time;
            $types .= 'i';
        }
        
        if (empty($updates)) {
            return ['success' => false];
        }
        
        $params[] = $dept_id;
        $types .= 'i';
        
        $sql = "UPDATE departments SET " . implode(', ', $updates) . " WHERE id = ?";
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
     * Delete department
     */
    public function deleteDepartment($dept_id) {
        $sql = "DELETE FROM departments WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("i", $dept_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        
        $stmt->close();
        return ['success' => false];
    }
    
    /**
     * Update department load
     */
    public function updateDepartmentLoad($dept_id, $current_load) {
        $sql = "UPDATE departments SET current_load = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("ii", $current_load, $dept_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true];
        }
        
        $stmt->close();
        return ['success' => false];
    }
    
    /**
     * Get department with queue info
     */
    public function getDepartmentWithQueue($dept_id) {
        $sql = "SELECT d.id, d.dept_name, d.capacity, d.current_load, d.avg_service_time,
                 COUNT(t.id) as queue_length
                 FROM departments d
                 LEFT JOIN tokens t ON d.id = t.department_id AND t.status IN ('Active', 'Called')
                 WHERE d.id = ?
                 GROUP BY d.id";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("i", $dept_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }
        
        $department = $result->fetch_assoc();
        $stmt->close();
        return $department;
    }
    
    /**
     * Get department count
     */
    public function getDepartmentCount() {
        $sql = "SELECT COUNT(*) as count FROM departments";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    
    /**
     * Get total capacity
     */
    public function getTotalCapacity() {
        $sql = "SELECT SUM(capacity) as total FROM departments";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    /**
     * Get total current load
     */
    public function getTotalCurrentLoad() {
        $sql = "SELECT SUM(current_load) as total FROM departments";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    /**
     * Get overloaded departments
     */
    public function getOverloadedDepartments() {
        $sql = "SELECT id, dept_name, capacity, current_load 
                 FROM departments 
                 WHERE current_load >= capacity
                 ORDER BY (current_load - capacity) DESC";
        
        $result = $this->db->query($sql);
        
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        
        return $departments;
    }
    
    /**
     * Get department stats
     */
    public function getDepartmentStats() {
        $stats = [];
        
        // Total departments
        $stats['total_depts'] = $this->getDepartmentCount();
        
        // Total capacity
        $stats['total_capacity'] = $this->getTotalCapacity();
        
        // Total load
        $stats['total_load'] = $this->getTotalCurrentLoad();
        
        // Utilization
        if ($stats['total_capacity'] > 0) {
            $stats['utilization'] = round(($stats['total_load'] / $stats['total_capacity']) * 100, 2);
        } else {
            $stats['utilization'] = 0;
        }
        
        // Overloaded count
        $stats['overloaded_count'] = count($this->getOverloadedDepartments());
        
        return $stats;
    }
}
?>
