/**
 * Admin Dashboard Interactivity
 * Handles admin panel interactions, modals, and dynamic updates
 */

// Auto-refresh dashboard every 10 seconds
const DASHBOARD_REFRESH_INTERVAL = 10000;
const ALERT_DISMISS_TIME = 5000;

// Initialize admin dashboard
document.addEventListener('DOMContentLoaded', function() {
    initAdminDashboard();
});

function initAdminDashboard() {
    setupModalHandlers();
    setupTableActions();
    setupFormValidation();
    startDashboardAutoRefresh();
}

/**
 * Modal and Form Handlers
 */
function setupModalHandlers() {
    // Edit buttons - populate modal with item data
    document.querySelectorAll('[data-edit-btn]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const itemId = this.getAttribute('data-id');
            const modalId = this.getAttribute('data-modal') || 'editModal';
            const dataAttr = this.getAttribute('data-json');
            
            if (dataAttr) {
                try {
                    const itemData = JSON.parse(dataAttr);
                    populateModal(modalId, itemData);
                    showModal(modalId);
                } catch(e) {
                    console.error('Failed to parse item data:', e);
                }
            }
        });
    });
    
    // Delete buttons - confirm action
    document.querySelectorAll('[data-delete-btn]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const itemName = this.getAttribute('data-name') || 'this item';
            
            if (confirm('Are you sure you want to delete ' + itemName + '? This action cannot be undone.')) {
                deleteItem(this.getAttribute('data-action'), this.getAttribute('data-id'));
            }
        });
    });
    
    // Close modal buttons
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.getAttribute('data-close-modal');
            hideModal(modalId);
        });
    });
}

function populateModal(modalId, data) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    // Populate form fields with data attributes matching input names
    Object.keys(data).forEach(key => {
        const input = modal.querySelector('[name="' + key + '"]');
        if (input) {
            if (input.type === 'checkbox') {
                input.checked = data[key];
            } else {
                input.value = data[key];
            }
        }
    });
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal && typeof bootstrap !== 'undefined') {
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal && typeof bootstrap !== 'undefined') {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    }
}

/**
 * Form Submission Handlers
 */
function setupFormValidation() {
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAjaxForm(this);
        });
    });
}

function submitAjaxForm(form) {
    const formData = new FormData(form);
    const action = form.getAttribute('data-ajax');
    const successMsg = form.getAttribute('data-success') || 'Operation completed successfully';
    const modalToClose = form.getAttribute('data-close-modal');
    
    // Show loading state
    const submitBtn = form.querySelector('[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : 'Submit';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    }
    
    fetch(action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', successMsg);
            form.reset();
            
            if (modalToClose) {
                hideModal(modalToClose);
            }
            
            // Reload page or refresh table after delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('danger', data.message || 'Operation failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Network error: ' + error.message);
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

/**
 * Delete operations via AJAX
 */
function deleteItem(action, itemId) {
    // Show loading state
    const deleteBtn = document.querySelector('[data-id="' + itemId + '"]');
    const originalHtml = deleteBtn ? deleteBtn.innerHTML : '';
    
    if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    const formData = new FormData();
    formData.append(itemId.includes('user') ? 'user_id' : (itemId.includes('dept') ? 'dept_id' : 'service_id'), itemId);
    
    fetch(action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Item deleted successfully');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert('danger', data.message || 'Failed to delete item');
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalHtml;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Network error: ' + error.message);
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalHtml;
        }
    });
}

/**
 * Token Management Actions
 */
function callToken(tokenId) {
    performTokenAction('admin/backend/api/call_token.php', tokenId, 'Token called successfully');
}

function completeToken(tokenId) {
    performTokenAction('admin/backend/api/complete_token.php', tokenId, 'Token marked as completed');
}

function missToken(tokenId) {
    performTokenAction('admin/backend/api/miss_token.php', tokenId, 'Token marked as missed');
}

function performTokenAction(action, tokenId, successMsg) {
    const btn = document.querySelector('[data-token-id="' + tokenId + '"]');
    const originalHtml = btn ? btn.innerHTML : '';
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    const formData = new FormData();
    formData.append('token_id', tokenId);
    
    fetch('/smarthealth_nepal/' + action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', successMsg);
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert('danger', data.message || 'Operation failed');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Network error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });
}

/**
 * Referral Management Actions
 */
function approveReferral(referralId) {
    performReferralAction('admin/backend/api/approve_referral.php', referralId, 'Referral approved successfully');
}

