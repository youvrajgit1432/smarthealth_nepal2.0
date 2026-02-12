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
     * Create new token for user
     */
    public function createToken($userId, $departmentId, $priority, $triageData) {
        $userId = (int)$userId;
        $departmentId = (int)$departmentId;
        $priority = $this->db->real_escape_string($priority);
        $triageJson = json_encode($triageData);
        $triageJson = $this->db->real_escape_string($triageJson);
        
        // Generate token number
        $date = date('Ymd');
        $result = $this->db->query(
            "SELECT MAX(token_number) as max_token FROM tokens 
             WHERE department_id = $departmentId AND DATE(created_at) = CURDATE()"
        );
        $row = $result->fetch_assoc();
        $tokenNumber = ($row['max_token'] ?? 0) + 1;
        
        // Calculate estimated wait time
        $waitTimeResult = $this->db->query(
            "SELECT COUNT(*) as queue_count, d.avg_service_time
             FROM tokens t
             JOIN departments d ON t.department_id = d.id
             WHERE t.department_id = $departmentId 
             AND t.status = 'Active'
             AND DATE(t.created_at) = CURDATE()"
        );
        $waitData = $waitTimeResult->fetch_assoc();
        $estimatedWait = ($waitData['queue_count'] ?? 0) * ($waitData['avg_service_time'] ?? 30);
        
        // Determine if emergency
        $isEmergency = $priority === 'Emergency' ? 1 : 0;
        $isChronicFollowup = $priority === 'Chronic' ? 1 : 0;
        
        $query = "INSERT INTO tokens (
                    user_id, department_id, token_number, priority, triage_reason,
                    status, estimated_wait_time, is_emergency, is_chronic_followup,
                    created_at
                  ) VALUES (
                    $userId, $departmentId, $tokenNumber, '$priority', '$triageJson',
                    'Active', $estimatedWait, $isEmergency, $isChronicFollowup,
                    NOW()
                  )";
        
        if ($this->db->query($query)) {
            return [
                'id' => $this->db->insert_id,
                'token_number' => $tokenNumber,
                'priority' => $priority,
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
     * Get user's active tokens
     */
    public function getUserActiveTokens($userId) {
        $userId = (int)$userId;
        
        $query = "SELECT t.*, d.name_en as department_name, d.name_ne as department_name_ne
                  FROM tokens t
                  JOIN departments d ON t.department_id = d.id
                  WHERE t.user_id = $userId 
                  AND t.status IN ('Active', 'Called')
                  AND DATE(t.created_at) = CURDATE()
                  ORDER BY t.created_at DESC
                  LIMIT 1";
        
        $result = $this->db->query($query);
        return $result->fetch_assoc();
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
        
        // Create new token
        $triageData = json_decode($token['triage_reason'], true);
        return $this->createToken($token['user_id'], $token['department_id'], $token['priority'], $triageData);
    }
    
    /**
     * Get queue position for user
     */
    public function getQueuePosition($userId, $departmentId) {
        $userId = (int)$userId;
        $departmentId = (int)$departmentId;
        
        // Get user's token
        $userToken = $this->db->query(
            "SELECT id, priority, created_at FROM tokens 
             WHERE user_id = $userId AND department_id = $departmentId 
             AND status = 'Active' AND DATE(created_at) = CURDATE() LIMIT 1"
        )->fetch_assoc();
        
        if (!$userToken) return null;
        
        // Count people ahead of user
        $result = $this->db->query(
            "SELECT COUNT(*) as ahead FROM tokens t
             WHERE t.department_id = $departmentId 
             AND t.status = 'Active'
             AND DATE(t.created_at) = CURDATE()
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
     * Get department load indicator
     */
    public function getDepartmentLoad($departmentId) {
        $departmentId = (int)$departmentId;
        
        $result = $this->db->query(
            "SELECT COUNT(*) as active_count, d.max_capacity
             FROM tokens t
             JOIN departments d ON t.department_id = d.id
             WHERE t.department_id = $departmentId 
             AND t.status = 'Active'
             AND DATE(t.created_at) = CURDATE()
             GROUP BY d.id"
        );
        
        $row = $result->fetch_assoc();
        if (!$row) return ['load' => 'Low', 'percentage' => 0];
        
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
