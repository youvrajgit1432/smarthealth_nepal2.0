<?php
/**
 * Token Booking Page with Triage
 */

// Set language if switching
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load backend
require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/TokenController.php';
require_once __DIR__ . '/../../../backend/controllers/AuthController.php';

// Check if logged in
$authController = new AuthController($db);
if (!$authController->isLoggedIn()) {
    header('Location: /smarthealth_nepal/frontend/views/auth/login.php');
    exit;
}

// Initialize controller
$tokenController = new TokenController($db);

// Get current user
$user = $authController->getCurrentUser();
$userId = $_SESSION['user_id'] ?? null;

// Load language
$lang_file = __DIR__ . '/../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

// Handle form submission
$bookingResponse = null;
$step = isset($_POST['step']) ? $_POST['step'] : 'select_dept';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'book_token') {
    $departmentId = $_POST['department_id'] ?? null;
    
    // Collect triage data
    $triageData = [
        'have_fever' => isset($_POST['have_fever']),
        'fever_days' => $_POST['fever_days'] ?? 0,
        'difficulty_breathing' => isset($_POST['difficulty_breathing']),
        'any_injury' => isset($_POST['any_injury']),
        'injury_severity' => $_POST['injury_severity'] ?? '',
        'are_pregnant' => isset($_POST['are_pregnant']),
        'chronic_disease' => isset($_POST['chronic_disease']),
        'chronic_disease_names' => isset($_POST['chronic_disease_names']) ? explode(',', $_POST['chronic_disease_names']) : [],
        'emergency_signs' => isset($_POST['emergency_signs']),
        'additional_notes' => $_POST['additional_notes'] ?? ''
    ];
    
    // Book token
    $bookingResponse = $tokenController->bookToken($userId, $departmentId, $triageData);
    
    if ($bookingResponse['success']) {
        $_SESSION['booking_success'] = true;
        $_SESSION['token_data'] = $bookingResponse;
        header('Location: /smarthealth_nepal/frontend/views/token/confirmation.php');
        exit;
    }
}

// Get departments list
$deptList = $tokenController->getDepartmentsList();

