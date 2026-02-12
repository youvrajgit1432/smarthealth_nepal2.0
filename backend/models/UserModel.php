<?php
/**
 * User Model
 * Handles all user-related database operations
 */

class UserModel {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    /**
     * Get user by phone number
     */
    public function getUserByPhone($phoneNumber) {
        $phoneNumber = $this->db->real_escape_string($phoneNumber);
        $result = $this->db->query("SELECT * FROM users WHERE phone_number = '$phoneNumber'");
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Create new user
     */
    public function create($userData) {
        try {
            $phoneNumber = $this->db->real_escape_string($userData['phone_number'] ?? '');
            $fullName = $this->db->real_escape_string($userData['full_name'] ?? 'User');
            
            $query = "INSERT INTO users (phone_number, full_name) VALUES ('$phoneNumber', '$fullName')";
            
            if ($this->db->query($query)) {
                return $this->db->insert_id;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("User Creation Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Register or get user by phone number
     */
    public function findOrCreateByPhone($phoneNumber) {
        $phoneNumber = $this->db->real_escape_string($phoneNumber);
        
        // Check if user exists
        $result = $this->db->query("SELECT * FROM users WHERE phone_number = '$phoneNumber'");
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        // Create new user
        $query = "INSERT INTO users (phone_number, created_at) 
                  VALUES ('$phoneNumber', NOW())";
        
        if ($this->db->query($query)) {
            return [
                'id' => $this->db->insert_id,
                'phone_number' => $phoneNumber,
                'full_name' => NULL,
                'age' => NULL,
                'gender' => NULL,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $userId = (int)$userId;
        $result = $this->db->query("SELECT * FROM users WHERE id = $userId");
        return $result->fetch_assoc();
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        $userId = (int)$userId;
        $fullName = $this->db->real_escape_string($data['full_name'] ?? '');
        $age = (int)($data['age'] ?? 0);
        $gender = $this->db->real_escape_string($data['gender'] ?? '');
        $email = $this->db->real_escape_string($data['email'] ?? '');
        
        $query = "UPDATE users SET 
                  full_name = '$fullName',
                  age = $age,
                  gender = '$gender',
                  email = '$email',
                  updated_at = NOW()
                  WHERE id = $userId";
        
        return $this->db->query($query);
    }
    
    /**
     * Mark user as pregnant
     */
    public function markPregnant($userId) {
        $userId = (int)$userId;
        $query = "UPDATE users SET is_pregnant = TRUE, updated_at = NOW() WHERE id = $userId";
        return $this->db->query($query);
    }
    
    /**
     * Add chronic disease to user
     */
    public function addChronicDisease($userId, $diseases) {
        $userId = (int)$userId;
        $diseasesJson = json_encode($diseases);
        $diseasesJson = $this->db->real_escape_string($diseasesJson);
        
        $query = "UPDATE users SET chronic_diseases = '$diseasesJson', updated_at = NOW() 
                  WHERE id = $userId";
        
        return $this->db->query($query);
    }
    
    /**
     * Get all users with active tokens
     */
    public function getActiveUsers() {
        $query = "SELECT DISTINCT u.* FROM users u
                  JOIN tokens t ON u.id = t.user_id
                  WHERE t.status = 'Active' AND DATE(t.created_at) = CURDATE()
                  ORDER BY u.created_at DESC";
        
        $result = $this->db->query($query);
        $users = [];
        
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    /**
     * Check if user has active chronic disease
     */
    public function hasActiveChronic($userId) {
        $userId = (int)$userId;
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM chronic_diseases 
             WHERE user_id = $userId AND status = 'Active'"
        );
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    /**
     * Check if user is pregnant
     */
    public function isPregnant($userId) {
        $user = $this->getUserById($userId);
        return $user['is_pregnant'] ?? false;
    }
}

?>
