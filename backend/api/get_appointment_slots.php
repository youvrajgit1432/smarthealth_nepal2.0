<?php
/**
 * API Endpoint: Get Available Appointment Slots
 * Used to fetch available dates and time slots for booking
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../helpers/AppointmentSlotHelper.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$slotHelper = new AppointmentSlotHelper($db);
$response = ['success' => false, 'data' => null, 'message' => ''];

try {
    if ($action === 'get_dates') {
        // Get available dates for department/hospital
        $hospitalId = isset($_GET['hospital_id']) ? (int)$_GET['hospital_id'] : null;
        $departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
        
        if (!$hospitalId || !$departmentId) {
            throw new Exception('Hospital ID and Department ID are required');
        }
        
        // Populate slots if they don't exist
        $slotHelper->populateSlotsForNextDays($hospitalId, $departmentId, 30);
        
        // Get available dates
        $dates = $slotHelper->getAvailableDates($hospitalId, $departmentId);
        
        if (empty($dates)) {
            $response = [
                'success' => false,
                'message' => 'No available dates for this department',
                'data' => []
            ];
        } else {
            $response = [
                'success' => true,
                'message' => 'Available dates retrieved',
                'data' => $dates
            ];
        }
    }
    elseif ($action === 'get_slots') {
        // Get available time slots for a specific date
        $hospitalId = isset($_GET['hospital_id']) ? (int)$_GET['hospital_id'] : null;
        $departmentId = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
        $slotDate = isset($_GET['slot_date']) ? $_GET['slot_date'] : null;
        
        if (!$hospitalId || !$departmentId || !$slotDate) {
            throw new Exception('Hospital ID, Department ID, and Slot Date are required');
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $slotDate)) {
            throw new Exception('Invalid date format');
        }
        
        $slots = $slotHelper->getAvailableSlots($hospitalId, $departmentId, $slotDate);
        
        if (empty($slots)) {
            $response = [
                'success' => false,
                'message' => 'No available slots for this date',
                'data' => []
            ];
        } else {
            $response = [
                'success' => true,
                'message' => 'Available slots retrieved',
                'data' => $slots
            ];
        }
    }
    else {
        throw new Exception('Invalid action');
    }
}
catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ];
}

echo json_encode($response);
?>
