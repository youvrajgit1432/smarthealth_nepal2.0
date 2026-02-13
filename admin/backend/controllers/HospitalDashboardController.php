<?php
/**
 * Hospital Admin Dashboard Controller
 */

namespace App\Admin;

use PDO;

class HospitalDashboardController
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Get hospital dashboard overview
     */
    public function getHospitalOverview($hospitalId)
    {
        try {
            // Get hospital info
            $hospitalQuery = "SELECT * FROM hospital_locations WHERE id = :id";
            $hospitalStmt = $this->db->prepare($hospitalQuery);
            $hospitalStmt->execute([':id' => $hospitalId]);
            $hospital = $hospitalStmt->fetch(PDO::FETCH_ASSOC);

            // Get today's tokens
            $tokensQuery = "SELECT 
                            COUNT(*) as total_tokens,
                            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN status = 'Running' THEN 1 ELSE 0 END) as running
                            FROM tokens 
                            WHERE hospital_id = :hospital_id AND DATE(created_at) = CURDATE()";
            $tokensStmt = $this->db->prepare($tokensQuery);
            $tokensStmt->execute([':hospital_id' => $hospitalId]);
            $tokens = $tokensStmt->fetch(PDO::FETCH_ASSOC);

            // Get departments
            $deptQuery = "SELECT d.id, d.name_en, d.name_ne, hd.max_tokens_per_day, hd.current_daily_tokens, hd.available
                          FROM hospital_departments hd
                          JOIN departments d ON hd.department_id = d.id
                          WHERE hd.hospital_id = :hospital_id AND hd.is_active = 1";
            $deptStmt = $this->db->prepare($deptQuery);
            $deptStmt->execute([':hospital_id' => $hospitalId]);
            $departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get assisted bookings
            $assistedQuery = "SELECT COUNT(*) as total, 
                             SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                             SUM(CASE WHEN status = 'Assigned' THEN 1 ELSE 0 END) as assigned
                             FROM assisted_bookings 
                             WHERE hospital_id = :hospital_id AND booking_date >= CURDATE()";
            $assistedStmt = $this->db->prepare($assistedQuery);
            $assistedStmt->execute([':hospital_id' => $hospitalId]);
            $assisted = $assistedStmt->fetch(PDO::FETCH_ASSOC);

            // Get staff count
            $staffQuery = "SELECT COUNT(*) as total FROM hospital_staff WHERE hospital_id = :hospital_id AND is_active = 1";
            $staffStmt = $this->db->prepare($staffQuery);
            $staffStmt->execute([':hospital_id' => $hospitalId]);
            $staff = $staffStmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'hospital' => $hospital,
                'tokens_today' => $tokens,
                'departments' => $departments,
                'assisted_bookings' => $assisted,
                'staff_count' => $staff['total'] ?? 0
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all tokens for hospital
     */
    public function getTokens($hospitalId, $filters = [])
    {
        try {
            $query = "SELECT t.*, d.name_en as department, u.full_name as patient_name, u.phone_number
                      FROM tokens t
                      JOIN departments d ON t.department_id = d.id
                      LEFT JOIN users u ON t.user_id = u.id
                      WHERE t.hospital_id = :hospital_id";

            $params = [':hospital_id' => $hospitalId];

            if (!empty($filters['status'])) {
                $query .= " AND t.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['department_id'])) {
                $query .= " AND t.department_id = :department_id";
                $params[':department_id'] = $filters['department_id'];
            }

            if (!empty($filters['date'])) {
                $query .= " AND DATE(t.created_at) = :date";
                $params[':date'] = $filters['date'];
            }

            $query .= " ORDER BY t.created_at DESC LIMIT " . ($filters['limit'] ?? 100);

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            return [
                'success' => true,
                'tokens' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get assisted bookings
     */
    public function getAssistedBookings($hospitalId, $date = null)
    {
        try {
            $query = "SELECT ab.*, d.name_en as department, a.full_name as registered_by_name
                      FROM assisted_bookings ab
                      JOIN departments d ON ab.department_id = d.id
                      LEFT JOIN admins a ON ab.registered_by = a.id
                      WHERE ab.hospital_id = :hospital_id";

            $params = [':hospital_id' => $hospitalId];

            if ($date) {
                $query .= " AND ab.booking_date = :date";
                $params[':date'] = $date;
            } else {
                $query .= " AND ab.booking_date >= CURDATE()";
            }

            $query .= " ORDER BY ab.booking_date, ab.booking_time";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            return [
                'success' => true,
                'bookings' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get hospital departments
     */
    public function getDepartments($hospitalId)
    {
        try {
            $query = "SELECT hd.*, d.name_en, d.name_ne, d.description_en, d.description_ne
                      FROM hospital_departments hd
                      JOIN departments d ON hd.department_id = d.id
                      WHERE hd.hospital_id = :hospital_id AND hd.is_active = 1";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':hospital_id' => $hospitalId]);

            return [
                'success' => true,
                'departments' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update department availability
     */
    public function updateDepartmentAvailability($hospitalId, $departmentId, $available, $maxTokens = null)
    {
        try {
            $query = "UPDATE hospital_departments SET available = :available";
            
            $params = [
                ':hospital_id' => $hospitalId,
                ':department_id' => $departmentId,
                ':available' => $available ? 1 : 0
            ];

            if ($maxTokens !== null) {
                $query .= ", max_tokens_per_day = :max_tokens";
                $params[':max_tokens'] = $maxTokens;
            }

            $query .= " WHERE hospital_id = :hospital_id AND department_id = :department_id";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Department updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get hospital staff
     */
    public function getStaff($hospitalId)
    {
        try {
            $query = "SELECT hs.*, d.name_en as department, a.username as admin_username
                      FROM hospital_staff hs
                      LEFT JOIN departments d ON hs.department_id = d.id
                      LEFT JOIN admins a ON hs.admin_id = a.id
                      WHERE hs.hospital_id = :hospital_id AND hs.is_active = 1
                      ORDER BY hs.position, hs.name";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':hospital_id' => $hospitalId]);

            return [
                'success' => true,
                'staff' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add staff member
     */
    public function addStaff($hospitalId, $data)
    {
        try {
            if (empty($data['name']) || empty($data['position'])) {
                return [
                    'success' => false,
                    'message' => 'Missing required fields'
                ];
            }

            $query = "INSERT INTO hospital_staff 
                      (hospital_id, name, position, department_id, email, phone, admin_id, status, is_active, created_at)
                      VALUES (:hospital_id, :name, :position, :department_id, :email, :phone, :admin_id, :status, 1, NOW())";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':hospital_id' => $hospitalId,
                ':name' => $data['name'],
                ':position' => $data['position'],
                ':department_id' => $data['department_id'] ?? null,
                ':email' => $data['email'] ?? null,
                ':phone' => $data['phone'] ?? null,
                ':admin_id' => $data['admin_id'] ?? null,
                ':status' => $data['status'] ?? 'Active'
            ]);

            return [
                'success' => true,
                'message' => 'Staff member added successfully',
                'staff_id' => $this->db->lastInsertId()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get hospital statistics
     */
    public function getStatistics($hospitalId, $startDate = null, $endDate = null)
    {
        try {
            if (!$startDate) {
                $startDate = date('Y-m-d', strtotime('-30 days'));
            }
            if (!$endDate) {
                $endDate = date('Y-m-d');
            }

            $query = "SELECT * FROM hospital_statistics 
                      WHERE hospital_id = :hospital_id AND date BETWEEN :start_date AND :end_date
                      ORDER BY date DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':hospital_id' => $hospitalId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);

            return [
                'success' => true,
                'statistics' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
?>
