<?php
/**
 * HospitalHelper - Hospital Location and Suggestion Service
 * 
 * Handles:
 * - Loading hospital data from JSON
 * - Calculating distances between locations
 * - Finding nearest hospitals
 * - Suggesting hospitals based on specialities and distance
 * - Filtering by district, municipality, ward
 */

class HospitalHelper {
    private $db;
    private $hospitalsData = null;
    private $hospitalJsonPath;
    
    const EARTH_RADIUS_KM = 6371; // Earth's radius in kilometers
    const DEFAULT_SEARCH_RADIUS_KM = 50; // Default search radius
    
    public function __construct($db) {
        $this->db = $db;
        $this->hospitalJsonPath = __DIR__ . '/../data/hospitals.json';
    }
    
    /**
     * Load hospitals from JSON file or database
     * @param bool $useDatabase - Use database instead of JSON
     * @return array
     */
    public function loadHospitals($useDatabase = true) {
        if ($useDatabase) {
            return $this->loadFromDatabase();
        }
        
        if ($this->hospitalsData !== null) {
            return $this->hospitalsData;
        }
        
        if (!file_exists($this->hospitalJsonPath)) {
            return ['hospitals' => [], 'error' => 'Hospital data file not found'];
        }
        
        $jsonData = file_get_contents($this->hospitalJsonPath);
        $this->hospitalsData = json_decode($jsonData, true);
        
        return $this->hospitalsData ?? ['hospitals' => []];
    }
    
    /**
     * Load hospitals from database
     * @return array
     */
    private function loadFromDatabase() {
        try {
            $query = "SELECT * FROM hospital_locations WHERE is_active = 1 ORDER BY district, municipality";
            $result = $this->db->query($query);
            
            $hospitals = [];
            while ($row = $result->fetch_assoc()) {
                // Parse specialities JSON
                if (!empty($row['specialities'])) {
                    $row['specialities'] = json_decode($row['specialities'], true);
                }
                $hospitals[] = $row;
            }
            
            return ['hospitals' => $hospitals];
        } catch (Exception $e) {
            error_log("Error loading hospitals from database: " . $e->getMessage());
            return ['hospitals' => [], 'error' => 'Database error'];
        }
    }
    
    /**
     * Get hospitals by district
     * @param string $district - District name
     * @return array
     */
    public function getHospitalsByDistrict($district) {
        $hospitals = $this->loadHospitals();
        
        return array_filter($hospitals['hospitals'], function($h) use ($district) {
            return strtolower($h['district']) === strtolower($district);
        });
    }
    
    /**
     * Get hospitals by district and municipality
     * @param string $district
     * @param string $municipality
     * @return array
     */
    public function getHospitalsByLocation($district, $municipality) {
        $hospitals = $this->loadHospitals();
        
        return array_filter($hospitals['hospitals'], function($h) use ($district, $municipality) {
            return strtolower($h['district']) === strtolower($district) &&
                   strtolower($h['municipality']) === strtolower($municipality);
        });
    }
    
    /**
     * Get hospitals by speciality
     * @param string $speciality - Required speciality
     * @return array
     */
    public function getHospitalsBySpeciality($speciality) {
        $hospitals = $this->loadHospitals();
        
        return array_filter($hospitals['hospitals'], function($h) use ($speciality) {
            $specs = is_array($h['specialities']) ? $h['specialities'] : json_decode($h['specialities'], true) ?? [];
            return in_array($speciality, $specs, true);
        });
    }
    
