<?php
/**
 * Privacy Policy
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$langFile = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($langFile)) {
    require_once $langFile;
}

$pageTitle = 'Privacy Policy';
$activePage = '';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-2">Privacy Policy</h1>
            <p class="text-muted">Last updated: 2026</p>

            <div class="alert alert-warning">
                <strong>Demo notice:</strong> This deployment is a local demonstration using
                <strong>fictional data only</strong>. Do not enter real medical information into it.
            </div>

            <h5 class="mt-4">1. Information we collect</h5>
            <p>To provide the queue and health-tracking features, the system stores:</p>
            <ul>
                <li>Contact details you provide (name and phone number)</li>
                <li>Profile details such as age, gender and district</li>
                <li>Triage responses and booking history</li>
                <li>Any health-tracking information you choose to record</li>
            </ul>

            <h5 class="mt-4">2. How we use it</h5>
            <p>
                Information is used only to issue tokens, estimate waiting times, send booking
                confirmations, and display your history back to you. Hospital staff can see the
                queue and bookings relevant to their own hospital.
            </p>

            <h5 class="mt-4">3. Sharing</h5>
            <p>
                In this demo, data stays inside the local database. No information is shared with
                third parties and no real SMS provider is contacted by default (SMS is written to a
                local log unless a provider is configured).
            </p>

            <h5 class="mt-4">4. Security</h5>
            <p>
                Passwords are stored using one-way bcrypt hashing. Access to the admin and hospital
                panels is protected by authenticated sessions. The demo accounts use well-known
                credentials and must not be used for anything sensitive.
            </p>

            <h5 class="mt-4">5. Your choices</h5>
            <p>
                Because this is a demonstration environment, data can be removed at any time by
                re-importing the canonical database file (<code>database/smarthealth.sql</code>).
            </p>

            <a href="/smarthealth_nepal/frontend/views/home/" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left"></i> Back to home
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
