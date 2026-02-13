<?php
/**
 * Public Hospital Detail Page with Assisted Booking
 */

session_start();

$hospital = [];
$departments = [];
$error = '';
$success = '';
$hospital_id = $_GET['id'] ?? null;

try {
    include_once '../backend/config/database.php';
    
    $db = new \PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", 
                  $_ENV['DB_USER'], 
                  $_ENV['DB_PASSWORD']);
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // Get hospital details
    if ($hospital_id) {
        $query = "SELECT * FROM hospital_locations WHERE id = :id AND is_active = 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $hospital_id]);
        $hospital = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (empty($hospital)) {
            $error = 'Hospital not found';
        } else {
            // Get departments for this hospital
            $deptQuery = "SELECT d.id, d.name_en, d.name_ne, d.description_en, hd.max_tokens_per_day, hd.current_daily_tokens, hd.available
                         FROM hospital_departments hd
                         JOIN departments d ON hd.department_id = d.id
                         WHERE hd.hospital_id = :hospital_id AND hd.is_active = 1 AND hd.available = 1";
            $deptStmt = $db->prepare($deptQuery);
            $deptStmt->execute([':hospital_id' => $hospital_id]);
            $departments = $deptStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Handle assisted booking submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_assisted_booking') {
                // Validate inputs
                if (empty($_POST['patient_name']) || empty($_POST['patient_phone']) || empty($_POST['department_id']) || 
                    empty($_POST['booking_date']) || empty($_POST['booking_time'])) {
                    $error = 'Please fill in all required fields';
                } else {
                    try {
                        // Create assisted booking
                        $insertQuery = "INSERT INTO assisted_bookings 
                                       (hospital_id, department_id, patient_name, patient_phone, patient_age, patient_gender,
                                        symptoms, priority, booking_date, booking_time, notes, status, created_at)
                                       VALUES (:hospital_id, :department_id, :patient_name, :patient_phone, :patient_age, :patient_gender,
                                               :symptoms, :priority, :booking_date, :booking_time, :notes, 'Pending', NOW())";
                        
                        $insertStmt = $db->prepare($insertQuery);
                        $insertStmt->execute([
                            ':hospital_id' => $hospital_id,
                            ':department_id' => $_POST['department_id'],
                            ':patient_name' => $_POST['patient_name'],
                            ':patient_phone' => $_POST['patient_phone'],
                            ':patient_age' => $_POST['patient_age'] ?? null,
                            ':patient_gender' => $_POST['patient_gender'] ?? null,
                            ':symptoms' => $_POST['symptoms'] ?? null,
                            ':priority' => $_POST['priority'] ?? 'Normal',
                            ':booking_date' => $_POST['booking_date'],
                            ':booking_time' => $_POST['booking_time'],
                            ':notes' => $_POST['notes'] ?? null
                        ]);

                        $success = 'Assisted booking request submitted successfully! Hospital staff will contact you shortly.';
                    } catch (\Exception $e) {
                        $error = 'Error creating booking: ' . $e->getMessage();
                    }
                }
            }
        }
    } else {
        $error = 'No hospital selected';
    }
} catch (\Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($hospital) ? htmlspecialchars($hospital['hospital_name']) : 'Hospital'; ?> - SmartHealth Nepal</title>
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
            padding: 20px;
        }

        .header-top {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-top h1 {
            font-size: 28px;
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background-color: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .hospital-info {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .hospital-info h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .info-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .info-section:last-child {
            border-bottom: none;
        }

        .info-section h3 {
            font-size: 13px;
            font-weight: 600;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .info-section p {
            font-size: 14px;
            line-height: 1.6;
        }

        .info-section a {
            color: #667eea;
            text-decoration: none;
        }

        .info-section a:hover {
            text-decoration: underline;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .badge.emergency {
            background: #f8d7da;
            color: #721c24;
        }

        .hours {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            font-size: 13px;
        }

        .booking-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .booking-form h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .departments-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .departments-section h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
        }

        .departments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .department-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }

        .department-card h3 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .department-card p {
            font-size: 12px;
            color: #666;
            margin: 5px 0;
        }

        .required {
            color: #e74c3c;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .header-top {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-top">
            <h1><?php echo !empty($hospital) ? htmlspecialchars($hospital['hospital_name']) : 'Hospital'; ?></h1>
            <a href="/smarthealth_nepal/public/hospitals.php" class="back-btn">← Back to Hospitals</a>
        </div>
    </header>

    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($hospital)): ?>
            <div class="content-grid">
                <!-- Hospital Info -->
                <div class="hospital-info">
                    <h2><?php echo htmlspecialchars($hospital['hospital_name']); ?></h2>

                    <div class="badges">
                        <span class="badge"><?php echo htmlspecialchars($hospital['type']); ?></span>
                        <?php if ($hospital['emergency_24_7']): ?>
                            <span class="badge emergency">24/7 Emergency</span>
                        <?php endif; ?>
                    </div>

                    <div class="info-section">
                        <h3>Location</h3>
                        <p>
                            <?php echo htmlspecialchars($hospital['address']); ?><br>
                            <?php echo htmlspecialchars($hospital['ward']) ?? ''; ?>, 
                            <?php echo htmlspecialchars($hospital['municipality']); ?>,<br>
                            <?php echo htmlspecialchars($hospital['district']); ?>
                        </p>
                    </div>

                    <div class="info-section">
                        <h3>Contact</h3>
                        <p>
                            <strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($hospital['phone']); ?>"><?php echo htmlspecialchars($hospital['phone']); ?></a><br>
                            <?php if ($hospital['contact_email']): ?>
                                <strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($hospital['contact_email']); ?>"><?php echo htmlspecialchars($hospital['contact_email']); ?></a>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="info-section">
                        <h3>Hours</h3>
                        <div class="hours">
                            <p><strong>Regular Hours:</strong><br><?php echo htmlspecialchars($hospital['opening_time']); ?> - <?php echo htmlspecialchars($hospital['closing_time']); ?></p>
                            <?php if ($hospital['emergency_24_7']): ?>
                                <p style="margin-top: 10px;"><strong>Emergency:</strong> 24/7 Available</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="info-section">
                        <h3>Description</h3>
                        <p><?php echo htmlspecialchars($hospital['description']); ?></p>
                    </div>
                </div>

                <!-- Booking Form -->
                <div class="booking-form">
                    <h2>Request Assisted Booking</h2>
                    <p style="font-size: 12px; color: #666; margin-bottom: 20px;">
                        Fill in your details and our hospital staff will contact you to confirm your appointment.
                    </p>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="create_assisted_booking">

                        <div class="form-group">
                            <label for="patient_name">Full Name <span class="required">*</span></label>
                            <input type="text" id="patient_name" name="patient_name" required>
                        </div>

                        <div class="form-group">
                            <label for="patient_phone">Phone Number <span class="required">*</span></label>
                            <input type="tel" id="patient_phone" name="patient_phone" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="patient_age">Age</label>
                                <input type="number" id="patient_age" name="patient_age" min="0" max="150">
                            </div>
                            <div class="form-group">
                                <label for="patient_gender">Gender</label>
                                <select id="patient_gender" name="patient_gender">
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="department_id">Department <span class="required">*</span></label>
                            <select id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>">
                                        <?php echo htmlspecialchars($dept['name_en']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="booking_date">Preferred Date <span class="required">*</span></label>
                                <input type="date" id="booking_date" name="booking_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="booking_time">Preferred Time <span class="required">*</span></label>
                                <input type="time" id="booking_time" name="booking_time" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select id="priority" name="priority">
                                <option value="Normal">Normal</option>
                                <option value="Priority">Priority</option>
                                <option value="Emergency">Emergency</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="symptoms">Symptoms / Reason for Visit</label>
                            <textarea id="symptoms" name="symptoms" placeholder="Describe your symptoms or reason for visit..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes">Additional Notes</label>
                            <textarea id="notes" name="notes" placeholder="Any additional information..."></textarea>
                        </div>

                        <button type="submit" class="submit-btn">Request Assisted Booking</button>
                    </form>
                </div>
            </div>

            <!-- Departments -->
            <?php if (!empty($departments)): ?>
                <div class="departments-section">
                    <h2>Available Departments</h2>
                    <div class="departments-grid">
                        <?php foreach ($departments as $dept): ?>
                            <div class="department-card">
                                <h3><?php echo htmlspecialchars($dept['name_en']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($dept['description_en'] ?? '', 0, 80)); ?>...</p>
                                <p style="margin-top: 10px; color: #667eea; font-weight: 600;">
                                    <?php echo $dept['current_daily_tokens'] ?? 0; ?>/<?php echo $dept['max_tokens_per_day']; ?> tokens
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
