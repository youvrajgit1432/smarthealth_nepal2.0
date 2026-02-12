<?php
/**
 * Admin Service Management Controller
 * Handles service approval, forwarding, and referral management
 */

class ServiceController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all services
     */
    public function getAllServices($status = null) {
        $sql = "SELECT s.id, s.dept_id, s.service_name, s.description, s.status, d.dept_name
                 FROM services s
                 JOIN departments d ON s.dept_id = d.id";
        
        if ($status) {
            $sql .= " WHERE s.status = '" . $this->db->real_escape_string($status) . "'";
        }
        
        $sql .= " ORDER BY s.id DESC";
        
        $result = $this->db->query($sql);
        
        $services = [];
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        
        return $services;
    }
    
    /**
     * Get service by ID
     */
    public function getServiceById($service_id) {
        $sql = "SELECT s.*, d.dept_name FROM services s
                 JOIN departments d ON s.dept_id = d.id
                 WHERE s.id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Service not found'];
        }
        
        $service = $result->fetch_assoc();
        $stmt->close();
        
        return ['success' => true, 'service' => $service];
    }
    
    /**
     * Create new service
     */
    public function createService($dept_id, $service_name, $description = '') {
        if (empty($dept_id) || empty($service_name)) {
            return ['success' => false, 'message' => 'Department and service name are required'];
        }
        
        $status = 'Active';
        
        $sql = "INSERT INTO services (dept_id, service_name, description, status)
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("isss", $dept_id, $service_name, $description, $status);
        
        if ($stmt->execute()) {
            $service_id = $this->db->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Service created successfully', 'service_id' => $service_id];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to create service'];
    }
    
    /**
     * Update service
     */
    public function updateService($service_id, $data) {
        if (empty($service_id)) {
            return ['success' => false, 'message' => 'Service ID is required'];
        }
        
        $updates = [];
        $params = [];
        $types = '';
        
        if (isset($data['service_name'])) {
            $updates[] = 'service_name = ?';
            $params[] = $data['service_name'];
            $types .= 's';
        }
        
        if (isset($data['description'])) {
            $updates[] = 'description = ?';
            $params[] = $data['description'];
            $types .= 's';
        }
        
        if (isset($data['status'])) {
            $updates[] = 'status = ?';
            $params[] = $data['status'];
            $types .= 's';
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }
        
        $params[] = $service_id;
        $types .= 'i';
        
        $sql = "UPDATE services SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Service updated successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to update service'];
    }
    
    /**
     * Delete service
     */
    public function deleteService($service_id) {
        if (empty($service_id)) {
            return ['success' => false, 'message' => 'Service ID is required'];
        }
        
        $sql = "DELETE FROM services WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("i", $service_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Service deleted successfully'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to delete service'];
    }
    
    /**
     * Get referral requests pending
     */
    public function getPendingReferrals() {
        $sql = "SELECT r.id, r.user_id, r.from_hospital, r.to_hospital, r.reason, r.status, r.created_at,
                 u.name, u.phone
                 FROM referrals r
                 JOIN users u ON r.user_id = u.id
                 WHERE r.status = 'Pending'
                 ORDER BY r.created_at DESC";
        
        $result = $this->db->query($sql);
        
        $referrals = [];
        while ($row = $result->fetch_assoc()) {
            $referrals[] = $row;
        }
        
        return $referrals;
    }
    
    /**
     * Get all referrals
     */
    public function getAllReferrals($status = null) {
        $sql = "SELECT r.id, r.user_id, r.from_hospital, r.to_hospital, r.reason, r.status, r.created_at,
                 u.name, u.phone
                 FROM referrals r
                 JOIN users u ON r.user_id = u.id";
        
        if ($status) {
            $sql .= " WHERE r.status = '" . $this->db->real_escape_string($status) . "'";
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        $result = $this->db->query($sql);
        
        $referrals = [];
        while ($row = $result->fetch_assoc()) {
            $referrals[] = $row;
        }
        
        return $referrals;
    }
    
    /**
     * Approve referral
     */
    public function approveReferral($referral_id) {
        if (empty($referral_id)) {
            return ['success' => false, 'message' => 'Referral ID is required'];
        }
        
        $status = 'Approved';
        
        $sql = "UPDATE referrals SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("si", $status, $referral_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Referral approved'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to approve referral'];
    }
    
    /**
     * Reject referral
     */
    public function rejectReferral($referral_id, $reason = '') {
        if (empty($referral_id)) {
            return ['success' => false, 'message' => 'Referral ID is required'];
        }
        
        $status = 'Rejected';
        
        $sql = "UPDATE referrals SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("si", $status, $referral_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Referral rejected'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to reject referral'];
    }
    
    /**
     * Forward referral to another hospital
     */
    public function forwardReferral($referral_id, $to_hospital) {
        if (empty($referral_id) || empty($to_hospital)) {
            return ['success' => false, 'message' => 'Referral ID and hospital are required'];
        }
        
        $status = 'Forwarded';
        
        $sql = "UPDATE referrals SET to_hospital = ?, status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return ['success' => false, 'message' => 'Database error'];
        }
        
        $stmt->bind_param("ssi", $to_hospital, $status, $referral_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Referral forwarded'];
        }
        
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to forward referral'];
    }
    
    /**
     * Get services by department
     */
    public function getServicesByDepartment($dept_id) {
        $sql = "SELECT id, service_name, description, status FROM services WHERE dept_id = ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $dept_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $services = [];
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        
        $stmt->close();
        return $services;
    }
    
    /**
     * Get service statistics
     */
    public function getServiceStats() {
        $stats = [];
        
        // Total services
        $sql = "SELECT COUNT(*) as count FROM services";
        $result = $this->db->query($sql);
        $stats['total_services'] = $result->fetch_assoc()['count'];
        
        // Active services
        $sql = "SELECT COUNT(*) as count FROM services WHERE status = 'Active'";
        $result = $this->db->query($sql);
        $stats['active_services'] = $result->fetch_assoc()['count'];
        
        // Pending referrals
        $sql = "SELECT COUNT(*) as count FROM referrals WHERE status = 'Pending'";
        $result = $this->db->query($sql);
        $stats['pending_referrals'] = $result->fetch_assoc()['count'];
        
        // Total referrals
        $sql = "SELECT COUNT(*) as count FROM referrals";
        $result = $this->db->query($sql);
        $stats['total_referrals'] = $result->fetch_assoc()['count'];
        
        return $stats;
    }
}
?>
