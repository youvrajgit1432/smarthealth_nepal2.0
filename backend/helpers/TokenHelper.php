<?php
/**
 * Token Helper
 * Handles token-related business logic
 */

class TokenHelper {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    /**
     * Classify triage priority
     */
    public function classifyTriagePriority($triageData) {
        $priority = 'Normal';
        
        // Check emergency signs
        if (isset($triageData['emergency_signs']) && $triageData['emergency_signs']) {
            $priority = 'Emergency';
        }
        // Check difficulty breathing or fever with complications
        elseif ((isset($triageData['difficulty_breathing']) && $triageData['difficulty_breathing']) ||
                (isset($triageData['have_fever']) && $triageData['have_fever'] && isset($triageData['fever_days']) && $triageData['fever_days'] > 5)) {
            $priority = 'Priority';
        }
        // Check chronic disease
        elseif (isset($triageData['chronic_disease']) && $triageData['chronic_disease']) {
            $priority = 'Chronic';
        }
        // Check injury
        elseif (isset($triageData['any_injury']) && $triageData['any_injury']) {
            if (isset($triageData['injury_severity']) && in_array($triageData['injury_severity'], ['Severe', 'Critical'])) {
                $priority = 'Emergency';
            } else {
                $priority = 'Priority';
            }
        }
        // Check pregnancy
        elseif (isset($triageData['are_pregnant']) && $triageData['are_pregnant']) {
            $priority = 'Priority';
        }
        
        return $priority;
    }
    
    /**
     * Generate token message
     */
    public function generateTokenMessage($token, $department, $language = 'en') {
        $messages = [
            'en' => [
                'header' => "Your SmartHealth Nepal Token Confirmed!",
                'token' => "Token: {$token['token_number']}",
                'department' => "Department: {$department['name_en']}",
                'priority' => "Priority: {$token['priority']}",
                'wait' => "Estimated Wait: {$token['estimated_wait_time']} min",
                'footer' => "Keep this SMS for reference. Arrive before your estimated time. Call hospital for queries."
            ],
            'ne' => [
                'header' => "आपनो स्मार्टहेल्थ नेपाल टोकन पुष्टि भएको!",
                'token' => "टोकन: {$token['token_number']}",
                'department' => "विभाग: {$department['name_ne']}",
                'priority' => "प्राथमिकता: {$token['priority']}",
                'wait' => "अनुमानित प्रतीक्षा: {$token['estimated_wait_time']} मिनेट",
                'footer' => "यो SMS सन्दर्भको लागि राख्नुहोस्। अनुमानित समय अघि अस्पताल आनुहोस्।"
            ]
        ];
        
        $msgs = $messages[$language] ?? $messages['en'];
        
        return "{$msgs['header']}\n" .
               "{$msgs['token']}\n" .
               "{$msgs['department']}\n" .
               "{$msgs['priority']}\n" .
               "{$msgs['wait']}\n" .
               "{$msgs['footer']}";
    }
    
    /**
     * Generate reminder message for chronic disease
     */
    public function generateChronicReminder($disease, $language = 'en') {
        $messages = [
            'en' => "Reminder: Your {$disease} follow-up is due. Please book an appointment at your nearest hospital.",
            'ne' => "स्मरणीय: आपनो {$disease} अनुवर्ती कारण छ। कृपया आपनो नजिकको अस्पतालमा अपोइन्टमेन्ट बुक गर्नुहोस्।"
        ];
        
        return $messages[$language] ?? $messages['en'];
    }
    
    /**
     * Generate maternal notification
     */
    public function generateMaternalNotification($type, $language = 'en') {
        $messages = [
            'en' => [
                'antenatal' => "Reminder: Your antenatal checkup is due. Schedule an appointment for safe pregnancy.",
                'vaccination' => "Your baby needs vaccination. Please visit your nearest health post.",
                'due_soon' => "Your due date is approaching. Ensure final checkup and prepare for delivery."
            ],
            'ne' => [
                'antenatal' => "स्मरणीय: आपनो प्रसवपूर्व जाँच कारण छ। सुरक्षित गर्भावस्थाको लागि अपोइन्टमेन्ट तय गर्नुहोस्।",
                'vaccination' => "आपनो बच्चालाई टीकाकरण आवश्यक छ। आपनो नजिकको स्वास्थ्य चौकीमा भेट गर्नुहोस्।",
                'due_soon' => "आपनो कारण तारिख वर्दै छ। अन्तिम जाँच सुनिश्चित गर्नुहोस् र प्रसवको लागि तयार हुनुहोस्।"
            ]
        ];
        
        $msgs = $messages[$language] ?? $messages['en'];
        return $msgs[$type] ?? $msgs['antenatal'];
    }
}

?>
