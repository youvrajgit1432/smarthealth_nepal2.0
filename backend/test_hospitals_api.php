<?php
/**
 * Hospital API Test/Debug Page
 * Test if hospitals are being loaded correctly
 */

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/helpers/HospitalHelper.php';

$hospitalHelper = new HospitalHelper($db);

// Test 1: Load all hospitals
echo "<h2>Test 1: All Hospitals Count</h2>";
$allHospitals = $hospitalHelper->loadHospitals();
$count = count($allHospitals['hospitals'] ?? []);
echo "Total hospitals loaded: <strong>$count</strong><br>";
echo "<em>Expected: 32 (24 original + 8 new Kathmandu hospitals)</em><br><br>";

// Test 2: Get hospitals by district
echo "<h2>Test 2: Hospitals by District</h2>";
$katDistance = $hospitalHelper->getHospitalsByDistrict('Kathmandu');
echo "Kathmandu district: <strong>" . count($katDistance) . "</strong> hospitals<br>";
echo "Expected: 11 (original 6 + 5 new)<br><br>";

// Test 3: Get hospitals by Kirtipur Municipality
echo "<h2>Test 3: Kirtipur Municipality Hospitals</h2>";
$kirtipur = $hospitalHelper->getHospitalsByLocation('Kathmandu', 'Kirtipur Municipality');
echo "Kirtipur Municipality: <strong>" . count($kirtipur) . "</strong> hospitals<br>";
if (!empty($kirtipur)) {
    foreach ($kirtipur as $hospital) {
        echo "  - {$hospital['hospital_name']} ({$hospital['type']})<br>";
    }
} else {
    echo "<span style='color:red;'>ERROR: No hospitals found for Kirtipur!</span><br>";
}
echo "Expected: 4 hospitals<br><br>";

// Test 4: Priority-based suggestions for Kirtipur
echo "<h2>Test 4: Priority-Based Suggestions for Kirtipur</h2>";
$suggestions = $hospitalHelper->suggestHospitalsByPriority(
    'Kathmandu',
    'Kirtipur Municipality',
    ['fever'],  // Test with fever symptom
    null,
    null
);

echo "Suggestions found: <strong>" . count($suggestions) . "</strong><br>";
if (!empty($suggestions)) {
    foreach ($suggestions as $i => $hospital) {
        echo ($i+1) . ". {$hospital['hospital_name']}<br>";
        echo "   Score: {$hospital['priority_score']}<br>";
        echo "   Reasons: " . implode(', ', $hospital['priority_reasons']) . "<br><br>";
    }
} else {
    echo "<span style='color:red;'>ERROR: No suggestions generated!</span><br>";
}

// Test 5: Test API endpoint directly
echo "<h2>Test 5: API Endpoint Test</h2>";
echo "Testing: /smarthealth_nepal/backend/api/suggest_hospitals.php?action=hospitals-with-priority<br>";
echo "Parameters: district=Kathmandu&municipality=Kirtipur Municipality&symptoms=fever<br><br>";

// Simulate API call
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/smarthealth_nepal/backend/api/suggest_hospitals.php?action=hospitals-with-priority&district=Kathmandu&municipality=Kirtipur%20Municipality&symptoms=fever');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $data = json_decode($response, true);
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "</pre>";
} else {
    echo "<span style='color:red;'>ERROR: Could not reach API endpoint</span>";
}

echo "<hr>";
echo "<p><strong>If you see '4 hospitals' in Test 3, the problem is fixed!</strong></p>";
echo "<p>If you see an error, the hospitals.json needs to be fixed or reloaded.</p>";
?>
