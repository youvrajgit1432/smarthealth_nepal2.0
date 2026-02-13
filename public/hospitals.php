<?php
/**
 * Public Hospital Directory - Frontend Page
 */

session_start();

$hospitals = [];
$error = '';

try {
    include_once '../backend/config/database.php';
    
    $db = new \PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", 
                  $_ENV['DB_USER'], 
                  $_ENV['DB_PASSWORD']);
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // Get all active hospitals
    $query = "SELECT h.*, COUNT(DISTINCT hd.department_id) as dept_count 
              FROM hospital_locations h
              LEFT JOIN hospital_departments hd ON h.id = hd.hospital_id AND hd.is_active = 1
              WHERE h.is_active = 1
              GROUP BY h.id
              ORDER BY h.hospital_name";
    
    $stmt = $db->query($query);
    $hospitals = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    $error = 'Error loading hospitals: ' . $e->getMessage();
}

// Filter by district
$selected_district = $_GET['district'] ?? '';
$districts = [];

if (!empty($hospitals)) {
    $districts = array_unique(array_column($hospitals, 'district'));
    sort($districts);

    if ($selected_district) {
        $hospitals = array_filter($hospitals, function($h) use ($selected_district) {
            return $h['district'] === $selected_district;
        });
    }
}
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .filter-row {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group label {
            font-weight: 600;
            font-size: 14px;
        }

        .filter-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .hospitals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .hospital-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .hospital-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .hospital-header {
            background: #f9f9f9;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .hospital-header h3 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }

        .hospital-type {
            display: inline-block;
            padding: 3px 8px;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .hospital-content {
            padding: 15px;
            flex: 1;
        }

        .info-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .info-row strong {
            color: #666;
            min-width: 80px;
        }

        .info-row a {
            color: #667eea;
            text-decoration: none;
        }

        .info-row a:hover {
            text-decoration: underline;
        }

        .location {
            color: #999;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .departments {
            margin: 15px 0;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .departments label {
            font-weight: 600;
            font-size: 12px;
            color: #666;
            display: block;
            margin-bottom: 8px;
        }

        .dept-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .dept-tag {
            background: #f0f0f0;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            color: #666;
        }

        .specialities {
            margin: 10px 0;
        }

        .specialities label {
            font-weight: 600;
            font-size: 12px;
            color: #666;
            display: block;
            margin-bottom: 8px;
        }

        .spec-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .spec-item {
            background: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }

        .book-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            margin-top: 15px;
            width: 100%;
            transition: all 0.3s;
        }

        .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state h2 {
            margin-bottom: 10px;
        }

        nav {
            background: white;
            padding: 15px 20px;
            border-top: 1px solid #ddd;
            text-align: center;
        }

        nav a {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .emergency-badge {
            background: #f8d7da;
            color: #721c24;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
        }

        .results-count {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Find Nearby Hospitals</h1>
        <p>Book your appointment directly or get assistance from hospital staff</p>
    </header>

    <div class="container">
        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="district">Filter by District:</label>
                        <select id="district" name="district" onchange="this.form.submit()">
                            <option value="">All Districts</option>
                            <?php foreach ($districts as $dist): ?>
                                <option value="<?php echo htmlspecialchars($dist); ?>" <?php echo $selected_district === $dist ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dist); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: #fee; color: #c33; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($hospitals)): ?>
            <div class="results-count">
                Found <?php echo count($hospitals); ?> hospital<?php echo count($hospitals) !== 1 ? 's' : ''; ?>
                <?php echo $selected_district ? ' in ' . htmlspecialchars($selected_district) : ''; ?>
            </div>

            <div class="hospitals-grid">
                <?php foreach ($hospitals as $hospital): ?>
                    <div class="hospital-card">
                        <div class="hospital-header">
                            <h3><?php echo htmlspecialchars($hospital['hospital_name']); ?></h3>
                            <span class="hospital-type"><?php echo htmlspecialchars($hospital['type']); ?></span>
                            <?php if ($hospital['emergency_24_7']): ?>
                                <div class="emergency-badge">24/7 Emergency</div>
                            <?php endif; ?>
                        </div>

                        <div class="hospital-content">
                            <div class="location">
                                📍 <?php echo htmlspecialchars($hospital['municipality']); ?>, <?php echo htmlspecialchars($hospital['district']); ?>
                            </div>

                            <div class="info-row">
                                <strong>Phone:</strong>
                                <a href="tel:<?php echo htmlspecialchars($hospital['phone']); ?>">
                                    <?php echo htmlspecialchars($hospital['phone']); ?>
                                </a>
                            </div>

                            <div class="info-row">
                                <strong>Address:</strong>
                                <span><?php echo htmlspecialchars($hospital['address']); ?></span>
                            </div>

                            <?php if ($hospital['contact_email']): ?>
                                <div class="info-row">
                                    <strong>Email:</strong>
                                    <a href="mailto:<?php echo htmlspecialchars($hospital['contact_email']); ?>">
                                        <?php echo htmlspecialchars($hospital['contact_email']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="departments">
                                <label>Active Departments:</label>
                                <div class="dept-tags">
                                    <span class="dept-tag"><?php echo $hospital['dept_count'] ?? 0; ?> departments available</span>
                                </div>
                            </div>

                            <?php 
                            $specs = json_decode($hospital['specialities'], true);
                            if (!empty($specs) && is_array($specs)): 
                            ?>
                                <div class="specialities">
                                    <label>Specialities:</label>
                                    <div class="spec-list">
                                        <?php foreach (array_slice($specs, 0, 3) as $spec): ?>
                                            <span class="spec-item"><?php echo htmlspecialchars($spec); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($specs) > 3): ?>
                                            <span class="spec-item">+<?php echo count($specs) - 3; ?> more</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <button class="book-btn" onclick="location.href='/smarthealth_nepal/public/hospital-detail.php?id=<?php echo $hospital['id']; ?>'">
                                View & Book
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>No hospitals found</h2>
                <p>Try adjusting your filters or contact support for assistance.</p>
            </div>
        <?php endif; ?>
    </div>

    <nav>
        <a href="/smarthealth_nepal/">← Home</a>
        <a href="/smarthealth_nepal/frontend/views/token/">Book Online</a>
        <a href="/smarthealth_nepal/admin/hospital/login.php">Hospital Admin Login</a>
    </nav>
</body>
</html>
