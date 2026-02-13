<?php
/**
 * Public Hospital Listing Page
 * For patients to browse and select hospitals
 */

session_start();

require_once __DIR__ . '/../../backend/controllers/TokenController.php';
require_once __DIR__ . '/../../backend/models/UserModel.php';

$controller = new \App\Controllers\TokenController();

// Get all active hospitals
$hospitals = $controller->getAllHospitals();

// Get filter parameters
$district = isset($_GET['district']) ? trim($_GET['district']) : '';
$municipality = isset($_GET['municipality']) ? trim($_GET['municipality']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Filter hospitals
$filtered = $hospitals;
if (!empty($district)) {
    $filtered = array_filter($filtered, function($h) use ($district) {
        return strtolower($h['district']) === strtolower($district);
    });
}
if (!empty($municipality)) {
    $filtered = array_filter($filtered, function($h) use ($municipality) {
        return strtolower($h['municipality']) === strtolower($municipality);
    });
}
if (!empty($search)) {
    $filtered = array_filter($filtered, function($h) use ($search) {
        return stripos($h['hospital_name'], $search) !== false || 
               stripos(json_encode($h['specialities']), $search) !== false;
    });
}

$districts = array_unique(array_map(function($h) { return $h['district']; }, $hospitals));
sort($districts);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Hospitals - SmartHealth Nepal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .hospitals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .hospital-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .hospital-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .hospital-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }

        .hospital-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .hospital-type {
            font-size: 12px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hospital-body {
            padding: 15px;
        }

        .hospital-info {
            margin-bottom: 12px;
        }

        .info-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 14px;
            color: #2c3e50;
        }

        .specialities {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 12px;
        }

        .specialty-badge {
            background: #ecf0f1;
            color: #2c3e50;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .hospital-footer {
            padding: 15px;
            border-top: 1px solid #ecf0f1;
            display: flex;
            gap: 10px;
        }

        .btn-small {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: #667eea;
            color: white;
        }

        .btn-view:hover {
            background: #5568d3;
        }

        .btn-book {
            background: #27ae60;
            color: white;
        }

        .btn-book:hover {
            background: #229954;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
        }

        .no-results p {
            color: #999;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 24px;
            }

            .hospitals-grid {
                grid-template-columns: 1fr;
            }

            .filter-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 Find Hospitals</h1>
        <p>Browse and select hospitals in Nepal</p>
    </div>

    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET">
                <div class="filter-row">
                    <div class="form-group">
                        <label>Search Hospital</label>
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Type hospital name..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label>District</label>
                        <select name="district">
                            <option value="">All Districts</option>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?php echo htmlspecialchars($d); ?>" <?php echo ($d === $district) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Type</label>
                        <select name="type">
                            <option value="">All Types</option>
                            <option value="Government">Government</option>
                            <option value="Private">Private</option>
                            <option value="Specialized">Specialized</option>
                            <option value="Non-Profit">Non-Profit</option>
                        </select>
                    </div>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">🔍 Search Hospitals</button>
                    <a href="?" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </div>

        <!-- Hospitals Grid -->
        <?php if (!empty($filtered)): ?>
            <div class="hospitals-grid">
                <?php foreach ($filtered as $hospital): ?>
                    <div class="hospital-card">
                        <div class="hospital-header">
                            <div class="hospital-name"><?php echo htmlspecialchars($hospital['hospital_name']); ?></div>
                            <div class="hospital-type"><?php echo $hospital['type']; ?></div>
                        </div>

                        <div class="hospital-body">
                            <div class="hospital-info">
                                <div class="info-label">📍 Location</div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($hospital['municipality'] . ', ' . $hospital['district']); ?>
                                </div>
                            </div>

                            <div class="hospital-info">
                                <div class="info-label">📞 Phone</div>
                                <div class="info-value">
                                    <a href="tel:<?php echo htmlspecialchars($hospital['phone']); ?>" style="color: #667eea; text-decoration: none;">
                                        <?php echo htmlspecialchars($hospital['phone']); ?>
                                    </a>
                                </div>
                            </div>

                            <?php if (!empty($hospital['specialities'])): ?>
                                <div class="hospital-info">
                                    <div class="info-label">🎯 Specialities</div>
                                    <div class="specialities">
                                        <?php 
                                        $specs = is_string($hospital['specialities']) ? json_decode($hospital['specialities'], true) : $hospital['specialities'];
                                        foreach ((array)$specs as $spec):
                                        ?>
                                            <span class="specialty-badge"><?php echo htmlspecialchars($spec); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="hospital-info">
                                <div class="info-label">Description</div>
                                <div class="info-value" style="font-size: 13px;">
                                    <?php echo htmlspecialchars(substr($hospital['description'], 0, 100) . '...'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="hospital-footer">
                            <button class="btn-small btn-view" onclick="viewHospital(<?php echo $hospital['id']; ?>)">
                                View Details
                            </button>
                            <button class="btn-small btn-book" onclick="bookHospital(<?php echo $hospital['id']; ?>)">
                                Book Now
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>❌ No hospitals found matching your criteria.</p>
                <p>Try adjusting your filters or search terms.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function viewHospital(hospitalId) {
            window.location.href = 'hospital_detail.php?id=' + hospitalId;
        }

        function bookHospital(hospitalId) {
            window.location.href = 'public_booking.php?hospital_id=' + hospitalId;
        }
    </script>
</body>
</html>
