<?php
/**
 * Hospital Admin Authentication
 */

namespace App\Admin;

use PDO;

class HospitalAuthController
{
    private $db;
    private $table = 'admins';

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Handle hospital admin login
     */
    public function login($username, $password)
    {
        try {
            // Get admin by username
            $query = "SELECT * FROM {$this->table} WHERE username = :username AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':username' => $username]);
            
            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password',
                    'code' => 401
                ];
            }

            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if (!password_verify($password, $admin['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password',
                    'code' => 401
                ];
            }

            // Check authorization
            if ($admin['role'] === 'SuperAdmin') {
                // Super admin can access all hospitals
                $admin['hospital_id'] = null;
                $admin['access_type'] = 'super';
            } elseif ($admin['is_hospital_admin'] && $admin['hospital_id']) {
                // Hospital admin
                $admin['access_type'] = 'hospital';
            } else {
                return [
                    'success' => false,
                    'message' => 'You do not have access to admin panel',
                    'code' => 403
                ];
            }

            // Update last login
            $updateQuery = "UPDATE {$this->table} SET last_login = NOW() WHERE id = :id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->execute([':id' => $admin['id']]);

            return [
                'success' => true,
                'message' => 'Login successful',
                'admin' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'full_name' => $admin['full_name'],
                    'email' => $admin['email'],
                    'role' => $admin['role'],
                    'hospital_id' => $admin['hospital_id'],
                    'department_id' => $admin['department_id'],
                    'access_type' => $admin['access_type']
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Check if admin has access to specific hospital
     */
    public function hasHospitalAccess($adminId, $hospitalId = null)
    {
        try {
            $query = "SELECT role, hospital_id FROM {$this->table} WHERE id = :id AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $adminId]);
            
            if ($stmt->rowCount() === 0) {
                return false;
            }

            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // SuperAdmin has access to all
            if ($admin['role'] === 'SuperAdmin') {
                return true;
            }

            // Hospital admin can only access their hospital
            if ($admin['hospital_id'] && $admin['hospital_id'] == $hospitalId) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get admin hospital assignments
     */
    public function getAdminHospitals($adminId)
    {
        try {
            $query = "SELECT DISTINCT h.* FROM hospital_locations h
                      INNER JOIN admins a ON a.hospital_id = h.id
                      WHERE a.id = :admin_id AND h.is_active = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':admin_id' => $adminId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Create hospital admin
     */
    public function createHospitalAdmin($data)
    {
        try {
            if (empty($data['username']) || empty($data['full_name']) || empty($data['hospital_id']) || empty($data['password'])) {
                return [
                    'success' => false,
                    'message' => 'Missing required fields'
                ];
            }

            // Check if username exists
            $checkQuery = "SELECT id FROM {$this->table} WHERE username = :username";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute([':username' => $data['username']]);

            if ($checkStmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'Username already exists'
                ];
            }

            // Verify hospital exists
            $hospitalQuery = "SELECT id FROM hospital_locations WHERE id = :hospital_id AND is_active = 1";
            $hospitalStmt = $this->db->prepare($hospitalQuery);
            $hospitalStmt->execute([':hospital_id' => $data['hospital_id']]);

            if ($hospitalStmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Hospital not found'
                ];
            }

            $query = "INSERT INTO {$this->table} 
                      (username, password_hash, full_name, email, role, hospital_id, department_id, is_hospital_admin, is_active, created_at)
                      VALUES (:username, :password_hash, :full_name, :email, :role, :hospital_id, :department_id, :is_hospital_admin, 1, NOW())";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':username' => $data['username'],
                ':password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
                ':full_name' => $data['full_name'],
                ':email' => $data['email'] ?? null,
                ':role' => $data['role'] ?? 'Admin',
                ':hospital_id' => $data['hospital_id'],
                ':department_id' => $data['department_id'] ?? null,
                ':is_hospital_admin' => 1
            ]);

            return [
                'success' => true,
                'message' => 'Hospital admin created successfully',
                'admin_id' => $this->db->lastInsertId()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating admin: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify session and return admin info
     */
    public function verifySession()
    {
        if (!isset($_SESSION['admin_id'])) {
            return [
                'authenticated' => false,
                'message' => 'Not authenticated'
            ];
        }

        try {
            $query = "SELECT id, username, full_name, email, role, hospital_id, department_id, is_hospital_admin 
                      FROM {$this->table} WHERE id = :id AND is_active = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $_SESSION['admin_id']]);
            
            if ($stmt->rowCount() === 0) {
                return [
                    'authenticated' => false,
                    'message' => 'Admin not found'
                ];
            }

            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'authenticated' => true,
                'admin' => $admin
            ];
        } catch (\Exception $e) {
            return [
                'authenticated' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }
}
?>