    /**
     * Calculate distance between two coordinates using Haversine formula
     * @param float $lat1 - User latitude
     * @param float $lon1 - User longitude  
     * @param float $lat2 - Hospital latitude
     * @param float $lon2 - Hospital longitude
     * @return float - Distance in kilometers
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $lat1_rad = deg2rad($lat1);
        $lon1_rad = deg2rad($lon1);
        $lat2_rad = deg2rad($lat2);
        $lon2_rad = deg2rad($lon2);
        
        $dlat = $lat2_rad - $lat1_rad;
        $dlon = $lon2_rad - $lon1_rad;
        
        $a = sin($dlat / 2) * sin($dlat / 2) +
             cos($lat1_rad) * cos($lat2_rad) * sin($dlon / 2) * sin($dlon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = self::EARTH_RADIUS_KM * $c;
        
        return round($distance, 2);
    }
    
    /**
     * Find nearest hospitals by distance
     * @param float $latitude - User latitude
     * @param float $longitude - User longitude
     * @param int $limit - Number of hospitals to return
     * @param float $radiusKm - Search radius in kilometers
     * @return array
     */
    public function findNearestHospitals($latitude, $longitude, $limit = 5, $radiusKm = self::DEFAULT_SEARCH_RADIUS_KM) {
        if (!$latitude || !$longitude) {
            return ['error' => 'Invalid coordinates provided'];
        }
        
        $hospitals = $this->loadHospitals();
        $nearbyHospitals = [];
        
        foreach ($hospitals['hospitals'] as $hospital) {
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                (float)$hospital['latitude'],
                (float)$hospital['longitude']
            );
            
            // Only include hospitals within search radius
            if ($distance <= $radiusKm) {
                $hospital['distance'] = $distance;
                $nearbyHospitals[] = $hospital;
            }
        }
        
        // Sort by distance
        usort($nearbyHospitals, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        
        // Return top N
        return array_slice($nearbyHospitals, 0, $limit);
    }
    
    /**
     * Suggest best hospitals based on symptoms and location
     * @param array $symptoms - Array of symptoms/conditions
     * @param float $latitude - User latitude
     * @param float $longitude - User longitude
     * @param string $district - User district (optional, for fallback)
     * @param string $municipality - User municipality (optional, for fallback)
     * @return array
     */
    public function suggestBestHospitals($symptoms, $latitude = null, $longitude = null, $district = null, $municipality = null) {
        $requiredSpecialities = $this->mapSymptomsToSpecialities($symptoms);
        
        if (empty($requiredSpecialities)) {
            // Fallback: return nearest hospitals by location
            return $this->suggestByLocation($district, $municipality, $latitude, $longitude);
        }
        
        // Get hospitals with required specialities
        $suitableHospitals = [];
        $hospitals = $this->loadHospitals();
        
        foreach ($hospitals['hospitals'] as $hospital) {
            $specs = is_array($hospital['specialities']) ? 
                     $hospital['specialities'] : 
                     json_decode($hospital['specialities'], true) ?? [];
            
            // Check how many required specialities this hospital has
            $matchCount = count(array_intersect($specs, $requiredSpecialities));
            
            if ($matchCount > 0) {
                $hospital['speciality_match'] = $matchCount;
                
                // Calculate distance if coordinates provided
                if ($latitude && $longitude) {
                    $hospital['distance'] = $this->calculateDistance(
                        $latitude,
                        $longitude,
                        $hospital['latitude'],
                        $hospital['longitude']
                    );
                }
                
                $suitableHospitals[] = $hospital;
            }
        }
        
        // Sort by speciality match first, then by distance
        usort($suitableHospitals, function($a, $b) {
            if ($a['speciality_match'] === $b['speciality_match']) {
                // If both have same speciality match, sort by distance
                $distA = $a['distance'] ?? PHP_INT_MAX;
                $distB = $b['distance'] ?? PHP_INT_MAX;
                return $distA <=> $distB;
            }
            return $b['speciality_match'] <=> $a['speciality_match']; // Higher match first
        });
        
        return array_slice($suitableHospitals, 0, 5); // Return top 5
    }
    
