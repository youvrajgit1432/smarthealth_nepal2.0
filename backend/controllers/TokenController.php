<?php
/**
 * Token Controller
 */

require_once __DIR__ . '/../../backend/init.php';

class TokenController {
    private $db;
    private $tokenModel;
    private $userModel;
    private $deptModel;
    private $tokenHelper;
    private $smsHelper;
    
    public function __construct($connection) {
        $this->db = $connection;
        require_once __DIR__ . '/../models/TokenModel.php';
        require_once __DIR__ . '/../models/UserModel.php';
        require_once __DIR__ . '/../models/DepartmentModel.php';
        require_once __DIR__ . '/../helpers/TokenHelper.php';
        require_once __DIR__ . '/../helpers/SMSHelper.php';
        
        $this->tokenModel = new TokenModel($connection);
        $this->userModel = new UserModel($connection);
        $this->deptModel = new DepartmentModel($connection);
        $this->tokenHelper = new TokenHelper($connection);
        $this->smsHelper = new SMSHelper();
    }
    
    /**
     * Book new token
     */
    public function bookToken($userId, $departmentId, $triageData) {
        global $lang;
        
        // Check if department can accept more tokens
        if (!$this->deptModel->canBookToken($departmentId)) {
            return [
                'success' => false,
                'message' => 'Department is at full capacity. Please try again later.'
            ];
        }
        
        // Classify priority based on triage
        $priority = $this->tokenHelper->classifyTriagePriority($triageData);
        
        // Create token
        $token = $this->tokenModel->createToken($userId, $departmentId, $priority, $triageData);
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to create token'
            ];
        }
        
        // Get user details
        $user = $this->userModel->getUserById($userId);
        $department = $this->deptModel->getById($departmentId);
        
        // Generate SMS message
        $message = $this->tokenHelper->generateTokenMessage($token, $department, $_SESSION['language'] ?? 'en');
        
        // Send SMS
        if ($user['phone_number']) {
            $this->smsHelper->send($user['phone_number'], $message);
        }
        
        return [
            'success' => true,
            'message' => 'Token booked successfully',
            'token' => $token,
            'department' => $department
        ];
    }
    
    /**
     * Get token status
     */
    public function getTokenStatus($userId) {
        $token = $this->tokenModel->getUserActiveTokens($userId);
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'No active token found',
                'token' => null
            ];
        }
        
        // Get queue position
        $queuePosition = $this->tokenModel->getQueuePosition($userId, $token['department_id']);
        
        // Get department load
        $load = $this->tokenModel->getDepartmentLoad($token['department_id']);
        
        return [
            'success' => true,
            'token' => $token,
            'queue_position' => $queuePosition,
            'department_load' => $load
        ];
    }
    
    /**
     * Track token by number
     */
    public function trackByNumber($tokenNumber, $departmentId = null) {
        $token = $this->tokenModel->getTokenByNumber($tokenNumber, $departmentId);
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Token not found',
                'token' => null
            ];
        }
        
        // Get queue position
        $queuePosition = $this->tokenModel->getQueuePosition($token['user_id'], $token['department_id']);
        
        // Get department load
        $load = $this->tokenModel->getDepartmentLoad($token['department_id']);
        
        return [
            'success' => true,
            'token' => $token,
            'queue_position' => $queuePosition,
            'department_load' => $load
        ];
    }
    
    /**
     * Get all departments with queue info
     */
    public function getDepartmentsList() {
        $departments = $this->deptModel->getAll();
        
        $result = [];
        foreach ($departments as $dept) {
            $status = $this->deptModel->getDepartmentStatus($dept['id']);
            $load = $this->deptModel->getLoadIndicator($dept['id']);
            
            $result[] = [
                'id' => $dept['id'],
                'name' => $dept['name_en'],
                'name_ne' => $dept['name_ne'],
                'description' => $dept['description_en'],
                'active_tokens' => $status['active_tokens'] ?? 0,
                'load' => $load,
                'can_book' => $this->deptModel->canBookToken($dept['id'])
            ];
        }
        
        return [
            'success' => true,
            'departments' => $result
        ];
    }
    
    /**
     * Reschedule token
     */
    public function rescheduleToken($tokenId) {
        global $lang;
        
        $newToken = $this->tokenModel->rescheduleToken($tokenId);
        
        if (!$newToken) {
            return [
                'success' => false,
                'message' => 'Failed to reschedule'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Token rescheduled successfully',
            'token' => $newToken
        ];
    }
}

?>
