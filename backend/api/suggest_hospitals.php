<?php
/**
 * Hospital Suggestion API
 * 
 * Endpoints:
 * POST /api/suggest-hospitals - Get hospital suggestions based on symptoms and location
 * GET /api/hospitals/by-location - Get hospitals by district/municipality
 * GET /api/districts - Get list of districts
 * GET /api/municipalities - Get municipalities by district
 * GET /api/wards - Get wards by municipality
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../helpers/HospitalHelper.php';

// Set JSON response header
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$hospitalHelper = new HospitalHelper($db);
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    
    // ===== GET HOSPITAL SUGGESTIONS (WITH PRIORITY) =====
    if ($method === 'POST' && $action === 'suggest') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $symptoms = $data['symptoms'] ?? [];
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        $district = $data['district'] ?? null;
        $municipality = $data['municipality'] ?? null;
        
        if (!$district || !$municipality) {
            $response['message'] = 'District and municipality are required';
            echo json_encode($response);
            exit;
        }
        
        // Use priority-based suggestions
        $suggestions = $hospitalHelper->getDetailedHospitalSuggestions(
            $district,
            $municipality,
            $symptoms,
            $latitude,
            $longitude
        );
        
        $response['success'] = true;
        $response['message'] = 'Hospital suggestions retrieved with priority ranking';
        $response['data'] = $suggestions;
    }
    
    // ===== GET HOSPITALS BY LOCATION =====
    else if ($method === 'GET' && $action === 'by-location') {
        $district = $_GET['district'] ?? null;
        $municipality = $_GET['municipality'] ?? null;
        
        if ($district && $municipality) {
            $hospitals = $hospitalHelper->getHospitalsByLocation($district, $municipality);
        } elseif ($district) {
            $hospitals = $hospitalHelper->getHospitalsByDistrict($district);
        } else {
            $response['message'] = 'District is required';
            echo json_encode($response);
            exit;
        }
        
        $response['success'] = true;
        $response['message'] = 'Hospitals retrieved';
        $response['data'] = array_values($hospitals);
    }
    
    // ===== GET DISTRICTS =====
    else if ($method === 'GET' && $action === 'districts') {
        $districts = $hospitalHelper->getDistrictsList();
        
        $response['success'] = true;
        $response['message'] = 'Districts retrieved';
        $response['data'] = $districts;
    }
    
    // ===== GET MUNICIPALITIES =====
    else if ($method === 'GET' && $action === 'municipalities') {
        $district = $_GET['district'] ?? null;
        
        if (!$district) {
            $response['message'] = 'District is required';
            echo json_encode($response);
            exit;
        }
        
        $municipalities = $hospitalHelper->getMunicipalitiesByDistrict($district);
        
        $response['success'] = true;
        $response['message'] = 'Municipalities retrieved';
        $response['data'] = $municipalities;
    }
    
    // ===== GET DISTRICT INFO WITH PRIORITY =====
    else if ($method === 'GET' && $action === 'district-info') {
        $district = $_GET['district'] ?? null;
        
        if (!$district) {
            $response['message'] = 'District is required';
            echo json_encode($response);
            exit;
        }
        
        $municipalities = $hospitalHelper->getMunicipalitiesForDistrict($district);
        
        if (empty($municipalities)) {
            $response['message'] = 'District not found';
            echo json_encode($response);
            exit;
        }
        
        $response['success'] = true;
        $response['message'] = 'District information with priority ratings retrieved';
        $response['data'] = [
            'district' => $district,
            'municipalities' => $municipalities,
            'priority_legend' => [
                1 => 'Highest Priority (Metropolitan City)',
                2 => 'High Priority (Sub-Metropolitan)',
                3 => 'Medium Priority (Municipality)',
                4 => 'Low Priority (Rural Municipality)'
            ]
        ];
    }
    
    // ===== GET HOSPITALS WITH PRIORITY RANKING =====
    else if ($method === 'GET' && $action === 'hospitals-with-priority') {
        $district = $_GET['district'] ?? null;
        $municipality = $_GET['municipality'] ?? null;
        $symptoms = $_GET['symptoms'] ?? null;
        $latitude = $_GET['latitude'] ?? null;
        $longitude = $_GET['longitude'] ?? null;
        
        if (!$district || !$municipality) {
            $response['message'] = 'District and municipality are required';
            echo json_encode($response);
            exit;
        }
        
        $symptomArray = !empty($symptoms) ? explode(',', $symptoms) : [];
        
        $hospitals = $hospitalHelper->suggestHospitalsByPriority(
            $district,
            $municipality,
            $symptomArray,
            $latitude,
            $longitude
        );
        
        $response['success'] = true;
        $response['message'] = 'Hospitals retrieved with priority ranking';
        $response['data'] = [
            'district' => $district,
            'municipality' => $municipality,
            'hospitals' => array_slice($hospitals, 0, 10),
            'total_hospitals' => count($hospitals)
        ];
    }
    else if ($method === 'GET' && $action === 'wards') {
        $district = $_GET['district'] ?? null;
        $municipality = $_GET['municipality'] ?? null;
        
        if (!$district || !$municipality) {
            $response['message'] = 'District and municipality are required';
            echo json_encode($response);
            exit;
        }
        
        $wards = $hospitalHelper->getWardsByMunicipality($district, $municipality);
        
        $response['success'] = true;
        $response['message'] = 'Wards retrieved';
        $response['data'] = $wards;
    }
    
    // ===== GET NEAREST HOSPITALS =====
    else if ($method === 'GET' && $action === 'nearest') {
        $latitude = $_GET['latitude'] ?? null;
        $longitude = $_GET['longitude'] ?? null;
        $radius = (float)($_GET['radius'] ?? 50);
        $limit = (int)($_GET['limit'] ?? 5);
        
        if (!$latitude || !$longitude) {
            $response['message'] = 'Latitude and longitude are required';
            echo json_encode($response);
            exit;
        }
        
        $hospitals = $hospitalHelper->findNearestHospitals($latitude, $longitude, $limit, $radius);
        
        $response['success'] = true;
        $response['message'] = 'Nearest hospitals retrieved';
        $response['data'] = $hospitals;
    }
    
    // ===== GET ALL SPECIALITIES =====
    else if ($method === 'GET' && $action === 'specialities') {
        $specialities = $hospitalHelper->getAllSpecialities();
        
        $response['success'] = true;
        $response['message'] = 'Specialities retrieved';
        $response['data'] = $specialities;
    }
    
    // ===== GET HOSPITAL DETAILS =====
    else if ($method === 'GET' && $action === 'details') {
        $hospitalId = $_GET['id'] ?? null;
        
        if (!$hospitalId) {
            $response['message'] = 'Hospital ID is required';
            echo json_encode($response);
            exit;
        }
        
        $hospital = $hospitalHelper->getHospitalById($hospitalId);
        
        if ($hospital) {
            $response['success'] = true;
            $response['message'] = 'Hospital details retrieved';
            $response['data'] = $hospital;
        } else {
            $response['message'] = 'Hospital not found';
        }
    }
    
    // ===== INVALID ACTION =====
    else {
        $response['message'] = 'Invalid action. Available actions: suggest, by-location, districts, municipalities, district-info, hospitals-with-priority, wards, nearest, specialities, details';
    }
    
} catch (Exception $e) {
    error_log("Hospital API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $response['message'] = 'API Error: ' . $e->getMessage();
}

echo json_encode($response);
?>
