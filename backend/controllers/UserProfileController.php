<?php
/**
 * User Profile Controller
 * Handles user profile management, health history, and tracking
 */

class UserProfileController {
    private $db;
    private $userModel;
    private $healthAssessmentModel;
    
    public function __construct($connection) {
        $this->db = $connection;
        require_once __DIR__ . '/../models/UserModel.php';
        require_once __DIR__ . '/../models/HealthAssessmentModel.php';
        
        $this->userModel = new UserModel($connection);
        $this->healthAssessmentModel = new HealthAssessmentModel($connection);
    }
    
    /**
     * Get complete user profile with health data
     */
    public function getUserProfile($userId) {
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Get health assessments
        $assessments = $this->healthAssessmentModel->getUserAssessments($userId, 5);
        $summary = $this->healthAssessmentModel->getAssessmentSummary($userId);
        
        // Get chronic diseases
        $chronics = $this->getUserChronicDiseases($userId);
        
        // Get booking history
        $bookingHistory = $this->getBookingHistory($userId, 5);
        
        return [
            'success' => true,
            'user' => $user,
            'assessments' => $assessments,
            'summary' => $summary,
            'chronic_diseases' => $chronics,
            'booking_history' => $bookingHistory
        ];
    }
    
    /**
     * Update user profile information
     */
    public function updateProfile($userId, $profileData) {
        $userId = (int)$userId;
        
        $updates = [];
        
        if (isset($profileData['full_name'])) {
            $fullName = $this->db->real_escape_string($profileData['full_name']);
            $updates[] = "full_name = '$fullName'";
        }
        
        if (isset($profileData['age'])) {
            $age = (int)$profileData['age'];
            $updates[] = "age = $age";
        }
        
        if (isset($profileData['gender'])) {
            $gender = $this->db->real_escape_string($profileData['gender']);
            $updates[] = "gender = '$gender'";
        }
        
        if (isset($profileData['email'])) {
            $email = $this->db->real_escape_string($profileData['email']);
            $updates[] = "email = '$email'";
        }
        
        if (isset($profileData['blood_type'])) {
            $bloodType = $this->db->real_escape_string($profileData['blood_type']);
            $updates[] = "blood_type = '$bloodType'";
        }
        
        if (isset($profileData['allergies'])) {
            $allergies = $this->db->real_escape_string($profileData['allergies']);
            $updates[] = "allergies = '$allergies'";
        }
        
        if (isset($profileData['emergency_contact'])) {
            $contact = $this->db->real_escape_string($profileData['emergency_contact']);
            $updates[] = "emergency_contact = '$contact'";
        }
        
        if (isset($profileData['emergency_contact_name'])) {
            $name = $this->db->real_escape_string($profileData['emergency_contact_name']);
            $updates[] = "emergency_contact_name = '$name'";
        }
        
        if (isset($profileData['district'])) {
            $district = $this->db->real_escape_string($profileData['district']);
            $updates[] = "district = '$district'";
        }
        
        if (isset($profileData['municipality'])) {
            $municipality = $this->db->real_escape_string($profileData['municipality']);
            $updates[] = "municipality = '$municipality'";
        }
        
        if (isset($profileData['ward'])) {
            $ward = $this->db->real_escape_string($profileData['ward']);
            $updates[] = "ward = '$ward'";
        }
        
        if (empty($updates)) {
            return [
                'success' => false,
                'message' => 'No data to update'
            ];
        }
        
        $query = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = $userId";
        
        if ($this->db->query($query)) {
            return [
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $this->userModel->getUserById($userId)
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update profile: ' . $this->db->error
        ];
    }
    
    /**
     * Get user's chronic diseases
     */
    public function getUserChronicDiseases($userId) {
        $userId = (int)$userId;
        
        $result = $this->db->query("
            SELECT * FROM chronic_diseases 
            WHERE user_id = $userId AND status = 'Active'
            ORDER BY next_followup_date ASC
        ");
        
        $diseases = [];
        while ($row = $result->fetch_assoc()) {
            $diseases[] = $row;
        }
        
        return $diseases;
    }
    
    /**
     * Get user's booking history
     */
    public function getBookingHistory($userId, $limit = 10) {
        $userId = (int)$userId;
        $limit = (int)$limit;
        
        $result = $this->db->query("
            SELECT 
                bh.*,
                d.name_en as department_name,
                d.name_ne as department_name_ne,
                hl.hospital_name,
                t.token_number,
                t.priority
            FROM booking_history bh
            LEFT JOIN departments d ON bh.department_id = d.id
            LEFT JOIN hospital_locations hl ON bh.hospital_id = hl.id
            LEFT JOIN tokens t ON bh.token_id = t.id
            WHERE bh.user_id = $userId
            ORDER BY bh.booking_date DESC
            LIMIT $limit
        ");
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        return $history;
    }
    
    /**
     * Add booking history entry
     */
    public function addBookingHistory($userId, $tokenId, $departmentId, $hospitalId = null) {
        $userId = (int)$userId;
        $tokenId = (int)($tokenId ?? 0);
        $departmentId = (int)$departmentId;
        $hospitalId = (int)($hospitalId ?? 0);
        
        $query = "INSERT INTO booking_history (
            user_id, token_id, department_id, hospital_id, status
        ) VALUES (
            $userId,
            " . ($tokenId > 0 ? $tokenId : "NULL") . ",
            $departmentId,
            " . ($hospitalId > 0 ? $hospitalId : "NULL") . ",
            'Pending'
        )";
        
        if ($this->db->query($query)) {
            return [
                'success' => true,
                'booking_history_id' => $this->db->insert_id
            ];
        }
        
        return [
            'success' => false,
            'error' => $this->db->error
        ];
    }
    
    /**
     * Get health statistics for dashboard
     */
    public function getHealthStatistics($userId) {
        $userId = (int)$userId;
        
        // Total assessments
        $assessments = $this->db->query("
            SELECT COUNT(*) as count FROM health_assessments 
            WHERE user_id = $userId
        ")->fetch_assoc()['count'];
        
        // Chronic conditions
        $chronic = $this->db->query("
            SELECT COUNT(*) as count FROM chronic_diseases 
            WHERE user_id = $userId AND status = 'Active'
        ")->fetch_assoc()['count'];
        
        // Total bookings
        $bookings = $this->db->query("
            SELECT COUNT(*) as count FROM booking_history 
            WHERE user_id = $userId
        ")->fetch_assoc()['count'];
        
        // Overdue follow-ups
        $overdue = $this->db->query("
            SELECT COUNT(*) as count FROM chronic_diseases 
            WHERE user_id = $userId 
            AND status = 'Active'
            AND next_followup_date < CURDATE()
        ")->fetch_assoc()['count'];
        
        return [
            'total_assessments' => $assessments,
            'active_conditions' => $chronic,
            'total_bookings' => $bookings,
            'overdue_followups' => $overdue
        ];
    }
    
    /**
     * Get avatar initials and color
     */
    public function getAvatar($user) {
        $initials = '';
        if (!empty($user['full_name'])) {
            $parts = explode(' ', $user['full_name']);
            $initials = strtoupper(substr($parts[0], 0, 1));
            if (isset($parts[1])) {
                $initials .= strtoupper(substr($parts[1], 0, 1));
            }
        } else {
            $initials = strtoupper(substr($user['phone_number'], -2));
        }
        
        $colors = ['primary', 'success', 'info', 'warning', 'danger'];
        $colorIndex = (int)$user['id'] % count($colors);
        $color = $colors[$colorIndex];
        
        return [
            'initials' => $initials,
            'color' => $color
        ];
    }
}

?>
