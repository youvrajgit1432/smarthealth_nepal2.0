<?php
/**
 * Notification Model
 */

class NotificationModel {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    /**
     * Create notification
     */
    public function create($userId, $type, $messageEn, $messageNe, $isSms = false) {
        $userId = (int)$userId;
        $type = $this->db->real_escape_string($type);
        $messageEn = $this->db->real_escape_string($messageEn);
        $messageNe = $this->db->real_escape_string($messageNe);
        $isSms = $isSms ? 1 : 0;
        
        $query = "INSERT INTO notifications (user_id, type, message_en, message_ne, is_sms, delivery_status)
                  VALUES ($userId, '$type', '$messageEn', '$messageNe', $isSms, 'Pending')";
        
        return $this->db->query($query);
    }
    
    /**
     * Mark notification as sent
     */
    public function markSent($notificationId) {
        $notificationId = (int)$notificationId;
        
        $query = "UPDATE notifications SET 
                  delivery_status = 'Sent',
                  is_sent = TRUE,
                  sent_at = NOW()
                  WHERE id = $notificationId";
        
        return $this->db->query($query);
    }
    
    /**
     * Get pending SMS notifications
     */
    public function getPendingSMS() {
        $query = "SELECT n.*, u.phone_number 
                  FROM notifications n
                  JOIN users u ON n.user_id = u.id
                  WHERE n.is_sms = TRUE 
                  AND n.delivery_status = 'Pending'
                  ORDER BY n.created_at ASC
                  LIMIT 100";
        
        $result = $this->db->query($query);
        $notifications = [];
        
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        return $notifications;
    }
    
    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 20) {
        $userId = (int)$userId;
        $limit = (int)$limit;
        
        $query = "SELECT * FROM notifications 
                  WHERE user_id = $userId
                  ORDER BY created_at DESC
                  LIMIT $limit";
        
        $result = $this->db->query($query);
        $notifications = [];
        
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        return $notifications;
    }
}

?>
