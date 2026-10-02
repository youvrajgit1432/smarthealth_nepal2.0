<?php
/**
 * Contact Us
 * ----------
 * Stores contact enquiries in the `contact_messages` table. No email is sent
 * (there is no mail provider configured for this demo), and the page says so
 * honestly instead of pretending a message was emailed.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../backend/config/database.php';
require_once __DIR__ . '/../../../backend/helpers/CsrfHelper.php';

$langFile = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($langFile)) {
    require_once $langFile;
}

$pageTitle = 'Contact';
$activePage = '';

$errors = [];
$success = false;
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['subject'] = trim($_POST['subject'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');

    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    if ($old['name'] === '') {
        $errors[] = 'Please enter your name.';
    }
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($old['message'] === '' || mb_strlen($old['message']) < 5) {
        $errors[] = 'Please enter a message.';
    }

    if (!$errors) {
        try {
            $stmt = $db->prepare(
                "INSERT INTO contact_messages (name, email, subject, message, status)
                 VALUES (?, ?, ?, ?, 'New')"
            );
            $subject = $old['subject'] !== '' ? $old['subject'] : null;
            $stmt->bind_param('ssss', $old['name'], $old['email'], $subject, $old['message']);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();
            $success = true;
            $old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
        } catch (Exception $e) {
            $errors[] = 'Sorry, your message could not be saved. Please try again later.';
        }
    }
}

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-3">Contact Us</h1>
            <p class="text-muted">
                Questions, feedback or bug reports? Send us a message and it will be recorded in
                this local demo instance.
            </p>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <strong>Thank you!</strong> Your message has been recorded.
                    <span class="d-block small mt-1">This demo does not send email — your message is
                    stored in the application database for review.</span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-md-7">
                    <form method="POST" novalidate class="card shadow-sm">
                        <div class="card-body">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label for="name" class="form-label">Your name *</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       maxlength="100" required value="<?php echo htmlspecialchars($old['name']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       maxlength="150" required value="<?php echo htmlspecialchars($old['email']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject"
                                       maxlength="150" value="<?php echo htmlspecialchars($old['subject']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5"
                                          required><?php echo htmlspecialchars($old['message']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send message
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-5">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Reach us</h6>
                            <p class="mb-1"><i class="fas fa-phone text-primary"></i> +977-9854634578</p>
                            <p class="mb-1"><i class="fas fa-envelope text-primary"></i> info@smarthealth.npl</p>
                            <p class="mb-0"><i class="fas fa-map-marker-alt text-primary"></i> Kathmandu, Nepal</p>
                        </div>
                    </div>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title">Emergency</h6>
                            <p class="mb-0 small">
                                This demo is not an emergency service. If you have a medical emergency,
                                contact your nearest hospital or emergency services immediately.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <a href="/smarthealth_nepal/frontend/views/home/" class="btn btn-secondary mt-4">
                <i class="fas fa-arrow-left"></i> Back to home
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
