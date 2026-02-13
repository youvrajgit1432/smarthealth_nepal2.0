<?php
/**
 * Assisted Bookings Management Page
 */

session_start();

// Check if hospital admin is logged in
if (!isset($_SESSION['hospital_admin_id']) || !isset($_SESSION['hospital_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/hospital/login.php');
    exit;
}

$hospital_id = $_SESSION['hospital_id'];
require_once __DIR__ . '/../../../backend/controllers/HospitalDashboardController.php';
$controller = new \App\Controllers\HospitalDashboardController();

// Handle form submission for new assisted booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'create') {
        $result = $controller->createAssistedBooking(
            $hospital_id,
            $_POST['department_id'],
            $_POST['patient_name'],
            $_POST['patient_phone'],
            $_POST['patient_age'] ?? null,
            $_POST['patient_gender'] ?? null,
            $_POST['symptoms'] ?? null,
            $_POST['priority'] ?? 'Normal',
            $_POST['booking_date'],
            $_POST['booking_time'],
            $_SESSION['hospital_admin_id'],
            $_POST['notes'] ?? null
        );
        
        if ($result['success']) {
            header('Location: assisted_bookings.php?success=Booking created successfully');
            exit;
        } else {
            $error_message = $result['message'];
        }
    }
}

// Get assisted bookings
$bookings = $controller->getAssistedBookings($hospital_id, 50);
$departments = $controller->getHospitalDepartments($hospital_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assisted Bookings - SmartHealth Nepal</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #95a5a6;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: #ecf0f1;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #bdc3c7;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
        }

        .table tbody tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge.assigned {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge.completed {
            background: #d4edda;
            color: #155724;
        }

        .badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .badge.normal {
            background: #e2e3e5;
            color: #383d41;
        }

        .badge.priority {
            background: #fff3cd;
            color: #856404;
        }

        .badge.emergency {
            background: #f8d7da;
            color: #721c24;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
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

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #667eea;
        }

        .modal-header h2 {
            color: #2c3e50;
        }

        .modal-footer {
            margin-top: 20px;
            text-align: right;
        }

        .modal-footer button {
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 12px;
            }

            .table td, .table th {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📱 Assisted Bookings</h1>
        <button class="btn" onclick="openNewBookingModal()">+ New Booking</button>
    </div>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">✓ <?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">✗ <?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="section">
            <h2>Assisted Bookings List</h2>
            
            <?php if (!empty($bookings)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Patient Name</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Date & Time</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Token</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($booking['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['patient_phone']); ?></td>
                                <td><?php echo htmlspecialchars($booking['department_name'] ?? 'N/A'); ?></td>
                                <td><?php echo $booking['booking_date'] . ' ' . $booking['booking_time']; ?></td>
                                <td>
                                    <span class="badge <?php echo strtolower($booking['priority']); ?>">
                                        <?php echo $booking['priority']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo strtolower($booking['status']); ?>">
                                        <?php echo $booking['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($booking['token_number'] ?? 'Pending'); ?></td>
                                <td>
                                    <button class="btn" style="font-size: 12px;" onclick="editBooking(<?php echo $booking['id']; ?>)">Edit</button>
                                    <button class="btn btn-danger" style="font-size: 12px;" onclick="deleteBooking(<?php echo $booking['id']; ?>)">Cancel</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 40px;">No assisted bookings yet. <a href="#" onclick="openNewBookingModal(); return false;">Create one now</a></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- New Booking Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Assisted Booking</h2>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label>Patient Name *</label>
                    <input type="text" name="patient_name" required placeholder="Full name">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="patient_phone" required placeholder="98XXXXXXXXX">
                    </div>

                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="patient_age" placeholder="Age" min="0" max="150">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="patient_gender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['name_en']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Booking Date *</label>
                        <input type="date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Booking Time *</label>
                        <input type="time" name="booking_time" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority">
                            <option value="Normal">Normal</option>
                            <option value="Priority">Priority</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Symptoms/Reason for Visit</label>
                    <textarea name="symptoms" placeholder="Describe symptoms or reason for visit"></textarea>
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" placeholder="Additional notes"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeNewBookingModal()">Cancel</button>
                    <button type="submit" class="btn">Create Booking</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openNewBookingModal() {
            document.getElementById('bookingModal').classList.add('active');
        }

        function closeNewBookingModal() {
            document.getElementById('bookingModal').classList.remove('active');
        }

        function editBooking(bookingId) {
            alert('Edit booking ' + bookingId + ' - Coming soon');
            // TODO: Implement edit functionality
        }

        function deleteBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                alert('Delete booking ' + bookingId + ' - Coming soon');
                // TODO: Implement delete functionality
            }
        }

        // Close modal when clicking outside
        document.getElementById('bookingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    </script>
</body>
</html>