    /**
     * Suggest hospitals by location only
     * @param string $district
     * @param string $municipality
     * @param float $latitude
     * @param float $longitude
     * @return array
     */
    private function suggestByLocation($district, $municipality, $latitude = null, $longitude = null) {
        $hospitals = [];
        
        // First try municipality level
        if ($district && $municipality) {
            $hospitals = array_values($this->getHospitalsByLocation($district, $municipality));
        }
        
        // If no results, try district level
        if (empty($hospitals) && $district) {
            $hospitals = array_values($this->getHospitalsByDistrict($district));
        }
        
        // If still no results, return nearest hospitals by distance
        if (empty($hospitals) && $latitude && $longitude) {
            return $this->findNearestHospitals($latitude, $longitude);
        }
        
        // Add distances if coordinates provided
        if ($latitude && $longitude) {
            foreach ($hospitals as &$hospital) {
                $hospital['distance'] = $this->calculateDistance(
                    $latitude,
                    $longitude,
                    $hospital['latitude'],
                    $hospital['longitude']
                );
            }
            
            // Sort by distance
            usort($hospitals, function($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });
        }
        
        return array_slice($hospitals, 0, 5);
    }
    
    /**
     * Map symptoms to required specialities
     * @param array $symptoms - Array of symptoms
     * @return array - Array of required specialities
     */
    private function mapSymptomsToSpecialities($symptoms) {
        $symptomMap = [
            // Fever-related
            'fever' => ['General Medicine', 'Internal Medicine', 'Emergency'],
            
            // Respiratory issues
            'respiratory' => ['Emergency', 'Internal Medicine', 'Respiratory Medicine'],
            'difficulty_breathing' => ['Emergency', 'Internal Medicine', 'Respiratory Medicine'],
            
            // Cardiac issues
            'chest_pain' => ['Cardiology', 'Emergency'],
            
            // Injury-related
            'injury' => ['Surgery', 'Emergency', 'Orthopedics', 'Trauma'],
            
            // Maternal health
            'maternal' => ['Maternal Health', 'Obstetrics & Gynecology', 'General Medicine'],
            'pregnancy' => ['Maternal Health', 'Obstetrics & Gynecology', 'General Medicine'],
            'pregnant' => ['Maternal Health', 'Obstetrics & Gynecology', 'General Medicine'],
            
            // Chronic diseases
            'diabetes' => ['General Medicine', 'Internal Medicine', 'Endocrinology'],
            'hypertension' => ['Cardiology', 'General Medicine', 'Internal Medicine'],
            'respiratory_disease' => ['Internal Medicine', 'Respiratory Medicine'],
            'chronic' => ['General Medicine', 'Internal Medicine'],
            'chronic_disease' => ['General Medicine', 'Internal Medicine'],
            
            // Other conditions
            'eye_problem' => ['Ophthalmology'],
            'child_health' => ['Pediatrics'],
            'bone_joint' => ['Orthopedics', 'Surgery'],
            'emergency_signs' => ['Emergency']
        ];
        
        $specialities = [];
        
        if (is_array($symptoms)) {
            foreach ($symptoms as $symptom) {
                $key = strtolower(str_replace(' ', '_', trim($symptom)));
                if (isset($symptomMap[$key])) {
                    $specialities = array_merge($specialities, $symptomMap[$key]);
                }
            }
        }
        
        // Remove duplicates and return
        return array_unique($specialities);
    }
    
    /**
     * Get districts list
     * @return array
     */
    public function getDistrictsList() {
        $hospitals = $this->loadHospitals();
        $districts = [];
        
        foreach ($hospitals['hospitals'] as $hospital) {
            if (!in_array($hospital['district'], $districts)) {
                $districts[] = $hospital['district'];
            }
        }
        
        sort($districts);
        return $districts;
    }
    
    /**
     * Get municipalities for a district
     * @param string $district
     * @return array
     */
    public function getMunicipalitiesByDistrict($district) {
        $hospitals = $this->loadHospitals();
        $municipalities = [];
        
        foreach ($hospitals['hospitals'] as $hospital) {
            if (strtolower($hospital['district']) === strtolower($district)) {
                if (!in_array($hospital['municipality'], $municipalities)) {
                    $municipalities[] = $hospital['municipality'];
                }
            }
        }
        
        sort($municipalities);
        return $municipalities;
    }
    
