<?php
/**
 * Offline Assisted Booking Page
 */

require_once __DIR__ . '/../../../backend/init.php';

// Load language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

$pageTitle = $lang['offline_booking_mode'] ?? 'Offline Booking';
$activePage = 'offline';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-network-wired"></i>
                    <?php echo $lang['offline_booking_mode'] ?? 'Offline Booking'; ?>
                </h5>
            </div>
            
            <div class="card-body p-4">
                <div class="alert alert-info">
                    <h6><?php echo $lang['assisted_booking'] ?? 'For Hospital Staff'; ?></h6>
                    <p><?php echo $lang['hospital_staff_only'] ?? 'This feature is for hospital staff to book tokens for patients'; ?></p>
                </div>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="patient_phone" class="form-label"><?php echo $lang['patient_phone'] ?? 'Patient Phone'; ?></label>
                        <input type="text" class="form-control" name="patient_phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="patient_name" class="form-label"><?php echo $lang['patient_name'] ?? 'Patient Name'; ?></label>
                        <input type="text" class="form-control" name="patient_name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="staff_id" class="form-label"><?php echo $lang['staff_id'] ?? 'Staff ID'; ?></label>
                        <input type="text" class="form-control" name="staff_id" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="department" class="form-label"><?php echo $lang['select_department'] ?? 'Department'; ?></label>
                        <select class="form-control" name="department" required>
                            <option>General Medicine</option>
                            <option>Emergency</option>
                            <option>Maternal Health</option>
                            <option>Pediatrics</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <?php echo $lang['book_for_patient'] ?? 'Book Token'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php';?>