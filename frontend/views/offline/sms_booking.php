<?php
/**
 * SMS Booking Instructions Page
 */

require_once __DIR__ . '/../../../backend/init.php';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sms"></i>
                    SMS Booking Instructions
                </h5>
            </div>
            
            <div class="card-body p-4">
                <div class="alert alert-success">
                    <h6>How to book via SMS:</h6>
                    <ol>
                        <li>Send an SMS to: 9841000000</li>
                        <li>Write: BOOK FEVER (or INJURY, BREATHING, etc.)</li>
                        <li>Receive token number via SMS</li>
                    </ol>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>Available SMS Commands:</strong>
                    </div>
                    <div class="card-body">
                        <code>BOOK FEVER</code> - For fever complaints<br>
                        <code>BOOK INJURY</code> - For injuries<br>
                        <code>BOOK BREATHING</code> - For breathing difficulty<br>
                        <code>BOOK EMERGENCY</code> - For emergency cases<br>
                        <code>BOOK CHRONIC</code> - For chronic disease check-up<br>
                        <code>BOOK MATERNITY</code> - For maternal checkup
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <strong>Note:</strong> SMS booking is available for all users, even those without internet connection.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php';?>