    /**
     * Get wards for a municipality
     * @param string $district
     * @param string $municipality
     * @return array
     */
    public function getWardsByMunicipality($district, $municipality) {
        $hospitals = $this->loadHospitals();
        $wards = [];
        
        foreach ($hospitals['hospitals'] as $hospital) {
            if (strtolower($hospital['district']) === strtolower($district) &&
                strtolower($hospital['municipality']) === strtolower($municipality)) {
                if ($hospital['ward'] && !in_array($hospital['ward'], $wards)) {
                    $wards[] = $hospital['ward'];
                }
            }
        }
        
        sort($wards);
        return $wards;
    }
    
    /**
     * Get all specialities
     * @return array
     */
    public function getAllSpecialities() {
        $hospitals = $this->loadHospitals();
        $specialities = [];
        
        foreach ($hospitals['hospitals'] as $hospital) {
            $specs = is_array($hospital['specialities']) ? 
                     $hospital['specialities'] : 
                     json_decode($hospital['specialities'], true) ?? [];
            
            foreach ($specs as $spec) {
                if (!in_array($spec, $specialities)) {
                    $specialities[] = $spec;
                }
            }
        }
        
        sort($specialities);
        return $specialities;
    }
    
    /**
     * Get hospital details by ID
     * @param int $hospitalId
     * @return array|null
     */
    public function getHospitalById($hospitalId) {
        $hospitals = $this->loadHospitals();
        
        foreach ($hospitals['hospitals'] as $hospital) {
            if ($hospital['id'] == $hospitalId) {
                return $hospital;
            }
        }
        
        return null;
    }
    
    /**
     * Load Nepal districts data from JSON
     * @return array
     */
    public function loadNepalDistricts() {
        $nepalDistrictsPath = __DIR__ . '/../data/nepal_districts.json';
        
        if (!file_exists($nepalDistrictsPath)) {
            return ['divisions' => [], 'error' => 'Nepal districts file not found'];
        }
        
        $jsonData = file_get_contents($nepalDistrictsPath);
        $data = json_decode($jsonData, true);
        
        return $data ?? ['divisions' => []];
    }
    
    /**
     * Get all districts from Nepal divisions
     * @return array
     */
    public function getAllDistricts() {
        $data = $this->loadNepalDistricts();
        $districts = [];
        
        foreach ($data['divisions'] as $division) {
            $districts[] = [
                'name' => $division['district'],
                'province' => $division['province'],
                'local_level_count' => count($division['local_levels'] ?? [])
            ];
        }
        
        return $districts;
    }
    
    /**
     * Get municipalities for a district with full details
     * @param string $district
     * @return array
     */
    public function getMunicipalitiesForDistrict($district) {
        $data = $this->loadNepalDistricts();
        
        foreach ($data['divisions'] as $division) {
            if (strtolower($division['district']) === strtolower($district)) {
                return $division['local_levels'] ?? [];
            }
        }
        
        return [];
    }
    
    /**
     * Get wards for a municipality
     * @param string $district
     * @param string $municipality
     * @return int - Number of wards
     */
    public function getWardsForMunicipality($district, $municipality) {
        $municipalities = $this->getMunicipalitiesForDistrict($district);
        
        foreach ($municipalities as $muni) {
            if (strtolower($muni['name']) === strtolower($municipality)) {
                return $muni['wards'] ?? 0;
            }
        }
        
        return 0;
    }
    
    /**
     * Get ward list as array for a municipality
     * @param string $district
     * @param string $municipality
     * @return array
     */
    public function getWardListForMunicipality($district, $municipality) {
        $wardCount = $this->getWardsForMunicipality($district, $municipality);
        $wards = [];
        
        for ($i = 1; $i <= $wardCount; $i++) {
            $wards[] = (string)$i;
        }
        
        return $wards;
    }
    
