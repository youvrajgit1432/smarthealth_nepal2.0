<?php
/**
 * Admin - Active Token Management
 * Display and manage active tokens in the queue
 */

require_once __DIR__ . '/../../../backend/init.php';
require_once __DIR__ . '/../../../backend/controllers/DashboardController.php';
require_once __DIR__ . '/../../../backend/models/TokenModel.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/frontend/views/auth/login.php');
    exit;
}

// Ensure language is loaded
if (!isset($lang)) {
    $lang = [];
}

global $db;
$dashboardController = new DashboardController($db);
$activeQueue = $dashboardController->getActiveQueue(50);

$pageTitle = $lang['active_tokens'] ?? 'Active Tokens';
$activePage = 'token_management';
?>

<?php require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<style>
    .page-header {
        margin-bottom: 2rem;
    }

    .page-header h2 {
        color: #0056b3;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card {
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .card-header {
        background: linear-gradient(135deg, #0056b3 0%, #003d99 100%);
        border: none;
        border-radius: 8px 8px 0 0;
        padding: 1.5rem;
    }

    .card-header h5 {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-body {
        padding: 0;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }

    .table thead th {
        color: #0056b3;
        font-weight: 600;
        border-bottom: 2px solid #0056b3;
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    .table tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #dee2e6;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f0f8ff;
    }

    .text-primary {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .badge {
        padding: 0.5rem 0.75rem;
        font-weight: 500;
        border-radius: 4px;
    }

    .badge.bg-danger {
        background-color: #dc3545 !important;
    }

    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #333;
    }

    .badge.bg-info {
        background-color: #17a2b8 !important;
    }

    .badge.bg-secondary {
        background-color: #6c757d !important;
    }

    .badge.bg-success {
        background-color: #28a745 !important;
    }

    .btn-group-sm {
        display: flex;
        gap: 0.25rem;
        align-items: center;
    }

    .btn-group-sm .btn {
        padding: 0.35rem 0.65rem;
        font-size: 0.85rem;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .btn-group-sm .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .btn-group-sm .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #333;
    }

    .btn-group-sm .btn-warning:hover {
        background-color: #e0a800;
        border-color: #e0a800;
    }

    .btn-group-sm .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-group-sm .btn-success:hover {
        background-color: #218838;
        border-color: #218838;
    }

    .btn-group-sm .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-group-sm .btn-danger:hover {
        background-color: #c82333;
        border-color: #c82333;
    }

    .alert {
        margin: 1.5rem;
        border-radius: 6px;
        border: none;
    }

    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    @media (max-width: 768px) {
        .table {
            font-size: 0.9rem;
        }

        .table thead th {
            padding: 0.6rem 0.4rem;
        }

        .table tbody td {
            padding: 0.6rem 0.4rem;
        }

        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-group-sm {
            flex-wrap: wrap;
        }
    }
</style>

<div class="page-header">
    <h2><i class="fas fa-list"></i> <?php echo $lang['active_tokens'] ?? 'Active Tokens'; ?></h2>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-users-cog"></i> Current Queue Status
        </h5>
    </div>
    
    <div class="card-body">
        <?php if (empty($activeQueue)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> <?php echo $lang['no_active_tokens'] ?? 'No active tokens'; ?>
        </div>
        <?php else: ?>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th><i class="fas fa-hashtag"></i> <?php echo $lang['token'] ?? 'Token'; ?></th>
                        <th><i class="fas fa-exclamation"></i> <?php echo $lang['priority'] ?? 'Priority'; ?></th>
                        <th><i class="fas fa-user"></i> <?php echo $lang['patient_name'] ?? 'Patient'; ?></th>
                        <th><i class="fas fa-phone"></i> <?php echo $lang['phone'] ?? 'Phone'; ?></th>
                        <th><i class="fas fa-clinic-medical"></i> <?php echo $lang['department'] ?? 'Department'; ?></th>
                        <th><i class="fas fa-hourglass-end"></i> <?php echo $lang['wait_time'] ?? 'Wait Time'; ?></th>
                        <th><i class="fas fa-info"></i> <?php echo $lang['status'] ?? 'Status'; ?></th>
                        <th><i class="fas fa-cog"></i> <?php echo $lang['actions'] ?? 'Actions'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeQueue as $token): ?>
                    <tr>
                        <td>
                            <strong class="text-primary"><?php echo $token['token_number']; ?></strong>
                        </td>
                        <td>
                            <?php 
                            $priority_color = match($token['priority']) {
                                'Emergency' => 'danger',
                                'Priority' => 'warning',
                                'Chronic' => 'info',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?php echo $priority_color; ?>">
                                <?php echo $token['priority']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($token['name']); ?></td>
                        <td><a href="tel:<?php echo htmlspecialchars($token['phone']); ?>"><?php echo htmlspecialchars($token['phone']); ?></a></td>
                        <td><?php echo htmlspecialchars($token['dept_name']); ?></td>
                        <td>
                            <span class="badge bg-secondary"><?php echo $token['wait_time']; ?> min</span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $token['status'] === 'Called' ? 'danger' : 'success'; ?>">
                                <?php echo $token['status']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <?php if ($token['status'] === 'Active'): ?>
                                <button type="button" class="btn btn-warning btn-sm" title="Call Patient to Counter" onclick="callToken(<?php echo $token['id']; ?>)">
                                    <i class="fas fa-phone"></i> Call
                                </button>
                                <?php else: ?>
                                <span class="badge bg-secondary"><i class="fas fa-phone-slash"></i> Called</span>
                                <?php endif; ?>
                                
                                <button type="button" class="btn btn-success btn-sm" title="Mark as Complete" onclick="completeToken(<?php echo $token['id']; ?>)">
                                    <i class="fas fa-check"></i> Done
                                </button>
                                
                                <button type="button" class="btn btn-danger btn-sm" title="Mark as Missed" onclick="missToken(<?php echo $token['id']; ?>)">
                                    <i class="fas fa-times"></i> Miss
                                </button>
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

<style>
    .alert-notification {
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 2000;
        animation: slideIn 0.3s ease;
        max-width: 400px;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    .alert-notification.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-notification.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .alert-notification.info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .alert-notification.loading {
        background: #e2e3e5;
        color: #383d41;
        border: 1px solid #d6d8db;
    }
    
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        border-top-color: currentColor;
        animation: spin 0.8s linear infinite;
        margin-right: 0.5rem;
        vertical-align: middle;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<script>
    let alertTimeout;
    
    function showAlert(message, type = 'info') {
        const container = document.getElementById('alertContainer') || createAlertContainer();
        
        const alert = document.createElement('div');
        alert.className = `alert-notification ${type}`;
        alert.innerHTML = message;
        
        container.appendChild(alert);
        
        clearTimeout(alertTimeout);
        alertTimeout = setTimeout(() => {
            alert.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    }
    
    function createAlertContainer() {
        const container = document.createElement('div');
        container.id = 'alertContainer';
        document.body.appendChild(container);
        return container;
    }
    
    function callToken(tokenId) {
        if (!confirm('Ready to call this patient to counter?')) return;
        
        tokenAction(tokenId, '/smarthealth_nepal/admin/api/call_token.php', 'Patient called successfully!', 'Token called');
    }
    
    function completeToken(tokenId) {
        if (!confirm('Mark this token as completed?')) return;
        
        tokenAction(tokenId, '/smarthealth_nepal/admin/api/complete_token.php', 'Token completed successfully!', 'Token completed');
    }
    
    function missToken(tokenId) {
        if (!confirm('Are you sure you want to mark this token as missed?')) return;
        
        tokenAction(tokenId, '/smarthealth_nepal/admin/api/miss_token.php', 'Token marked as missed!', 'Token missed');
    }
    
    function tokenAction(tokenId, endpoint, successMessage, actionType) {
        showAlert(`<span class="loading-spinner"></span> Processing ${actionType}...`, 'loading');
        
        const formData = new FormData();
        formData.append('token_id', tokenId);
        
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(`<i class="fas fa-check-circle"></i> ${successMessage}`, 'success');
                // Refresh the page after 1 second
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(`<i class="fas fa-exclamation-circle"></i> Error: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert(`<i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.`, 'error');
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
