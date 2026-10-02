<?php
/**
 * About Us
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$langFile = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($langFile)) {
    require_once $langFile;
}

$pageTitle = 'About';
$activePage = '';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-3">About SmartHealth Nepal</h1>
            <p class="lead">
                SmartHealth Nepal is a digital healthcare queue-management and records
                platform built to make hospital visits simpler, fairer and more transparent.
            </p>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Our Mission</h5>
                    <p class="card-text mb-0">
                        Reduce waiting-room chaos in busy hospitals by giving patients a clear
                        digital token, an honest estimate of their wait, and a single place to
                        track their health history — while giving hospital staff a dependable
                        operations dashboard.
                    </p>
                </div>
            </div>

            <h4 class="mt-4 mb-3">What the system does</h4>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h6><i class="fas fa-ticket-alt text-primary"></i> Queue &amp; Tokens</h6>
                            <p class="mb-0 small">Digital tokens with priority-based ordering, live
                            queue position and estimated wait times.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h6><i class="fas fa-stethoscope text-primary"></i> Digital Pre-triage</h6>
                            <p class="mb-0 small">Structured symptom questions route patients to the
                            right department and flag emergencies.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h6><i class="fas fa-heartbeat text-primary"></i> Chronic &amp; Maternal Care</h6>
                            <p class="mb-0 small">Follow-up tracking for chronic conditions and
                            antenatal visits for expectant mothers.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h6><i class="fas fa-globe text-primary"></i> Bilingual by default</h6>
                            <p class="mb-0 small">Full English and Nepali interface so patients can
                            use the system in the language they are comfortable with.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border mt-4">
                <strong>Project context:</strong> SmartHealth Nepal is a student/hackathon
                project and portfolio demonstration. All hospitals, patients and records shown
                in the demo data are fictional, and the system is not officially adopted by any
                government or hospital.
            </div>

            <a href="/smarthealth_nepal/frontend/views/home/" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left"></i> Back to home
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
