<?php
/**
 * Terms & Conditions
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$langFile = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($langFile)) {
    require_once $langFile;
}

$pageTitle = 'Terms & Conditions';
$activePage = '';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-2">Terms &amp; Conditions</h1>
            <p class="text-muted">Last updated: 2026</p>

            <div class="alert alert-warning">
                <strong>Demo notice:</strong> SmartHealth Nepal is a demonstration project. It is
                <strong>not</strong> a substitute for professional medical advice and must not be
                relied upon for real clinical decisions.
            </div>

            <h5 class="mt-4">1. Acceptance</h5>
            <p>By using this application you agree to these terms. If you do not agree, please do not use the system.</p>

            <h5 class="mt-4">2. No medical advice</h5>
            <p>
                The pre-triage questions and priority classifications are decision-support aids only.
                They do not diagnose conditions and do not replace assessment by a qualified health
                professional. In a real emergency, contact emergency services directly.
            </p>

            <h5 class="mt-4">3. Accurate information</h5>
            <p>You agree to provide accurate contact and booking details so hospital staff can reach you.</p>

            <h5 class="mt-4">4. Acceptable use</h5>
            <ul>
                <li>Do not attempt to gain unauthorised access to any portal.</li>
                <li>Do not submit abusive, false or unlawful content.</li>
                <li>Do not load the system in a way that disrupts service for others.</li>
            </ul>

            <h5 class="mt-4">5. Demo data &amp; availability</h5>
            <p>
                All data in this environment is fictional. The service is provided “as is”, without
                warranty, and may be reset or unavailable at any time.
            </p>

            <h5 class="mt-4">6. Changes</h5>
            <p>These terms may be updated as the project evolves. Continued use constitutes acceptance of any changes.</p>

            <a href="/smarthealth_nepal/frontend/views/home/" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left"></i> Back to home
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
