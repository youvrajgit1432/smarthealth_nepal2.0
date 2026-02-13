<?php
/**
 * Hospital Management API
 * Handles API calls for hospital admin operations
 */

header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$hospital_id = $_GET['hospital_id'] ?? $_POST['hospital_id'] ?? $_SESSION['hospital_id'] ?? null;

try {
    include_once '../../backend/config/database.php';
    
    $db = new \PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", 
                  $_ENV['DB_USER'], 
                  $_ENV['DB_PASSWORD']);
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // Hospital admin can only access their hospital
    if ($_SESSION['access_type'] !== 'super' && $hospital_id != $_SESSION['hospital_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    switch ($action) {
        case 'get_tokens':
            // Get tokens for a hospital
            $query = "SELECT t.*, d.name_en as department, u.full_name, u.phone_number
                     FROM tokens t
                     JOIN departments d ON t.department_id = d.id
                     LEFT JOIN users u ON t.user_id = u.id
                     WHERE t.hospital_id = :hospital_id
                     ORDER BY t.created_at DESC
                     LIMIT 100";
            
            $stmt = $db->prepare($query);
            $stmt->execute([':hospital_id' => $hospital_id]);
            
            echo json_encode([
                'success' => true,
                'tokens' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
            ]);
            break;

        case 'update_token_status':
            // Update token status
            $token_id = $_POST['token_id'] ?? null;
            $status = $_POST['status'] ?? null;

            if (!$token_id || !$status) {
                throw new Exception('Missing token_id or status');
            }

            $query = "UPDATE tokens SET status = :status, updated_at = NOW() 
                     WHERE id = :token_id AND hospital_id = :hospital_id";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':status' => $status,
                ':token_id' => $token_id,
                ':hospital_id' => $hospital_id
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Token status updated'
            ]);
            break;

        case 'get_assisted_bookings':
            // Get assisted bookings
            $date = $_GET['date'] ?? null;
            
            $query = "SELECT ab.*, d.name_en as department 
                     FROM assisted_bookings ab
                     JOIN departments d ON ab.department_id = d.id
                     WHERE ab.hospital_id = :hospital_id";
            
            $params = [':hospital_id' => $hospital_id];

            if ($date) {
                $query .= " AND ab.booking_date = :date";
                $params[':date'] = $date;
            }

            $query .= " ORDER BY ab.booking_date, ab.booking_time";

            $stmt = $db->prepare($query);
            $stmt->execute($params);

            echo json_encode([
                'success' => true,
                'bookings' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
            ]);
            break;

        case 'get_statistics':
            // Get hospital statistics
            $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $end_date = $_GET['end_date'] ?? date('Y-m-d');

            $query = "SELECT * FROM hospital_statistics 
                     WHERE hospital_id = :hospital_id 
                     AND date BETWEEN :start_date AND :end_date
                     ORDER BY date DESC";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':hospital_id' => $hospital_id,
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);

            echo json_encode([
                'success' => true,
                'statistics' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
            ]);
            break;

        case 'get_departments':
            // Get hospital departments
            $query = "SELECT hd.*, d.name_en, d.name_ne 
                     FROM hospital_departments hd
                     JOIN departments d ON hd.department_id = d.id
                     WHERE hd.hospital_id = :hospital_id AND hd.is_active = 1";
            
            $stmt = $db->prepare($query);
            $stmt->execute([':hospital_id' => $hospital_id]);

            echo json_encode([
                'success' => true,
                'departments' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
            ]);
            break;

        case 'get_staff':
            // Get hospital staff
            $query = "SELECT hs.*, d.name_en as department 
                     FROM hospital_staff hs
                     LEFT JOIN departments d ON hs.department_id = d.id
                     WHERE hs.hospital_id = :hospital_id AND hs.is_active = 1
                     ORDER BY hs.position, hs.name";
            
            $stmt = $db->prepare($query);
            $stmt->execute([':hospital_id' => $hospital_id]);

            echo json_encode([
                'success' => true,
                'staff' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
            ]);
            break;

        case 'get_daily_summary':
            // Get today's summary
            $query = "SELECT 
                     (SELECT COUNT(*) FROM tokens WHERE hospital_id = :hospital_id AND DATE(created_at) = CURDATE()) as total_tokens,
                     (SELECT COUNT(*) FROM tokens WHERE hospital_id = :hospital_id AND DATE(created_at) = CURDATE() AND status = 'Completed') as completed,
                     (SELECT COUNT(*) FROM assisted_bookings WHERE hospital_id = :hospital_id AND booking_date = CURDATE()) as assisted_today,
                     (SELECT COUNT(*) FROM hospital_staff WHERE hospital_id = :hospital_id AND is_active = 1) as staff_count";
            
            $stmt = $db->prepare($query);
            $stmt->execute([':hospital_id' => $hospital_id]);
            $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'summary' => $summary
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
