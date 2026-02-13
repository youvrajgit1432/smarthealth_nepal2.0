<?php
/**
 * Token Model
 * Handles all token/queue-related database operations
 */

class TokenModel {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    /**
     * Create new token for user with hospital tracking
     * Token number format: hospital_id + YYYYMMDD + serial_number
     * Example: Hospital 12 on 2026-02-12 with serial 3 = 1220260212003
     */
    public function createToken($userId, $departmentId, $priority, $triageData, $hospitalId = null, $locationData = null) {
        $userId = (int)$userId;
        $departmentId = (int)$departmentId;
        $priority = $this->db->real_escape_string($priority);
        $triageJson = json_encode($triageData);
        $triageJson = $this->db->real_escape_string($triageJson);
        
        // If no hospital ID provided, use a default (e.g., 1)
        if (!$hospitalId) {
            $hospitalId = 1;
        }
        $hospitalId = (int)$hospitalId;
        
        // Generate token number with format: hospital_id + YYYYMMDD + serial
        $date = date('Ymd');  // YYYYMMDD format
        
        // Find max serial number for this hospital on this date
        $result = $this->db->query(
            "SELECT COUNT(*) as serial FROM tokens 
             WHERE hospital_id = $hospitalId AND DATE(created_at) = CURDATE()"
        );
        $row = $result->fetch_assoc();
        $serialNumber = ($row['serial'] ?? 0) + 1;
        
        // Combine: hospital_id + YYYYMMDD + serial number (padded with zeros)
        // Example: 12 + 20260212 + 003 = 1220260212003
        $tokenNumber = (int)("{$hospitalId}{$date}" . str_pad($serialNumber, 3, '0', STR_PAD_LEFT));
        
        // Calculate estimated wait time - simplified: queue position * 10 minutes
        // This ensures realistic wait time based on actual queue (3 people ahead = ~30 min)
        $waitTimeResult = $this->db->query(
            "SELECT COUNT(*) as queue_count
             FROM tokens t
             WHERE t.department_id = $departmentId 
             AND t.status = 'Active'
             AND DATE(t.created_at) = CURDATE()"
        );
        $waitData = $waitTimeResult->fetch_assoc();
        // Each person ahead takes ~10 minutes (realistic estimate)
        $estimatedWait = ($waitData['queue_count'] ?? 0) * 10;
        
        // Extract location data if provided
        $userDistrict = null;
        $userMunicipality = null;
        $userWard = null;
        
        if ($locationData && is_array($locationData)) {
            $userDistrict = $this->db->real_escape_string($locationData['district'] ?? '');
            $userMunicipality = $this->db->real_escape_string($locationData['municipality'] ?? '');
            $userWard = $this->db->real_escape_string($locationData['ward'] ?? '');
        }
        
        // Determine if emergency
        $isEmergency = $priority === 'Emergency' ? 1 : 0;
        $isChronicFollowup = $priority === 'Chronic' ? 1 : 0;
        
        $query = "INSERT INTO tokens (
                    user_id, department_id, hospital_id, token_number, priority, triage_reason,
                    user_district, user_municipality, user_ward,
                    status, estimated_wait_time, is_emergency, is_chronic_followup,
                    created_at
                  ) VALUES (
                    $userId, $departmentId, $hospitalId, $tokenNumber, '$priority', '$triageJson',
                    '$userDistrict', '$userMunicipality', '$userWard',
                    'Active', $estimatedWait, $isEmergency, $isChronicFollowup,
                    NOW()
                  )";
        
