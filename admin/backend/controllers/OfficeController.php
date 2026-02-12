<?php
/**
 * Admin Office/Department Management Controller
 * Handles department CRUD, capacity management, and staff allocation
 */

class OfficeController {
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
            
            if ($row['load_percentage'] >= 80) {
                $row['load_status'] = 'High';
                $row['load_color'] = 'danger';
            } elseif ($row['load_percentage'] >= 50) {
                $row['load_status'] = 'Moderate';
                $row['load_color'] = 'warning';
            } else {
                $row['load_status'] = 'Low';
                $row['load_color'] = 'success';
            }
            
            $departments[] = $row;
        }
        
        return $departments;
    }
    
    /**
     * Get department by ID
     */
    public function getDepartmentById($dept_id) {
        $sql = "SELECT id, hospital_id, dept_name, capacity, current_load, avg_service_time
                 FROM departments
                 WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $dept_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Department not found'];
        }
        
        $department = $result->fetch_assoc();
        $stmt->close();
        
        return ['success' => true, 'department' => $department];
    }
    
    /**
     * Create new department
     */
    public function createDepartment($dept_name, $capacity, $avg_service_time, $hospital_id = 1) {
        if (empty($dept_name) || empty($capacity)) {
            return ['success' => false, 'message' => 'Department name and capacity are required'];
        }
        
        $current_load = 0;
        
        $sql = "INSERT INTO departments (hospital_id, dept_name, capacity, current_load, avg_service_time)
                 VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("isiii", $hospital_id, $dept_name, $capacity, $current_load, $avg_service_time);
        
        if ($stmt->execute()) {
            $dept_id = $this->db->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Department created successfully', 'dept_id' => $dept_id];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to create department'];
    }
    
    /**
     * Update department
     */
    public function updateDepartment($dept_id, $data) {
        if (empty($dept_id)) {
            return ['success' => false, 'message' => 'Department ID is required'];
        }
        
        $updates = [];
        $params = [];
        $types = '';
        
        if (isset($data['dept_name'])) {
            $updates[] = 'dept_name = ?';
            $params[] = $data['dept_name'];
            $types .= 's';
        }
        
        if (isset($data['capacity'])) {
            $updates[] = 'capacity = ?';
            $params[] = $data['capacity'];
            $types .= 'i';
        }
        
        if (isset($data['avg_service_time'])) {
            $updates[] = 'avg_service_time = ?';
            $params[] = $data['avg_service_time'];
            $types .= 'i';
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }
        
        $params[] = $dept_id;
        $types .= 'i';
        
        $sql = "UPDATE departments SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Department updated successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to update department'];
    }
    
    /**
     * Delete department
     */
    public function deleteDepartment($dept_id) {
        if (empty($dept_id)) {
            return ['success' => false, 'message' => 'Department ID is required'];
        }
        
        $sql = "DELETE FROM departments WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $dept_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Department deleted successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to delete department'];
    }
    
    /**
     * Update department capacity/load
     */
    public function updateCapacity($dept_id, $new_load) {
        if (empty($dept_id) || $new_load < 0) {
            return ['success' => false, 'message' => 'Invalid department or load value'];
        }
        
        $sql = "UPDATE departments SET current_load = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ii", $new_load, $dept_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Capacity updated'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to update capacity'];
    }
    
    /**
     * Get department with load indicator
     */
    public function getDepartmentWithLoad($dept_id) {
        $sql = "SELECT id, dept_name, capacity, current_load, 
                 ROUND((current_load / capacity) * 100, 2) as load_percentage,
                 avg_service_time
                 FROM departments
                 WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false];
        }
        
        $stmt->bind_param("i", $dept_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Department not found'];
        }
        
        $dept = $result->fetch_assoc();
        $stmt->close();
        
        // Add status indicator
        $load_pct = $dept['load_percentage'];
        if ($load_pct >= 90) {
            $dept['status'] = 'Critical';
            $dept['color'] = 'danger';
        } elseif ($load_pct >= 75) {
            $dept['status'] = 'Very High';
            $dept['color'] = 'warning';
        } elseif ($load_pct >= 50) {
            $dept['status'] = 'Moderate';
            $dept['color'] = 'info';
        } else {
            $dept['status'] = 'Low';
            $dept['color'] = 'success';
        }
        
        return ['success' => true, 'department' => $dept];
    }
    
    /**
     * Get department statistics
     */
    public function getDepartmentStats() {
        $stats = [];
        
        // Total departments
        $sql = "SELECT COUNT(*) as count FROM departments";
        $result = $this->db->query($sql);
        $stats['total_departments'] = $result->fetch_assoc()['count'];
        
        // Total capacity
        $sql = "SELECT SUM(capacity) as total FROM departments";
        $result = $this->db->query($sql);
        $stats['total_capacity'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Current load
        $sql = "SELECT SUM(current_load) as total FROM departments";
        $result = $this->db->query($sql);
        $stats['total_load'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Overloaded departments
        $sql = "SELECT COUNT(*) as count FROM departments WHERE current_load >= capacity";
        $result = $this->db->query($sql);
        $stats['overloaded_depts'] = $result->fetch_assoc()['count'];
        
        // Average utilization
        if ($stats['total_capacity'] > 0) {
            $stats['utilization_percent'] = round(($stats['total_load'] / $stats['total_capacity']) * 100, 2);
        } else {
            $stats['utilization_percent'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * Get department queue length
     */
    public function getDepartmentQueueLength($dept_id) {
        $sql = "SELECT COUNT(*) as queue_length FROM tokens 
                 WHERE department_id = ? AND status IN ('Active', 'Called')";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return 0;
        }
        
        $stmt->bind_param("i", $dept_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['queue_length'] ?? 0;
    }
    
    /**
     * Get all departments with queue info
     */
    public function getDepartmentsWithQueues() {
        $departments = $this->getAllDepartments();
        
        foreach ($departments as &$dept) {
            $dept['queue_length'] = $this->getDepartmentQueueLength($dept['id']);
        }
        
        return $departments;
    }
}
?>