$pageTitle = $lang['book_new_token'] ?? 'Book Token';
$activePage = 'book';

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-ticket-alt"></i>
                    <?php echo $lang['book_new_token'] ?? 'Book New Token'; ?>
                </h5>
            </div>
            
            <div class="card-body p-4">
                
                <?php if ($bookingResponse && !$bookingResponse['success']): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $bookingResponse['message']; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="triageForm">
                    <input type="hidden" name="step" value="book_token">
                    
                    <!-- Step 1: Select Department -->
                    <fieldset class="mb-4">
                        <legend class="mb-3">
                            <h6 class="badge bg-info"><?php echo $lang['select_department'] ?? 'Step 1: Select Department'; ?></h6>
                        </legend>
                        
                        <div class="row">
                            <?php foreach ($deptList['departments'] as $dept): ?>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="department_id"
                                           id="dept_<?php echo $dept['id']; ?>" value="<?php echo $dept['id']; ?>"
                                           <?php echo $deptList['departments'][0]['id'] == $dept['id'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="dept_<?php echo $dept['id']; ?>">
                                        <strong><?php echo $_SESSION['language'] === 'ne' ? $dept['name_ne'] : $dept['name']; ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            Load: <span class="badge bg-<?php 
                                                echo $dept['load'] === 'High' ? 'danger' : ($dept['load'] === 'Moderate' ? 'warning' : 'success');
                                            ?>">
                                                <?php echo $dept['load']; ?>
                                            </span>
                                        </small>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    
                    <hr>
                    
                    <!-- Step 2: Health Triage -->
                    <fieldset class="mb-4">
                        <legend class="mb-3">
                            <h6 class="badge bg-info"><?php echo $lang['triage_assessment'] ?? 'Step 2: Health Assessment'; ?></h6>
                        </legend>
                        
                        <p class="text-muted"><?php echo $lang['questionnaire'] ?? 'Please answer the following questions:'; ?></p>
                        
                        <!-- Fever -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="have_fever" id="fever" onchange="toggleFeverDays()">
                                <label class="form-check-label" for="fever">
                                    <strong><?php echo $lang['have_fever'] ?? 'Do you have fever?'; ?></strong>
                                </label>
                            </div>
                            <div id="feverDays" class="mt-2" style="display:none;">
                                <label for="feverDuration" class="form-label"><?php echo $lang['fever_days'] ?? 'How many days?'; ?></label>
                                <input type="number" class="form-control" id="feverDuration" name="fever_days" min="1" max="30">
                            </div>
                        </div>
                        
                        <!-- Breathing -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="difficulty_breathing" id="breathing">
                                <label class="form-check-label" for="breathing">
                                    <strong><?php echo $lang['difficulty_breathing'] ?? 'Difficulty breathing?'; ?></strong>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Injury -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="any_injury" id="injury" onchange="toggleInjurySeverity()">
                                <label class="form-check-label" for="injury">
                                    <strong><?php echo $lang['any_injury'] ?? 'Any injury?'; ?></strong>
                                </label>
                            </div>
                            <div id="injurySeverity" class="mt-2" style="display:none;">
                                <label for="severity" class="form-label"><?php echo $lang['injury_severity'] ?? 'Severity'; ?></label>
                                <select class="form-control" name="injury_severity" id="severity">
                                    <option value="">Select...</option>
                                    <option value="Minor">Minor</option>
                                    <option value="Moderate">Moderate</option>
                                    <option value="Severe">Severe</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Pregnancy -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="are_pregnant" id="pregnant">
                                <label class="form-check-label" for="pregnant">
                                    <strong><?php echo $lang['are_pregnant'] ?? 'Pregnant?'; ?></strong>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Chronic Disease -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="chronic_disease" id="chronic" onchange="toggleChronicList()">
                                <label class="form-check-label" for="chronic">
                                    <strong><?php echo $lang['chronic_disease'] ?? 'Chronic disease?'; ?></strong>
                                </label>
                            </div>
                            <div id="chronicList" class="mt-2" style="display:none;">
                                <label for="diseases" class="form-label"><?php echo $lang['select_disease'] ?? 'Select disease:'; ?></label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chronic_diseases" value="Diabetes" id="diabetes">
                                        <label class="form-check-label" for="diabetes"><?php echo $lang['diabetes'] ?? 'Diabetes'; ?></label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chronic_diseases" value="Hypertension" id="hypertension">
                                        <label class="form-check-label" for="hypertension"><?php echo $lang['hypertension'] ?? 'Hypertension'; ?></label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="chronic_diseases" value="Respiratory" id="respiratory">
                                        <label class="form-check-label" for="respiratory"><?php echo $lang['respiratory'] ?? 'Respiratory Disease'; ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Emergency Signs -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="emergency_signs" id="emergency">
                                <label class="form-check-label" for="emergency">
                                    <strong class="text-danger"><?php echo $lang['emergency_signs'] ?? 'Emergency signs?'; ?></strong>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Additional Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?php echo $lang['additional_notes'] ?? 'Additional information:'; ?></label>
                            <textarea class="form-control" name="additional_notes" id="notes" rows="3"></textarea>
                        </div>
                    </fieldset>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> <?php echo $lang['proceed'] ?? 'Proceed'; ?>
                        </button>
                        <a href="/smarthealth_nepal/frontend/views/home/" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> <?php echo $lang['cancel'] ?? 'Cancel'; ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFeverDays() {
    document.getElementById('feverDays').style.display = 
        document.getElementById('fever').checked ? 'block' : 'none';
}

function toggleInjurySeverity() {
    document.getElementById('injurySeverity').style.display = 
        document.getElementById('injury').checked ? 'block' : 'none';
}

function toggleChronicList() {
    document.getElementById('chronicList').style.display = 
        document.getElementById('chronic').checked ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php';?>