        if ($this->db->query($query)) {
            return [
                'id' => $this->db->insert_id,
                'token_number' => $tokenNumber,
                'priority' => $priority,
                'hospital_id' => $hospitalId,
                'estimated_wait_time' => $estimatedWait,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return false;
    }
    
    /**
     * Get active tokens for department
     */
    public function getActiveDepartmentTokens($departmentId) {
        $departmentId = (int)$departmentId;
        
        $query = "SELECT t.*, u.full_name, u.phone_number
                  FROM tokens t
                  JOIN users u ON t.user_id = u.id
                  WHERE t.department_id = $departmentId 
                  AND t.status IN ('Active', 'Called')
                  AND DATE(t.created_at) = CURDATE()
                  ORDER BY  
                    CASE 
                      WHEN t.priority = 'Emergency' THEN 1
                      WHEN t.priority = 'Priority' THEN 2
                      WHEN t.priority = 'Normal' THEN 3
                      ELSE 4
                    END,
                    t.created_at ASC";
        
        $result = $this->db->query($query);
        $tokens = [];
        
        while ($row = $result->fetch_assoc()) {
            $tokens[] = $row;
        }
        
        return $tokens;
    }
    
    /**
     * Get token by ID
     */
    public function getTokenById($tokenId) {
        $tokenId = (int)$tokenId;
        $result = $this->db->query(
            "SELECT t.*, u.phone_number, d.name_en as department_name
             FROM tokens t
             JOIN users u ON t.user_id = u.id
             JOIN departments d ON t.department_id = d.id
             WHERE t.id = $tokenId"
        );
        return $result->fetch_assoc();
    }
    
    /**
     * Get token by token number and date
     */
    public function getTokenByNumber($tokenNumber, $departmentId = null) {
        $tokenNumber = (int)$tokenNumber;
        $query = "SELECT t.*, u.phone_number, d.name_en as department_name
                  FROM tokens t
                  JOIN users u ON t.user_id = u.id
                  JOIN departments d ON t.department_id = d.id
                  WHERE t.token_number = $tokenNumber AND DATE(t.created_at) = CURDATE()";
        
        if ($departmentId) {
            $departmentId = (int)$departmentId;
            $query .= " AND t.department_id = $departmentId";
        }
        
        $result = $this->db->query($query);
        return $result->fetch_assoc();
    }
    
    /**
     * Get user's active tokens - looks for tokens from today, or most recent valid token
     */
    public function getUserActiveTokens($userId) {
        $userId = (int)$userId;
        
        // First try: Look for today's active/called tokens
        $query = "SELECT t.*, d.name_en as department_name, d.name_ne as department_name_ne
                  FROM tokens t
                  JOIN departments d ON t.department_id = d.id
                  WHERE t.user_id = $userId 
                  AND t.status IN ('Active', 'Called', 'Pending')
                  AND DATE(t.created_at) = CURDATE()
                  ORDER BY t.created_at DESC
                  LIMIT 1";
        
        $result = $this->db->query($query);
        
        // If found today's token, return it
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        // Fallback: Look for any recent valid token (last 7 days) that hasn't been completed/missed/cancelled
        $query = "SELECT t.*, d.name_en as department_name, d.name_ne as department_name_ne
                  FROM tokens t
                  JOIN departments d ON t.department_id = d.id
                  WHERE t.user_id = $userId 
                  AND t.status NOT IN ('Completed', 'Missed', 'Cancelled', 'Rescheduled')
                  AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  ORDER BY t.created_at DESC
                  LIMIT 1";
        
        $result = $this->db->query($query);
        return $result ? $result->fetch_assoc() : null;
    }
    
    /**
     * Update token status
     */
    public function updateTokenStatus($tokenId, $status) {
        $tokenId = (int)$tokenId;
        $status = $this->db->real_escape_string($status);
        
        $updateFields = "status = '$status'";
        
        if ($status === 'Called') {
            $updateFields .= ", called_at = NOW()";
        } elseif ($status === 'Completed') {
            $updateFields .= ", completed_at = NOW()";
        }
        
        $query = "UPDATE tokens SET $updateFields WHERE id = $tokenId";
        return $this->db->query($query);
    }
    
    /**
     * Mark token as missed
     */
    public function markMissed($tokenId) {
        return $this->updateTokenStatus($tokenId, 'Missed');
    }
    
    /**
     * Reschedule token
     */
    public function rescheduleToken($tokenId) {
        $tokenId = (int)$tokenId;
        
        $token = $this->getTokenById($tokenId);
        if (!$token) return false;
        
        // Mark old token as rescheduled
        $this->db->query("UPDATE tokens SET status = 'Rescheduled' WHERE id = $tokenId");
        
        // Create new token with same hospital and location data
        $triageData = json_decode($token['triage_reason'], true);
        $locationData = [
            'district' => $token['user_district'],
            'municipality' => $token['user_municipality'],
            'ward' => $token['user_ward']
        ];
        return $this->createToken($token['user_id'], $token['department_id'], $token['priority'], $triageData, $token['hospital_id'], $locationData);
    }
    
    /**
     * Get queue position for user - handles tokens from today or recent days
     */
    public function getQueuePosition($userId, $departmentId) {
        $userId = (int)$userId;
        $departmentId = (int)$departmentId;
        
        // First try: Get today's token
        $userToken = $this->db->query(
            "SELECT id, priority, created_at FROM tokens 
             WHERE user_id = $userId AND department_id = $departmentId 
             AND status IN ('Active', 'Called', 'Pending') 
             AND DATE(created_at) = CURDATE() LIMIT 1"
        )->fetch_assoc();
        
        // Fallback: Get any recent valid token (last 7 days)
        if (!$userToken) {
            $userToken = $this->db->query(
                "SELECT id, priority, created_at FROM tokens 
                 WHERE user_id = $userId AND department_id = $departmentId 
                 AND status NOT IN ('Completed', 'Missed', 'Cancelled', 'Rescheduled')
                 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 ORDER BY created_at DESC LIMIT 1"
            )->fetch_assoc();
        }
        
        if (!$userToken) return 0; // Return 0 if no token found
        
        // Count people ahead of user (any valid status tokens in same department)
        $result = $this->db->query(
            "SELECT COUNT(*) as ahead FROM tokens t
             WHERE t.department_id = $departmentId 
             AND t.status NOT IN ('Completed', 'Missed', 'Cancelled', 'Rescheduled')
             AND (t.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY))
             AND (
               CASE 
                 WHEN t.priority = 'Emergency' THEN 1
                 WHEN t.priority = 'Priority' THEN 2
                 WHEN t.priority = 'Normal' THEN 3
                 ELSE 4
               END < CASE 
                 WHEN '{$userToken['priority']}' = 'Emergency' THEN 1
                 WHEN '{$userToken['priority']}' = 'Priority' THEN 2
                 WHEN '{$userToken['priority']}' = 'Normal' THEN 3
                 ELSE 4
               END
               OR (CASE 
                 WHEN t.priority = 'Emergency' THEN 1
                 WHEN t.priority = 'Priority' THEN 2
                 WHEN t.priority = 'Normal' THEN 3
                 ELSE 4
               END = CASE 
                 WHEN '{$userToken['priority']}' = 'Emergency' THEN 1
                 WHEN '{$userToken['priority']}' = 'Priority' THEN 2
                 WHEN '{$userToken['priority']}' = 'Normal' THEN 3
                 ELSE 4
               END AND t.created_at < '{$userToken['created_at']}')
             )"
        );
        
        $data = $result->fetch_assoc();
        return $data['ahead'] ?? 0;
    }
    
    /**
     * Get department load indicator - counts valid tokens from recent days
     */
    public function getDepartmentLoad($departmentId) {
        $departmentId = (int)$departmentId;
        
        // Count active/valid tokens from today or recent days
        $result = $this->db->query(
            "SELECT COUNT(*) as active_count, d.max_capacity
             FROM tokens t
             JOIN departments d ON t.department_id = d.id
             WHERE t.department_id = $departmentId 
             AND t.status NOT IN ('Completed', 'Missed', 'Cancelled', 'Rescheduled')
             AND (t.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 DAY))
             GROUP BY d.id"
        );
        
        $row = $result->fetch_assoc();
        if (!$row) return ['load' => 'Low', 'percentage' => 0, 'active_count' => 0, 'max_capacity' => 0];
        
        $percentage = ($row['active_count'] / $row['max_capacity']) * 100;
        
        if ($percentage >= 80) {
            $load = 'High';
        } elseif ($percentage >= 50) {
            $load = 'Moderate';
        } else {
            $load = 'Low';
        }
        
        return [
            'load' => $load,
            'percentage' => round($percentage, 2),
            'active_count' => $row['active_count'],
            'max_capacity' => $row['max_capacity']
        ];
    }
}

?>
