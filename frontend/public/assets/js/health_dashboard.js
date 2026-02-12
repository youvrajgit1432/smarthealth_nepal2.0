/**
 * Health Dashboard - Chronic Disease & Maternal Health Tracking
 * Manages follow-up reminders and health status visualization
 */

(function() {
    'use strict';
    
    /**
     * Initialize dashboard
     */
    function initDashboard() {
        loadChronicDiseases();
        loadMaternalStatus();
        setupRefreshTimer();
    }
    
    /**
     * Load chronic disease data
     */
    function loadChronicDiseases() {
        fetch('/smarthealth_nepal/backend/api/get_chronic_diseases.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderChronicDiseases(data.diseases);
                }
            })
            .catch(error => console.error('Error loading chronic diseases:', error));
    }
    
    /**
     * Render chronic disease cards
     */
    function renderChronicDiseases(diseases) {
        const container = document.getElementById('chronicDiseases');
        if (!container) return;
        
        if (diseases.length === 0) {
            container.innerHTML = '<div class="alert alert-info">No chronic diseases recorded</div>';
            return;
        }
        
        container.innerHTML = diseases.map(disease => `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-1">${escapeHtml(disease.disease_type)}</h6>
                            <small class="text-muted">
                                Diagnosed: ${formatDate(disease.diagnosis_date)}
                            </small>
                        </div>
                        <span class="badge bg-${getStatusColor(disease.status)}">
                            ${disease.status}
                        </span>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <small>Next Follow-up:</small>
                            <small><strong>${formatDate(disease.next_followup)}</strong></small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-${getDaysStatusColor(disease.next_followup)}" 
                                 style="width: ${getProgressPercentage(disease.next_followup)}%"></div>
                        </div>
                        <small class="text-muted">${getDaysUntilFollowup(disease.next_followup)} days remaining</small>
                    </div>
                    
                    <small class="mt-2 d-block">
                        Last Visit: ${disease.last_visit ? formatDate(disease.last_visit) : 'Not yet'}
                    </small>
                </div>
                <div class="card-footer bg-light">
                    <button class="btn btn-sm btn-primary" onclick="editFollowup(${disease.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-success" onclick="markCompleted(${disease.id})">
                        <i class="fas fa-check"></i> Mark Complete
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Load maternal health data
     */
    function loadMaternalStatus() {
        fetch('/smarthealth_nepal/backend/api/get_maternal_status.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.maternal) {
                    renderMaternalStatus(data.maternal);
                }
            })
            .catch(error => console.error('Error loading maternal status:', error));
    }
    
    /**
     * Render maternal health card
     */
    function renderMaternalStatus(maternal) {
        const container = document.getElementById('maternalStatus');
        if (!container) return;
        
        const weeksRemaining = getWeeksRemaining(maternal.due_date);
        const trimester = getTrimester(weeksRemaining);
        
        container.innerHTML = `
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-heart"></i> Pregnancy Tracking</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Due Date</h6>
                            <h5><?php echo date('d M Y', strtotime(${maternal.due_date})); ?></h5>
                            <small class="text-muted">${weeksRemaining} weeks remaining</small>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Trimester</h6>
                            <h5>${trimester}</h5>
                            <small class="text-muted">Pregnancy stage</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Antenatal Visits: ${maternal.antenatal_visits || 0}</h6>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar bg-success" style="width: ${(maternal.antenatal_visits / 4) * 100}%"></div>
                        </div>
                        <small class="text-muted">4 visits recommended</small>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Vaccination Status</h6>
                        <small class="d-block text-muted">${maternal.vaccination_status || 'Not recorded'}</small>
                    </div>
                    
                    <div class="alert alert-warning mb-0">
                        <h6 class="mb-2">⚠️ Warning Signs to Watch For:</h6>
                        <ul class="small mb-0">
                            <li>Severe headache or vision changes</li>
                            <li>Abdominal pain or cramps</li>
                            <li>Vaginal bleeding or fluid leakage</li>
                            <li>Severe swelling in face/hands</li>
                        </ul>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Get days until follow-up
     */
    function getDaysUntilFollowup(date) {
        const today = new Date();
        const followup = new Date(date);
        const diff = followup - today;
        return Math.ceil(diff / (1000 * 60 * 60 * 24));
    }
    
    /**
     * Get progress percentage for days until followup
     */
    function getProgressPercentage(date) {
        const days = getDaysUntilFollowup(date);
        return Math.min(100, Math.max(0, (30 - days) / 30 * 100));
    }
    
    /**
     * Get color based on days remaining
     */
    function getDaysStatusColor(date) {
        const days = getDaysUntilFollowup(date);
        if (days < 0) return 'danger';
        if (days < 7) return 'warning';
        return 'success';
    }
    
    /**
     * Get status badge color
     */
    function getStatusColor(status) {
        const colors = {
            'Active': 'danger',
            'Controlled': 'success',
            'In Progress': 'warning'
        };
        return colors[status] || 'secondary';
    }
    
    /**
     * Format date
     */
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }
    
    /**
     * Get weeks remaining in pregnancy
     */
    function getWeeksRemaining(dueDate) {
        const today = new Date();
        const due = new Date(dueDate);
        const weeks = Math.ceil((due - today) / (1000 * 60 * 60 * 24 * 7));
        return Math.max(0, weeks);
    }
    
    /**
     * Get trimester
     */
    function getTrimester(weeksRemaining) {
        const total = 40;
        const weeks = total - weeksRemaining;
        if (weeks < 13) return '1st Trimester (0-12 weeks)';
        if (weeks < 27) return '2nd Trimester (13-26 weeks)';
        return '3rd Trimester (27-40 weeks)';
    }
    
    /**
     * Escape HTML for safe display
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Mark follow-up as completed
     */
    window.markCompleted = function(diseaseId) {
        if (!confirm('Mark this follow-up as completed?')) return;
        
        fetch('/smarthealth_nepal/backend/api/mark_followup_completed.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'disease_id=' + diseaseId + '&completed_date=' + new Date().toISOString().split('T')[0]
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                loadChronicDiseases();
                showAlert('Follow-up marked as completed', 'success');
            }
        });
    };
    
    /**
     * Edit follow-up date
     */
    window.editFollowup = function(diseaseId) {
        const newDate = prompt('Enter new follow-up date (YYYY-MM-DD):');
        if (!newDate) return;
        
        fetch('/smarthealth_nepal/backend/api/update_followup.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'disease_id=' + diseaseId + '&next_date=' + newDate
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                loadChronicDiseases();
                showAlert('Follow-up date updated', 'success');
            } else {
                showAlert(d.message, 'danger');
            }
        });
    };
    
    /**
     * Show alert message
     */
    function showAlert(message, type = 'info') {
        const alertEl = document.createElement('div');
        alertEl.className = 'alert alert-' + type + ' alert-dismissible fade show';
        alertEl.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.insertBefore(alertEl, document.body.firstChild);
        setTimeout(() => alertEl.remove(), 5000);
    }
    
    /**
     * Setup auto-refresh timer
     */
    function setupRefreshTimer() {
        setInterval(function() {
            loadChronicDiseases();
            loadMaternalStatus();
        }, 300000); // Refresh every 5 minutes
    }
    
    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        initDashboard();
    }
})();