    /**
     * Suggest hospitals with priority-based ranking
     * 
     * Priority calculation:
     * 1. Hospital location priority (from municipalities)
     * 2. Symptom/specialty match
     * 3. Distance from user
     * 
     * @param string $district - User's district
     * @param string $municipality - User's municipality
     * @param array $symptoms - Health symptoms/conditions
     * @param float $latitude - User latitude (optional)
     * @param float $longitude - User longitude (optional)
     * @return array - Hospitals ranked by priority
     */
    public function suggestHospitalsByPriority($district, $municipality, $symptoms = [], $latitude = null, $longitude = null) {
        // Get municipality priority rating
        $municipalities = $this->getMunicipalitiesForDistrict($district);
        $muniPriority = 999; // Default low priority
        
        foreach ($municipalities as $muni) {
            if (strtolower($muni['name']) === strtolower($municipality)) {
                $muniPriority = $muni['priority_rating'] ?? 999;
                break;
            }
        }
        
        // Get hospitals in this municipality
        $hospitals = array_values($this->getHospitalsByLocation($district, $municipality));
        
        if (empty($hospitals)) {
            // Fallback to district level
            $hospitals = array_values($this->getHospitalsByDistrict($district));
        }
        
        // Score each hospital
        foreach ($hospitals as &$hospital) {
            $score = 0;
            $reasons = [];
            
            // 1. Location priority (40 points max)
            $locationScore = max(0, (5 - $muniPriority) * 8); // 1→32, 2→24, 3→16, 4→8
            $score += max(0, 40 - ($muniPriority - 1) * 10);
            if ($score > 0) {
                $reasons[] = "location_priority";
            }
            
            // 2. Specialty match (35 points max)
            if (!empty($symptoms)) {
                $requiredSpecs = $this->mapSymptomsToSpecialities($symptoms);
                $specs = is_array($hospital['specialities']) ? 
                         $hospital['specialities'] : 
                         json_decode($hospital['specialities'], true) ?? [];
                
                $matchCount = count(array_intersect($specs, $requiredSpecs));
                $specialtyScore = $matchCount * 7; // Each match = 7 points
                $score += $specialtyScore;
                
                if ($matchCount > 0) {
                    $reasons[] = "specialty_match:" . $matchCount;
                }
            }
            
            // 3. Distance (25 points max - closer is better)
            if ($latitude && $longitude) {
                $distance = $this->calculateDistance(
                    $latitude,
                    $longitude,
                    $hospital['latitude'],
                    $hospital['longitude']
                );
                $hospital['distance'] = $distance;
                
                // Distance score: 0-5km = 25, 5-10km = 20, 10-20km = 15, 20-50km = 10, >50km = 0
                if ($distance <= 5) {
                    $score += 25;
                    $reasons[] = "nearby_under_5km";
                } elseif ($distance <= 10) {
                    $score += 20;
                    $reasons[] = "nearby_under_10km";
                } elseif ($distance <= 20) {
                    $score += 15;
                    $reasons[] = "nearby_under_20km";
                } elseif ($distance <= 50) {
                    $score += 10;
                    $reasons[] = "nearby_under_50km";
                }
            }
            
            // 4. Type of hospital (bonus points)
            if ($hospital['type'] === 'Government') {
                $score += 5;
                $reasons[] = "government_hospital";
            }
            
            $hospital['priority_score'] = $score;
            $hospital['priority_reasons'] = $reasons;
        }
        
        // Sort by priority score descending
        usort($hospitals, function($a, $b) {
            if ($a['priority_score'] === $b['priority_score']) {
                // If same score, sort by distance (closer first)
                $distA = $a['distance'] ?? PHP_INT_MAX;
                $distB = $b['distance'] ?? PHP_INT_MAX;
                return $distA <=> $distB;
            }
            return $b['priority_score'] <=> $a['priority_score'];
        });
        
        return $hospitals;
    }
    
    /**
     * Get hospital suggestions with detailed information including priority
     * Combines location-based data with hospital suggestions
     * 
     * @param string $district
     * @param string $municipality
     * @param array $symptoms
     * @param float $latitude
     * @param float $longitude
     * @return array
     */
    public function getDetailedHospitalSuggestions($district, $municipality, $symptoms = [], $latitude = null, $longitude = null) {
        $suggestions = $this->suggestHospitalsByPriority($district, $municipality, $symptoms, $latitude, $longitude);
        
        return [
            'success' => true,
            'count' => count($suggestions),
            'district' => $district,
            'municipality' => $municipality,
            'hospitals' => array_slice($suggestions, 0, 5), // Top 5
            'all_hospitals' => $suggestions
        ];
    }
}
?>
