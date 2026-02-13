<?php
/**
 * Assisted Bookings Management
 */

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

$hospital_id = $_GET['hospital_id'] ?? $_SESSION['hospital_id'] ?? null;
$error = '';
$success = '';
$bookings = [];
$pageTitle = 'Assisted Bookings';
$activePage = 'assisted-bookings';

try {
    require_once __DIR__ . '/../../backend/config/database.php';
    
    // Check connection
    if (!isset($db) || $db->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Handle booking submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $query = "INSERT INTO assisted_bookings 
                     (hospital_id, department_id, patient_name, patient_phone, patient_age, patient_gender, 
                      triage_data, priority, booking_date, booked_by, notes, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
            
            $stmt = $db->prepare($query);
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            
            $triage_data = isset($_POST['symptoms']) ? json_encode(['symptoms' => $_POST['symptoms']]) : null;
            $patient_age = $_POST['patient_age'] ?? null;
            $patient_gender = $_POST['patient_gender'] ?? null;
            $priority = $_POST['priority'] ?? 'Normal';
            $booking_date = $_POST['booking_date'];
            $notes = $_POST['notes'] ?? null;
            $admin_id = $_SESSION['admin_id'];
            $dept_id = $_POST['department_id'];
            
            $stmt->bind_param('iissiissisi', 
                $hospital_id,
                $dept_id,
                $_POST['patient_name'],
                $_POST['patient_phone'],
                $patient_age,
                $patient_gender,
                $triage_data,
                $priority,
                $booking_date,
                $admin_id,
                $notes
            );
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception('Insert failed: ' . $stmt->error);
            }
            $stmt->close();
            $success = 'Assisted booking created successfully!';
        } elseif ($_POST['action'] === 'update_status') {
            $query = "UPDATE assisted_bookings SET status = ?, updated_at = NOW() WHERE id = ? AND hospital_id = ?";
            $stmt = $db->prepare($query);
            if (!$stmt) {
                throw new Exception('Database error: ' . $db->error);
            }
            $status = $_POST['status'];
            $booking_id = $_POST['booking_id'];
            $stmt->bind_param('sii', $status, $booking_id, $hospital_id);
            $stmt->execute();
            if ($stmt->error) {
                throw new Exception('Update failed: ' . $stmt->error);
            }
            $stmt->close();
            $success = 'Booking status updated!';
        }
    }

    // Get bookings
    if ($hospital_id) {
        $query = "SELECT ab.*, d.name_en as department, a.full_name as booked_by_name
                 FROM assisted_bookings ab
                 LEFT JOIN departments d ON ab.department_id = d.id
                 LEFT JOIN admins a ON ab.booked_by = a.id
                 WHERE ab.hospital_id = ?
                 ORDER BY ab.booking_date DESC, ab.created_at DESC";
        
        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception('Database error: ' . $db->error);
        }
        $stmt->bind_param('i', $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}

// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

        <h1>Assisted Bookings Management</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- New Booking Form -->
        <div class="booking-form">
            <h2 style="margin-bottom: 20px;">Create New Assisted Booking</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="create">

                <div class="form-row">
                    <div class="form-group">
                        <label for="patient_name">Patient Name *</label>
                        <input type="text" id="patient_name" name="patient_name" required>
                    </div>
                    <div class="form-group">
                        <label for="patient_phone">Patient Phone *</label>
                        <input type="tel" id="patient_phone" name="patient_phone" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="patient_age">Age</label>
                        <input type="number" id="patient_age" name="patient_age" min="0" max="150">
                    </div>
                    <div class="form-group">
                        <label for="patient_gender">Gender</label>
                        <select id="patient_gender" name="patient_gender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department_id">Department *</label>
                        <select id="department_id" name="department_id" required>
                            <option value="">Select Department</option>
                            <!-- Departments will be populated from database -->
                            <option value="1">General Medicine</option>
                            <option value="2">Emergency</option>
                            <option value="3">Maternal Health</option>
                            <option value="7">Cardiology</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <option value="Normal">Normal</option>
                            <option value="Priority">Priority</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="booking_date">Booking Date *</label>
                        <input type="date" id="booking_date" name="booking_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="booking_time">Booking Time *</label>
                        <input type="time" id="booking_time" name="booking_time" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="symptoms">Symptoms / Reason for Visit</label>
                    <textarea id="symptoms" name="symptoms" placeholder="Describe symptoms or reason for visit"></textarea>
                </div>

                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes" placeholder="Any additional notes..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Create Assisted Booking</button>
            </form>
        </div>

        <!-- Bookings List -->
        <div class="booking-list">
            <h2 style="padding: 20px; border-bottom: 1px solid #ddd; margin: 0;">Upcoming Assisted Bookings</h2>
            
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <p>No assisted bookings found.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Registered By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['patient_phone']); ?></td>
                                <td><?php echo htmlspecialchars($booking['department'] ?? '-'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                                <td>
                                    <span class="priority-badge <?php echo strtolower($booking['priority']); ?>">
                                        <?php echo htmlspecialchars($booking['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($booking['status']); ?>">
                                        <?php echo htmlspecialchars($booking['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($booking['registered_by_name'] ?? '-'); ?></td>
                                <td>
                                    <div class="actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="action-btn" style="padding: 5px;">
                                                <option value="<?php echo $booking['status']; ?>" selected>Change...</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Assigned">Assigned</option>
                                                <option value="Completed">Completed</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px;">
            <a href="/smarthealth_nepal/admin/hospital/dashboard/" style="color: #667eea;">← Back to Dashboard</a>
        </div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
