<?php
/**
 * Chronic Disease Model
 */

class ChronicDiseaseModel {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    /**
     * Add chronic disease for user
     */
    public function addDisease($userId, $diseaseName, $diseaseCode, $diagnosisDate) {
        $userId = (int)$userId;
        $diseaseName = $this->db->real_escape_string($diseaseName);
        $diseaseCode = $this->db->real_escape_string($diseaseCode);
        
        // Calculate next followup (30 days from now)
        $nextFollowup = date('Y-m-d', strtotime('+30 days'));
        
        $query = "INSERT INTO chronic_diseases (user_id, disease_name, disease_code, diagnosis_date, next_followup_date, status)
                  VALUES ($userId, '$diseaseName', '$diseaseCode', '$diagnosisDate', '$nextFollowup', 'Active')";
        
        return $this->db->query($query);
    }
    
    /**
     * Get user's active chronic diseases
     */
    public function getUserDiseases($userId) {
        $userId = (int)$userId;
        
        $query = "SELECT * FROM chronic_diseases 
                  WHERE user_id = $userId AND status = 'Active'
                  ORDER BY next_followup_date ASC";
        
        $result = $this->db->query($query);
        $diseases = [];
        
        while ($row = $result->fetch_assoc()) {
            $diseases[] = $row;
        }
        
        return $diseases;
    }
    
    /**
     * UpdateFollowup date and last visit
     */
    public function updateFollowup($diseaseId, $doctorNotes = '') {
        $diseaseId = (int)$diseaseId;
        $doctorNotes = $this->db->real_escape_string($doctorNotes);
        $nextFollowup = date('Y-m-d', strtotime('+30 days'));
        
        $query = "UPDATE chronic_diseases SET 
                  last_visit_date = CURDATE(),
                  next_followup_date = '$nextFollowup',
                  doctor_notes = '$doctorNotes',
                  updated_at = NOW()
                  WHERE id = $diseaseId";
        
        return $this->db->query($query);
    }
    
    /**
     * Get overdue followups
     */
    public function getOverdueFollowups($userId) {
        $userId = (int)$userId;
        
        $query = "SELECT * FROM chronic_diseases 
                  WHERE user_id = $userId 
                  AND status = 'Active'
                  AND next_followup_date < CURDATE()
                  ORDER BY next_followup_date ASC";
        
        $result = $this->db->query($query);
        $diseases = [];
        
        while ($row = $result->fetch_assoc()) {
            $diseases[] = $row;
        }
        
        return $diseases;
    }
}

?>
