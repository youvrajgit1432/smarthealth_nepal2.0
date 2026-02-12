/**
 * Token Status Real-time Tracking
 * Refreshes token status, queue position, and wait time every 30 seconds
 */

(function() {
    'use strict';
    
    const TOKEN_REFRESH_INTERVAL = 30000; // 30 seconds
    let refreshTimer = null;
    
    /**
     * Initialize token status tracking
     */
    function initTokenStatus() {
        const tokenNumberEl = document.getElementById('tokenNumber');
        if (!tokenNumberEl) return;
        
        // Auto-refresh token status
        startAutoRefresh();
        
        // Manual refresh button
        document.getElementById('refreshBtn')?.addEventListener('click', function() {
            refreshTokenStatus();
        });
    }
    
    /**
     * Refresh token status via AJAX
     */
    function refreshTokenStatus() {
        const tokenNumber = document.getElementById('tokenNumber')?.textContent.trim();
        if (!tokenNumber) return;
        
        fetch('/smarthealth_nepal/backend/api/get_token_status.php?token=' + encodeURIComponent(tokenNumber))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTokenDisplay(data.token);
                    updateQueueInfo(data);
                    
                    // Show alert if called
                    if (data.token.status === 'Called') {
                        showCallAlert();
                    }
                }
            })
            .catch(error => console.error('Error refreshing token:', error));
    }
    
    /**
     * Update token display with latest data
     */
    function updateTokenDisplay(token) {
        // Update status badge
        const statusEl = document.querySelector('[data-status]');
        if (statusEl) {
            statusEl.textContent = getStatusLabel(token.status);
            statusEl.className = 'badge bg-' + getStatusColor(token.status) + ' p-2';
        }
        
        // Update priority badge
        const priorityEl = document.querySelector('[data-priority]');
        if (priorityEl) {
            priorityEl.textContent = token.priority;
            priorityEl.className = 'badge bg-' + getPriorityColor(token.priority) + ' p-2';
        }
        
        // Update department load
        updateDepartmentLoad(token.department_load);
    }
    
    /**
     * Update queue information
     */
    function updateQueueInfo(data) {
        const positionEl = document.querySelector('[data-position]');
        if (positionEl) {
            positionEl.textContent = data.queue_position || 0;
        }
        
        const waitEl = document.querySelector('[data-wait-time]');
        if (waitEl) {
            waitEl.textContent = data.token.estimated_wait_time || 0;
        }
    }
    
    /**
     * Update department load indicator
     */
    function updateDepartmentLoad(load) {
        if (!load) return;
        
        const progressEl = document.querySelector('progress');
        if (progressEl) {
            progressEl.value = load.percentage || 0;
            progressEl.max = 100;
        }
        
        const statusEl = document.querySelector('[data-load-status]');
        if (statusEl) {
            statusEl.textContent = load.load || 'Unknown';
            statusEl.className = 'badge bg-' + getLoadColor(load.load) + ' p-2';
        }
    }
    
    /**
     * Show alert when token is called
     */
    function showCallAlert() {
        const alertEl = document.getElementById('calledAlert');
        if (alertEl) {
            alertEl.style.display = 'block';
            
            // Play notification sound if available
            try {
                const audio = new Audio('/smarthealth_nepal/frontend/public/assets/audio/call.mp3');
                audio.play().catch(e => console.log('Could not play audio:', e));
            } catch (e) {
                console.log('Audio playback not available');
            }
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
    
    /**
     * Start auto-refresh timer
     */
    function startAutoRefresh() {
        // Initial refresh
        refreshTokenStatus();
        
        // Set interval
        refreshTimer = setInterval(refreshTokenStatus, TOKEN_REFRESH_INTERVAL);
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (refreshTimer) clearInterval(refreshTimer);
        });
    }
    
    /**
     * Get status label
     */
    function getStatusLabel(status) {
        const labels = {
            'Active': 'Active - In Queue',
            'Called': 'Called - Please Proceed',
            'Completed': 'Completed',
            'Missed': 'Missed'
        };
        return labels[status] || status;
    }
    
    /**
     * Get status color
     */
    function getStatusColor(status) {
        const colors = {
            'Active': 'success',
            'Called': 'danger',
            'Completed': 'info',
            'Missed': 'secondary'
        };
        return colors[status] || 'secondary';
    }
    
    /**
     * Get priority color
     */
    function getPriorityColor(priority) {
        const colors = {
            'Emergency': 'danger',
            'Priority': 'warning',
            'Chronic': 'info',
            'Normal': 'secondary'
        };
        return colors[priority] || 'secondary';
    }
    
    /**
     * Get load color
     */
    function getLoadColor(load) {
        const colors = {
            'High': 'danger',
            'Moderate': 'warning',
            'Low': 'success'
        };
        return colors[load] || 'secondary';
    }
    
    /**
     * Countdown timer for estimated wait
     */
    function startCountdown() {
        setInterval(function() {
            const waitEl = document.querySelector('[data-wait-time]');
            if (waitEl && waitEl.textContent) {
                let wait = parseInt(waitEl.textContent) - 1;
                if (wait < 0) wait = 0;
                waitEl.textContent = wait;
            }
        }, 60000); // Update every minute
    }
    
    /**
     * Mobile notification support
     */
    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
    
    /**
     * Send browser notification when called
     */
    function sendBrowserNotification() {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Your Token is Called!', {
                body: 'Please proceed to the counter immediately',
                icon: '/smarthealth_nepal/frontend/public/assets/images/notification.png'
            });
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTokenStatus);
    } else {
        initTokenStatus();
    }
    
    // Export for testing
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = {
            refreshTokenStatus,
            updateTokenDisplay,
            startAutoRefresh
        };
    }
})();