function rejectReferral(referralId) {
    const reason = prompt('Enter rejection reason:');
    if (reason === null) return;
    
    if (!reason.trim()) {
        showAlert('warning', 'Rejection reason is required');
        return;
    }
    
    const formData = new FormData();
    formData.append('referral_id', referralId);
    formData.append('reason', reason);
    
    const btn = document.querySelector('[data-reject-referral="' + referralId + '"]');
    const originalHtml = btn ? btn.innerHTML : '';
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    fetch('/smarthealth_nepal/admin/backend/api/reject_referral.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Referral rejected');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert('danger', data.message || 'Operation failed');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Network error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });
}

function forwardReferral(referralId) {
    const toHospital = prompt('Enter destination hospital name:');
    if (toHospital === null) return;
    
    if (!toHospital.trim()) {
        showAlert('warning', 'Hospital name is required');
        return;
    }
    
    const notes = prompt('Enter forwarding notes (optional):');
    
    const formData = new FormData();
    formData.append('referral_id', referralId);
    formData.append('to_hospital', toHospital);
    formData.append('notes', notes || '');
    
    const btn = document.querySelector('[data-forward-referral="' + referralId + '"]');
    const originalHtml = btn ? btn.innerHTML : '';
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    fetch('/smarthealth_nepal/admin/backend/api/forward_referral.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Referral forwarded successfully');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert('danger', data.message || 'Operation failed');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Network error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });
}

function performReferralAction(action, referralId, successMsg) {
    const btn = document.querySelector('[data-referral-id="' + referralId + '"]');
    const originalHtml = btn ? btn.innerHTML : '';
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    const formData = new FormData();
    formData.append('referral_id', referralId);
    
    fetch('/smarthealth_nepal/' + action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', successMsg);
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert('danger', data.message || 'Operation failed');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Network error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });
}

/**
 * Table Actions
 */
function setupTableActions() {
    // Inline delete with confirmation
    document.querySelectorAll('[data-inline-delete]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const itemName = this.getAttribute('data-name') || 'item';
            if (confirm('Delete ' + itemName + '?')) {
                deleteItem(this.getAttribute('data-action'), this.getAttribute('data-id'));
            }
        });
    });
    
    // Toggle row selection
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            document.querySelectorAll('[data-row-select]').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
}

/**
 * Auto-refresh Dashboard
 */
function startDashboardAutoRefresh() {
    // Check if we're on dashboard
    if (!document.body.classList.contains('dashboard')) return;
    
    setInterval(refreshDashboardStats, DASHBOARD_REFRESH_INTERVAL);
}

function refreshDashboardStats() {
    // Fetch updated stats and refresh display
    fetch('/smarthealth_nepal/admin/backend/api/get_dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardStats(data);
            }
        })
        .catch(error => console.error('Dashboard refresh error:', error));
}

function updateDashboardStats(data) {
    // Update statistics cards
    const stats = data.statistics || {};
    
    if (stats.active_tokens !== undefined) {
        updateStat('activeTokens', stats.active_tokens);
    }
    if (stats.completed_today !== undefined) {
        updateStat('completedToday', stats.completed_today);
    }
    if (stats.missed_today !== undefined) {
        updateStat('missedToday', stats.missed_today);
    }
    if (stats.emergency_count !== undefined) {
        updateStat('emergencyCount', stats.emergency_count);
    }
}

function updateStat(statId, value) {
    const element = document.querySelector('[data-stat="' + statId + '"]');
    if (element && element.textContent !== String(value)) {
        element.textContent = value;
        // Add animation class
        element.classList.add('stat-updated');
        setTimeout(() => element.classList.remove('stat-updated'), 1000);
    }
}

/**
 * Alerts and Notifications
 */
function showAlert(type, message) {
    // Create alert element
    const alertId = 'alert-' + Date.now();
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert at top of page
    const alertContainer = document.querySelector('.alert-container') || document.querySelector('main');
    if (alertContainer) {
        const alertDiv = document.createElement('div');
        alertDiv.innerHTML = alertHTML;
        alertContainer.insertBefore(alertDiv.firstElementChild, alertContainer.firstChild);
        
        // Auto-dismiss after delay
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, ALERT_DISMISS_TIME);
    }
}

/**
 * Utility Functions
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
}

// Expose key functions globally for inline HTML handlers
window.callToken = callToken;
window.completeToken = completeToken;
window.missToken = missToken;
window.approveReferral = approveReferral;
window.rejectReferral = rejectReferral;
window.forwardReferral = forwardReferral;
window.deleteItem = deleteItem;
window.showAlert = showAlert;
