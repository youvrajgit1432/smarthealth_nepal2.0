<?php
/**
 * Appointment Slot Helper
 * Handles all appointment slot operations
 */

class AppointmentSlotHelper {
    private $db;
    
    public function __construct($connection) {
        $this->db = $connection;
    }
    
    /**
     * Populate appointment slots for departments and hospitals (next 30 days)
     */
    public function populateSlotsForNextDays($hospitalId, $departmentId, $days = 30) {
        $slots_created = 0;
        
        for ($i = 0; $i < $days; $i++) {
            $slot_date = date('Y-m-d', strtotime("+$i days"));
            
            // Skip Saturdays
            if (date('l', strtotime($slot_date)) === 'Saturday') {
                continue;
            }
            
            // Early Morning slot (8:00 - 12:00)
            $this->createOrUpdateSlot(
                $hospitalId,
                $departmentId,
                $slot_date,
                '08:00',
                '12:00',
                'Early Morning',
                15
            );
            $slots_created++;
            
            // Afternoon slot (14:00 - 17:00)
            $this->createOrUpdateSlot(
                $hospitalId,
                $departmentId,
                $slot_date,
                '14:00',
                '17:00',
                'Afternoon',
                12
            );
            $slots_created++;
        }
        
        return $slots_created;
    }
    
    /**
     * Create or update a single appointment slot
     */
    private function createOrUpdateSlot($hospitalId, $departmentId, $slotDate, $startTime, $endTime, $slotType, $maxCapacity) {
        $query = "INSERT INTO appointment_slots 
                  (hospital_id, department_id, slot_date, time_window_start, time_window_end, slot_type, max_capacity, booked_count, is_active)
                  VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1)
                  ON DUPLICATE KEY UPDATE 
                    slot_type = VALUES(slot_type),
                    max_capacity = VALUES(max_capacity),
                    is_active = VALUES(is_active)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('iissssi', $hospitalId, $departmentId, $slotDate, $startTime, $endTime, $slotType, $maxCapacity);
        
        return $stmt->execute();
    }
    
    /**
     * Get available dates for a department and hospital
     */
    public function getAvailableDates($hospitalId, $departmentId, $startDate = null, $limit = 30) {
        if (!$startDate) {
            $startDate = date('Y-m-d');
        }
        
        $endDate = date('Y-m-d', strtotime("+$limit days"));
        
        $query = "SELECT DISTINCT 
                    slot_date,
                    DAYNAME(slot_date) as day_name,
                    DATE_FORMAT(slot_date, '%a, %b %d') as formatted_date,
                    COUNT(*) as total_slots,
                    SUM(max_capacity - booked_count) as available_spots
                  FROM appointment_slots
                  WHERE hospital_id = ? 
                    AND department_id = ?
                    AND slot_date BETWEEN ? AND ?
                    AND is_active = 1
                    AND booked_count < max_capacity
                  GROUP BY slot_date
                  ORDER BY slot_date ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('iiss', $hospitalId, $departmentId, $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $dates = [];
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row;
        }
        
        return $dates;
    }
    
    /**
     * Get available time slots for a specific date
     */
    public function getAvailableSlots($hospitalId, $departmentId, $slotDate) {
        $query = "SELECT 
                    id,
                    slot_type,
                    time_window_start,
                    time_window_end,
                    max_capacity,
                    booked_count,
                    (max_capacity - booked_count) as available_spots,
                    CASE 
                        WHEN booked_count >= max_capacity THEN 0
                        ELSE 1
                    END as is_available
                  FROM appointment_slots
                  WHERE hospital_id = ?
                    AND department_id = ?
                    AND slot_date = ?
                    AND is_active = 1
                  ORDER BY time_window_start ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('iis', $hospitalId, $departmentId, $slotDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $slots = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['available_spots'] > 0) {
                $slots[] = $row;
            }
        }
        
        return $slots;
    }
    
    /**
     * Book an appointment slot
     */
    public function bookSlot($appointmentSlotId, $tokenId) {
        $query = "UPDATE appointment_slots 
                  SET booked_count = booked_count + 1
                  WHERE id = ? AND booked_count < max_capacity";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $appointmentSlotId);
        
        if ($stmt->execute()) {
            // Update token with slot details
            $slotQuery = "SELECT * FROM appointment_slots WHERE id = ?";
            $slotStmt = $this->db->prepare($slotQuery);
            $slotStmt->bind_param('i', $appointmentSlotId);
            $slotStmt->execute();
            $slotResult = $slotStmt->get_result();
            $slot = $slotResult->fetch_assoc();
            
            if ($slot) {
                $updateTokenQuery = "UPDATE tokens 
                                   SET appointment_date = ?, 
                                       appointment_slot_id = ?,
                                       time_window_start = ?,
                                       time_window_end = ?
                                   WHERE id = ?";
                
                $updateStmt = $this->db->prepare($updateTokenQuery);
                $updateStmt->bind_param('sisis', 
                    $slot['slot_date'],
                    $appointmentSlotId,
                    $slot['time_window_start'],
                    $slot['time_window_end'],
                    $tokenId
                );
                
                return $updateStmt->execute();
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get slot details
     */
    public function getSlotDetails($slotId) {
        $query = "SELECT * FROM appointment_slots WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $slotId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Check if slot has available capacity
     */
    public function hasAvailableCapacity($slotId) {
        $slot = $this->getSlotDetails($slotId);
        
        if (!$slot) {
            return false;
        }
        
        return $slot['booked_count'] < $slot['max_capacity'];
    }
}
?>
