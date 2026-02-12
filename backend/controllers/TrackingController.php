<?php
/**
 * Tracking Controller - For chronic and maternal health
 */

require_once __DIR__ . '/../../backend/init.php';

class TrackingController {
    private $db;
    private $chronModel;
    private $userModel;
    private $notifModel;
    
    public function __construct($connection) {
        $this->db = $connection;
        require_once __DIR__ . '/../models/HealthRecordModel.php';
        require_once __DIR__ . '/../models/UserModel.php';
        require_once __DIR__ . '/../models/NotificationModel.php';
        
        $this->chronModel = new HealthRecordModel($connection);
        $this->userModel = new UserModel($connection);
        $this->notifModel = new NotificationModel($connection);
    }
    
    /**
     * Get user's chronic diseases
     */
    public function getChronicDiseases($userId) {
        $diseases = $this->chronModel->getUserDiseases($userId);
        
        return [
            'success' => true,
            'diseases' => $diseases,
            'count' => count($diseases)
        ];
    }
    
    /**
     * Add chronic disease
     */
    public function addChronicDisease($userId, $diseaseName, $diseaseCode, $diagnosisDate) {
        $result = $this->chronModel->addDisease($userId, $diseaseName, $diseaseCode, $diagnosisDate);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to add chronic disease'
            ];
        }
        
        // Mark user as having chronic disease
        $this->userModel->addChronicDisease($userId, [$diseaseName]);
        
        return [
            'success' => true,
            'message' => 'Chronic disease added successfully'
        ];
    }
    
    /**
     * Update chronic disease follow-up
     */
    public function updateFollowup($diseaseId, $doctorNotes = '') {
        $result = $this->chronModel->updateFollowup($diseaseId, $doctorNotes);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to update follow-up'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Follow-up updated successfully'
        ];
    }
    
    /**
     * Get overdue follow-ups
     */
    public function getOverdueFollowups($userId) {
        $overdue = $this->chronModel->getOverdueFollowups($userId);
        
        return [
            'success' => true,
            'overdue_count' => count($overdue),
            'followups' => $overdue
        ];
    }
    
    /**
     * Create chronic follow-up reminder
     */
    public function createChronicReminder($userId, $disease) {
        global $lang;
        
        require_once __DIR__ . '/../helpers/TokenHelper.php';
        $tokenHelper = new TokenHelper($this->db);
        
        $messageEn = $tokenHelper->generateChronicReminder($disease, 'en');
        $messageNe = $tokenHelper->generateChronicReminder($disease, 'ne');
        
        // Create notification
        $this->notifModel->create($userId, 'Chronic', $messageEn, $messageNe, true);
        
        return [
            'success' => true,
            'message' => 'Reminder created and will be sent via SMS'
        ];
    }
}

?>
