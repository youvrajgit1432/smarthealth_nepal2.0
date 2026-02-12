<?php
/**
 * Chronic Disease Tracking Page
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/TrackingController.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';

$authController = new AuthController($db);
if (!$authController->isLoggedIn()) {
    header('Location: /smarthealth_nepal/frontend/views/auth/login.php');
    exit;
}

$trackingController = new TrackingController($db);
$userId = $_SESSION['user_id'];

// Load language
$lang_file = __DIR__ . '/../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Get chronic diseases
$result = $trackingController->getChronicDiseases($userId);
$diseases = $result['diseases'] ?? [];

// Handle add disease
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $trackingController->addChronicDisease($userId, $_POST['disease_name'], $_POST['disease_code'], $_POST['diagnosis_date']);
    header('Refresh:0');
}

$pageTitle = $lang['my_chronic_diseases'] ?? 'Chronic Disease Tracking';
$activePage = 'chronic';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-10">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-heartbeat"></i>
                            <?php echo $lang['my_chronic_diseases'] ?? 'My Chronic Diseases'; ?>
                        </h5>
                    </div>
                    <div class="col text-end">
                        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addDiseaseModal">
                            <i class="fas fa-plus"></i> Add Disease
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                
                <?php if (empty($diseases)): ?>
                <div class="alert alert-info text-center">
                    <p><?php echo $lang['no_chronic_diseases'] ?? 'No chronic diseases registered'; ?></p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiseaseModal">
                        <i class="fas fa-plus-circle"></i> <?php echo $lang['add_disease'] ?? 'Add Your First Chronic Disease'; ?>
                    </button>
                </div>
                <?php else: ?>
                
                <div class="row">
                    <?php foreach ($diseases as $disease): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card border-left-<?php echo time() > strtotime($disease['next_followup_date']) ? 'danger' : 'success'; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $disease['disease_name']; ?></h5>
                                
                                <div class="mb-2">
                                    <small class="text-muted"><?php echo $lang['diagnosed_date'] ?? 'Diagnosed:'; ?></small><br>
                                    <?php echo date('M d, Y', strtotime($disease['diagnosis_date'])); ?>
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-muted"><?php echo $lang['last_visit'] ?? 'Last Visit:'; ?></small><br>
                                    <?php echo $disease['last_visit_date'] ? date('M d, Y', strtotime($disease['last_visit_date'])) : 'Never'; ?>
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-muted"><?php echo $lang['next_followup'] ?? 'Next Follow-up:'; ?></small><br>
                                    <?php 
                                    $daysUntil = (int)((strtotime($disease['next_followup_date']) - time()) / 86400);
                                    if ($daysUntil < 0) {
                                        echo '<span class="badge bg-danger">Overdue by ' . abs($daysUntil) . ' days</span>';
                                    } elseif ($daysUntil <= 7) {
                                        echo '<span class="badge bg-warning">Due soon (' . $daysUntil . ' days)</span>';
                                    } else {
                                        echo date('M d, Y', strtotime($disease['next_followup_date']));
                                    }
                                    ?>
                                </div>
                                
                                <div class="d-grid gap-2 mt-3">
                                    <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-sm btn-primary">
                                        <?php echo $lang['book_followup'] ?? 'Book Follow-up'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Disease Modal -->
<div class="modal fade" id="addDiseaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Chronic Disease</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="disease_name" class="form-label">Disease Name</label>
                        <select class="form-control" name="disease_name" required>
                            <option value="">Select...</option>
                            <option value="Diabetes">Diabetes</option>
                            <option value="Hypertension">Hypertension</option>
                            <option value="Asthma">Asthma</option>
                            <option value="COPD">COPD</option>
                            <option value="Kidney Disease">Kidney Disease</option>
                            <option value="Heart Disease">Heart Disease</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="disease_code" class="form-label">Disease Code (ICD-10)</label>
                        <input type="text" class="form-control" name="disease_code" placeholder="E10, I10, etc.">
                    </div>
                    
                    <div class="mb-3">
                        <label for="diagnosis_date" class="form-label">Diagnosis Date</label>
                        <input type="date" class="form-control" name="diagnosis_date" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Disease</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php';?>
