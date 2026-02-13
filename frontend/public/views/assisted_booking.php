<?php
/**
 * Public Assisted Booking Page
 * For patients to register for assisted bookings without login
 */

session_start();

$hospital_id = isset($_GET['hospital_id']) ? intval($_GET['hospital_id']) : null;

if (!$hospital_id) {
    header('Location: hospitals.php');
    exit;
}

require_once __DIR__ . '/../../backend/controllers/TokenController.php';
$controller = new \App\Controllers\TokenController();

// Get hospital details
$hospital = $controller->getHospitalById($hospital_id);
if (!$hospital) {
    header('Location: hospitals.php?error=Hospital not found');
    exit;
}

// Get departments for this hospital
$departments = $controller->getHospitalDepartments($hospital_id);

$success_message = '';
$error_message = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_name = isset($_POST['patient_name']) ? trim($_POST['patient_name']) : '';
    $patient_phone = isset($_POST['patient_phone']) ? trim($_POST['patient_phone']) : '';
    $patient_age = isset($_POST['patient_age']) ? intval($_POST['patient_age']) : null;
    $patient_gender = isset($_POST['patient_gender']) ? trim($_POST['patient_gender']) : '';
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : null;
    $symptoms = isset($_POST['symptoms']) ? trim($_POST['symptoms']) : '';
    $booking_date = isset($_POST['booking_date']) ? trim($_POST['booking_date']) : '';
    $booking_time = isset($_POST['booking_time']) ? trim($_POST['booking_time']) : '';

    // Validation
    if (empty($patient_name)) {
        $error_message = 'Patient name is required';
    } elseif (empty($patient_phone) || !preg_match('/^98\d{8}$/', $patient_phone)) {
        $error_message = 'Valid phone number (98XXXXXXXXX) is required';
    } elseif (!$department_id) {
        $error_message = 'Please select a department';
    } elseif (empty($booking_date)) {
        $error_message = 'Booking date is required';
    } elseif (empty($booking_time)) {
        $error_message = 'Booking time is required';
    } else {
        // Create assisted booking using API
        require_once __DIR__ . '/../../backend/api/create_assisted_booking.php';
        
        // Response will be handled by the API
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assisted Booking - <?php echo htmlspecialchars($hospital['hospital_name']); ?> - SmartHealth Nepal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }

        .header h1 {
            font-size: 24px;
        }

        .header p {
            opacity: 0.9;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .form-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
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
            gap: 20px;
        }

        .required {
            color: #e74c3c;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .hospital-info-box {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .hospital-info-box h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .hospital-info-box p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📱 Assisted Booking Registration</h1>
        <p>Register for quick booking at <?php echo htmlspecialchars($hospital['hospital_name']); ?></p>
    </div>

    <div class="container">
        <div class="form-card">
            <!-- Hospital Info -->
            <div class="hospital-info-box">
                <h3>🏥 <?php echo htmlspecialchars($hospital['hospital_name']); ?></h3>
                <p>📍 <?php echo htmlspecialchars($hospital['municipality'] . ', ' . $hospital['district']); ?></p>
                <p>📞 <?php echo htmlspecialchars($hospital['phone']); ?></p>
                <p><?php echo htmlspecialchars($hospital['description']); ?></p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">❌ <?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">✓ <?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form method="POST">
                <!-- Patient Information -->
                <h2 style="margin-bottom: 20px; color: #2c3e50; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    Patient Information
                </h2>

                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input 
                        type="text" 
                        name="patient_name" 
                        placeholder="Enter your full name"
                        required
                        value="<?php echo htmlspecialchars($_POST['patient_name'] ?? ''); ?>"
                    >
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number <span class="required">*</span></label>
                        <input 
                            type="tel" 
                            name="patient_phone" 
                            placeholder="98XXXXXXXXX"
                            pattern="98[0-9]{8}"
                            required
                            value="<?php echo htmlspecialchars($_POST['patient_phone'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label>Age</label>
                        <input 
                            type="number" 
                            name="patient_age" 
                            placeholder="Age"
                            min="0"
                            max="150"
                            value="<?php echo htmlspecialchars($_POST['patient_age'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="patient_gender">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($_POST['patient_gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($_POST['patient_gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($_POST['patient_gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>

                <!-- Appointment Details -->
                <h2 style="margin: 30px 0 20px 0; color: #2c3e50; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    Appointment Details
                </h2>

                <div class="form-row">
                    <div class="form-group">
                        <label>Department <span class="required">*</span></label>
                        <select name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo ($_POST['department_id'] ?? '') == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name_en']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Preferred Date <span class="required">*</span></label>
                        <input 
                            type="date" 
                            name="booking_date" 
                            required
                            min="<?php echo date('Y-m-d'); ?>"
                            value="<?php echo htmlspecialchars($_POST['booking_date'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label>Preferred Time <span class="required">*</span></label>
                        <input 
                            type="time" 
                            name="booking_time" 
                            required
                            value="<?php echo htmlspecialchars($_POST['booking_time'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <!-- Additional Information -->
                <h2 style="margin: 30px 0 20px 0; color: #2c3e50; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                    Additional Information
                </h2>

                <div class="form-group">
                    <label>Symptoms / Reason for Visit</label>
                    <textarea 
                        name="symptoms" 
                        placeholder="Describe your symptoms or reason for visiting..."
                    ><?php echo htmlspecialchars($_POST['symptoms'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn-submit">✓ Complete Booking</button>

                <p style="text-align: center; margin-top: 20px; color: #999; font-size: 14px;">
                    <a href="hospitals.php" style="color: #667eea; text-decoration: none;">← Back to Hospitals</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
