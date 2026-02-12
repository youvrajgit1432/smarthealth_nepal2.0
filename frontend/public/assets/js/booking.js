/**
 * SmartHealth Nepal - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initializeTooltips();
    
    // Auto-refresh token status
    setupAutoRefresh();
    
    // Form handling
    setupFormHandling();
});

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
}

/**
 * Setup auto-refresh for token status
 */
function setupAutoRefresh() {
    // Check if we're on the token status page
    const statusPage = document.querySelector('[data-page="token-status"]');
    
    if (statusPage) {
        // Refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    }
}

/**
 * Setup form handling
 */
function setupFormHandling() {
    // Phone number input - allow only digits
    const phoneInputs = document.querySelectorAll('input[inputmode="numeric"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9+]/g, '');
        });
    });
}

/**
 * Format phone number
 */
function formatPhoneNumber(phone) {
    return phone.replace(/[^0-9]/g, '');
}

/**
 * Validate Nepali phone number
 */
function isValidNepalPhone(phone) {
    const patterns = [
        /^98\d{8}$/,           // 98XXXXXXXX
        /^97\d{8}$/,           // 97XXXXXXXX
        /^\+977\d{10}$/,       // +977XXXXXXXXXX
        /^9[78]\d{8}$/         // 9XXXXXXXXX
    ];
    
    return patterns.some(pattern => pattern.test(phone));
}

/**
 * Show loading spinner
 */
function showLoading() {
    const loader = document.createElement('div');
    loader.id = 'loadingSpinner';
    loader.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;
    document.body.appendChild(loader);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const loader = document.getElementById('loadingSpinner');
    if (loader) {
        loader.remove();
    }
}

/**
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    toast.role = 'alert';
    toast.innerHTML = `
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <div>${message}</div>
    `;
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

/**
 * Get query parameter
 */
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

/**
 * Calculate time remaining
 */
function calculateTimeRemaining(targetDateTime) {
    const target = new Date(targetDateTime).getTime();
    const now = new Date().getTime();
    const remaining = target - now;
    
    if (remaining <= 0) return 'Due now';
    
    const days = Math.floor(remaining / (1000 * 60 * 60 * 24));
    const hours = Math.floor((remaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
    
    if (days > 0) return `${days}d ${hours}h`;
    if (hours > 0) return `${hours}h ${minutes}m`;
    return `${minutes}m`;
}

/**
 * Disable form submit button to prevent double submission
 */
document.addEventListener('submit', function(e) {
    const form = e.target;
    const submitBtn = form.querySelector('[type="submit"]');
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML += ' <span class="spinner-border spinner-border-sm ms-2"></span>';
    }
});

/**
 * Language switcher
 */
function changeLanguage(lang) {
    const url = new URL(window.location);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}

// Export functions for use in other scripts
window.SmartHealth = {
    formatPhoneNumber,
    isValidNepalPhone,
    showLoading,
    hideLoading,
    showNotification,
    getQueryParam,
    calculateTimeRemaining,
    changeLanguage
};
