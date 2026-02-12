<?php
require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/TokenController.php';
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) require_once $lang_file;
$tokenController = new TokenController($db);
$depts = $tokenController->getDepartmentsList();
$activePage = 'departments';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="row justify-content-center mt-4">
    <div class="col-lg-10">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-hospital"></i> Browse Departments</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <?php foreach ($depts['departments'] as $dept): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $dept['name']; ?></h5>
                                <p class="card-text"><?php echo $dept['description']; ?></p>
                                <p><small>Active Patients: <?php echo $dept['active_tokens']; ?></small></p>
                                <p>
                                    Load: <span class="badge bg-<?php echo $dept['load'] == 'High' ? 'danger' : ($dept['load'] == 'Moderate' ? 'warning' : 'success'); ?>"><?php echo $dept['load']; ?></span>
                                </p>
                                <?php if ($dept['can_book']): ?>
                                <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-sm btn-primary">Book Token</a>
                                <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>Full Capacity</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php';?>