<?php
require_once __DIR__ . '/../../../backend/init.php';
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) require_once $lang_file;
$activePage = 'referral';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-exchange-alt"></i> Referral Service</h5>
            </div>
            <div class="card-body p-4">
                <p>If your assigned hospital is at full capacity or doesn't have the required specialist, we can refer you to the nearest available hospital.</p>
                <div class="alert alert-info">Our system will automatically suggest the nearest hospital with available services and lower load.</div>
                <div class="d-grid gap-2">
                    <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-primary btn-lg">Book Referral</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php';?>