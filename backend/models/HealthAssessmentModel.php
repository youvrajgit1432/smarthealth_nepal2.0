<?php
/**
 * Health Assessment Model
 * Handles storing and retrieving user health assessment/triage data
 */

class HealthAssessmentModel {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    /**
     * Create health assessment (from booking triage data)
     */
    public function createAssessment($userId, $tokenId, $assessmentData) {
        $userId = (int)$userId;
        $tokenId = (int)($tokenId ?? 0);
        
        // Prepare data
        $has_fever = isset($assessmentData['have_fever']) ? 1 : 0;
        $fever_days = (int)($assessmentData['fever_days'] ?? 0);
        $difficulty_breathing = isset($assessmentData['difficulty_breathing']) ? 1 : 0;
        $has_injury = isset($assessmentData['any_injury']) ? 1 : 0;
        $injury_severity = $this->db->real_escape_string($assessmentData['injury_severity'] ?? '');
        $is_pregnant = isset($assessmentData['are_pregnant']) ? 1 : 0;
        $has_chronic = isset($assessmentData['chronic_disease']) ? 1 : 0;
        $chronic_types = !empty($assessmentData['chronic_disease_names']) 
            ? json_encode($assessmentData['chronic_disease_names']) 
            : NULL;
        $emergency_signs = isset($assessmentData['emergency_signs']) ? 1 : 0;
        $additional_notes = $this->db->real_escape_string($assessmentData['additional_notes'] ?? '');
        
        // Location data
        $district = $this->db->real_escape_string($assessmentData['district'] ?? '');
        $municipality = $this->db->real_escape_string($assessmentData['municipality'] ?? '');
        $ward = $this->db->real_escape_string($assessmentData['ward'] ?? '');
        $department_id = (int)($assessmentData['department_id'] ?? 0);
        $hospital_id = (int)($assessmentData['hospital_id'] ?? 0);
        
        $query = "INSERT INTO health_assessments (
            user_id,
            token_id,
            has_fever,
            fever_days,
            difficulty_breathing,
            has_injury,
            injury_severity,
            is_pregnant,
            has_chronic_disease,
            chronic_disease_types,
            has_emergency_signs,
            additional_notes,
            assessment_district,
            assessment_municipality,
            assessment_ward,
            department_id,
            hospital_id,
            status
        ) VALUES (
            $userId,
            " . ($tokenId > 0 ? $tokenId : "NULL") . ",
            $has_fever,
            $fever_days,
            $difficulty_breathing,
            $has_injury,
            " . (!empty($injury_severity) ? "'$injury_severity'" : "NULL") . ",
            $is_pregnant,
            $has_chronic,
            " . (!is_null($chronic_types) ? "'$chronic_types'" : "NULL") . ",
            $emergency_signs,
            '$additional_notes',
            '$district',
            '$municipality',
            '$ward',
            " . ($department_id > 0 ? $department_id : "NULL") . ",
            " . ($hospital_id > 0 ? $hospital_id : "NULL") . ",
            'Active'
        )";
        
        if ($this->db->query($query)) {
            return $this->db->insert_id;
        }
        
        error_log("Health Assessment Creation Error: " . $this->db->error);
        return false;
    }
    
    /**
     * Get assessment by ID
     */
    public function getAssessmentById($assessmentId) {
        $assessmentId = (int)$assessmentId;
        $result = $this->db->query("
            SELECT * FROM health_assessments 
            WHERE id = $assessmentId
        ");
        
        return $result ? $result->fetch_assoc() : null;
    }
    
    /**
     * Get user's assessments
     */
    public function getUserAssessments($userId, $limit = 10) {
        $userId = (int)$userId;
        $limit = (int)$limit;
        
        $result = $this->db->query("
            SELECT 
                ha.*,
                d.name_en as department_name,
                hl.hospital_name,
                t.token_number,
                t.status as token_status
            FROM health_assessments ha
            LEFT JOIN departments d ON ha.department_id = d.id
            LEFT JOIN hospital_locations hl ON ha.hospital_id = hl.id
            LEFT JOIN tokens t ON ha.token_id = t.id
            WHERE ha.user_id = $userId
            ORDER BY ha.assessment_date DESC
            LIMIT $limit
        ");
        
        $assessments = [];
        while ($row = $result->fetch_assoc()) {
            // Parse JSON if needed
            if ($row['chronic_disease_types']) {
                $row['chronic_disease_types'] = json_decode($row['chronic_disease_types'], true);
            }
            $assessments[] = $row;
        }
        
        return $assessments;
    }
    
    /**
     * Get recent assessment (latest)
     */
    public function getLatestAssessment($userId) {
        $userId = (int)$userId;
        $result = $this->db->query("
            SELECT * FROM health_assessments 
            WHERE user_id = $userId
            ORDER BY assessment_date DESC
            LIMIT 1
        ");
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['chronic_disease_types']) {
                $row['chronic_disease_types'] = json_decode($row['chronic_disease_types'], true);
            }
            return $row;
        }
        
        return null;
    }
    
    /**
     * Get assessment summary for dashboard
     */
    public function getAssessmentSummary($userId) {
        $userId = (int)$userId;
        
        $result = $this->db->query("
            SELECT 
                COUNT(*) as total_assessments,
                SUM(has_chronic_disease) as chronic_count,
                SUM(is_pregnant) as pregnancy_count,
                SUM(has_emergency_signs) as emergency_count,
                MAX(assessment_date) as last_assessment
            FROM health_assessments
            WHERE user_id = $userId
        ");
        
        return $result ? $result->fetch_assoc() : null;
    }
    
    /**
     * Update assessment status
     */
    public function updateStatus($assessmentId, $status) {
        $assessmentId = (int)$assessmentId;
        $status = $this->db->real_escape_string($status);
        
        return $this->db->query("
            UPDATE health_assessments 
            SET status = '$status'
            WHERE id = $assessmentId
        ");
    }
}

?>
