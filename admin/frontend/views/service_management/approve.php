<?php
/**
 * Admin - Service Approval
 * Approve or reject pending services
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/ServiceController.php';

// Check admin login
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /smarthealth_nepal/admin/public/index.php?page=login');
    exit;
}

// Load admin language
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) {
    require_once $lang_file;
}

$serviceController = new ServiceController($db);
$services = $serviceController->getAllServices('Active');

$pageTitle = $lang['approve_services'] ?? 'Approve Services';
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle"></i> <?php echo $lang['approve_services'] ?? 'Approve Services'; ?>
                    </h5>
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($services)): ?>
                    <div class="alert alert-info m-3">
                        <?php echo $lang['no_services'] ?? 'No services available'; ?>
                    </div>
                    <?php else: ?>
                    
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?php echo $lang['service_name'] ?? 'Service Name'; ?></th>
                                    <th><?php echo $lang['department'] ?? 'Department'; ?></th>
                                    <th><?php echo $lang['description'] ?? 'Description'; ?></th>
                                    <th><?php echo $lang['status'] ?? 'Status'; ?></th>
                                    <th><?php echo $lang['actions'] ?? 'Actions'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($service['service_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($service['dept_name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($service['description'], 0, 50)); ?></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo $service['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" 
                                                    data-bs-target="#editModal" onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            
                                            <form method="POST" action="/smarthealth_nepal/admin/api/delete_service.php" 
                                                  style="display:inline;">
                                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Delete this service?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $lang['edit_service'] ?? 'Edit Service'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/smarthealth_nepal/admin/api/update_service.php">
                <div class="modal-body">
                    <input type="hidden" name="service_id" id="serviceId">
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo $lang['service_name'] ?? 'Service Name'; ?></label>
                        <input type="text" name="service_name" class="form-control" id="serviceName">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo $lang['description'] ?? 'Description'; ?></label>
                        <textarea name="description" class="form-control" rows="4" id="serviceDesc"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo $lang['status'] ?? 'Status'; ?></label>
                        <select name="status" class="form-select" id="serviceStatus">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editService(service) {
    document.getElementById('serviceId').value = service.id;
    document.getElementById('serviceName').value = service.service_name;
    document.getElementById('serviceDesc').value = service.description;
    document.getElementById('serviceStatus').value = service.status;
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
