/**
 * Offline SMS Booking Handler
 * Manages SMS commands for patients without internet access
 */

(function() {
    'use strict';
    
    const SMS_COMMANDS = {
        'BOOK FEVER': { symptoms: 'fever', label: 'Fever' },
        'BOOK BREATHING': { symptoms: 'breathing', label: 'Breathing Difficulty' },
        'BOOK INJURY': { symptoms: 'injury', label: 'Injury/Trauma' },
        'BOOK CHRONIC': { symptoms: 'chronic', label: 'Chronic Follow-up' },
        'BOOK MATERNAL': { symptoms: 'pregnancy', label: 'Maternal Health' },
        'BOOK GENERAL': { symptoms: 'general', label: 'General Check-up' },
        'STATUS': { action: 'status', label: 'Check Token Status' },
        'HELP': { action: 'help', label: 'Get Help' }
    };
    
    /**
     * Initialize offline SMS interface
     */
    function initOfflineSMS() {
        const smsInput = document.getElementById('smsCommand');
        const sendBtn = document.getElementById('sendSMS');
        
        if (!smsInput || !sendBtn) return;
        
        sendBtn.addEventListener('click', handleSMSCommand);
        smsInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') handleSMSCommand();
        });
        
        displaySMSInstructions();
    }
    
    /**
     * Handle SMS command
     */
    function handleSMSCommand() {
        const smsInput = document.getElementById('smsCommand');
        const command = smsInput.value.trim().toUpperCase();
        
        if (!command) {
            showSMSMessage('Please enter a command', 'info');
            return;
        }
        
        appendMessageToChat('You', command, 'sent');
        smsInput.value = '';
        
        // Simulate processing delay
        showProcessing();
        
        setTimeout(() => {
            processSMSCommand(command);
        }, 1000);
    }
    
    /**
     * Process SMS command
     */
    function processSMSCommand(command) {
        if (command === 'HELP') {
            showHelpMessage();
            return;
        }
        
        if (command === 'STATUS') {
            showStatusMessage();
            return;
        }
        
        // Check if it's a booking command
        const bookingPatterns = Object.keys(SMS_COMMANDS).filter(k => SMS_COMMANDS[k].symptoms);
        const matchedCommand = bookingPatterns.find(cmd => command === cmd || command.includes(SMS_COMMANDS[cmd].label.toUpperCase()));
        
        if (matchedCommand) {
            processBookingCommand(matchedCommand);
        } else if (command.startsWith('CONFIRM')) {
            // Confirm booking with OTP
            const otpMatch = command.match(/\d{6}/);
            if (otpMatch) {
                confirmBooking(otpMatch[0]);
            } else {
                appendMessageToChat('SmartHealth', 'Invalid OTP format. Please enter 6 digits.', 'received');
            }
        } else {
            appendMessageToChat('SmartHealth', 'Unknown command. Type HELP for available commands.', 'received');
        }
    }
    
    /**
     * Process booking command
     */
    function processBookingCommand(command) {
        const cmdInfo = SMS_COMMANDS[command];
        const response = `Token booking initiated for ${cmdInfo.label}. SMS is being processed. You will receive your token number shortly.`;
        
        appendMessageToChat('SmartHealth', response, 'received');
        
        // Send to backend to create token
        fetch('/smarthealth_nepal/backend/api/sms_book_token.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'command=' + encodeURIComponent(command)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Show token confirmation
                setTimeout(() => {
                    appendMessageToChat('SmartHealth', 
                        `Your token has been booked!\n\nToken: ${data.token_number}\nDepartment: ${data.department}\n\nOTP for confirmation: ${data.otp}\nType: CONFIRM ${data.otp}`, 
                        'received');
                }, 1500);
            } else {
                appendMessageToChat('SmartHealth', 'Failed to book token. ' + (data.message || 'Please try again.'), 'received');
            }
        })
        .catch(err => {
            appendMessageToChat('SmartHealth', 'Error processing booking. Please try again.', 'received');
        });
    }
    
    /**
     * Show status message
     */
    function showStatusMessage() {
        const response = `To check your token status, send your phone number:\nSTATUS 9841234567`;
        appendMessageToChat('SmartHealth', response, 'received');
    }
    
    /**
     * Show help message
     */
    function showHelpMessage() {
        const help = `Available SMS Commands:
        
BOOK FEVER - Book for fever symptoms
BOOK BREATHING - Book for breathing difficulty
BOOK INJURY - Book for injury/trauma
BOOK CHRONIC - Book for chronic follow-up
BOOK MATERNAL - Book for maternal health
BOOK GENERAL - General check-up
STATUS - Check your token status
HELP - Show this message

Reply with the exact command to proceed.`;
        
        appendMessageToChat('SmartHealth', help, 'received');
    }
    
    /**
     * Confirm booking with OTP
     */
    function confirmBooking(otp) {
        appendMessageToChat('SmartHealth', 'Confirming your booking...', 'received');
        
        fetch('/smarthealth_nepal/backend/api/confirm_sms_booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'otp=' + encodeURIComponent(otp)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                appendMessageToChat('SmartHealth', 
                    `✓ Booking confirmed!\n\nYour token is ready. You will receive an SMS notification with all details.`, 
                    'received');
            } else {
                appendMessageToChat('SmartHealth', 'Invalid OTP. Please try again.', 'received');
            }
        });
    }
    
    /**
     * Append message to chat display
     */
    function appendMessageToChat(sender, message, type) {
        const chatEl = document.getElementById('smsChat');
        if (!chatEl) return;
        
        const messageEl = document.createElement('div');
        messageEl.className = 'sms-message sms-' + type;
        messageEl.innerHTML = `
            <div class="sms-sender">${sender}</div>
            <div class="sms-text">${message}</div>
            <div class="sms-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
        `;
        
        chatEl.appendChild(messageEl);
        chatEl.scrollTop = chatEl.scrollHeight;
    }
    
    /**
     * Show processing indicator
     */
    function showProcessing() {
        const chatEl = document.getElementById('smsChat');
        if (!chatEl) return;
        
        const processingEl = document.createElement('div');
        processingEl.className = 'sms-processing';
        processingEl.id = 'processingIndicator';
        processingEl.innerHTML = '<span>SmartHealth is typing...</span>';
        
        chatEl.appendChild(processingEl);
        chatEl.scrollTop = chatEl.scrollHeight;
    }
    
    /**
     * Hide processing indicator
     */
    function hideProcessing() {
        const processingEl = document.getElementById('processingIndicator');
        if (processingEl) processingEl.remove();
    }
    
    /**
     * Display SMS instructions
     */
    function displaySMSInstructions() {
        const instructionsEl = document.getElementById('smsInstructions');
        if (instructionsEl) {
            instructionsEl.innerHTML = `
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> How to Use SMS Booking:</h6>
                    <ol class="mb-0">
                        <li>Type a command from the list (e.g., BOOK FEVER)</li>
                        <li>We'll show you your token details</li>
                        <li>Confirm with the OTP we send</li>
                        <li>Your token is ready to use!</li>
                    </ol>
                </div>
            `;
        }
    }
    
    /**
     * Show SMS message
     */
    function showSMSMessage(message, type) {
        const alertEl = document.createElement('div');
        alertEl.className = 'alert alert-' + type + ' alert-dismissible fade show';
        alertEl.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.getElementById('smsContainer') || document.body;
        container.insertBefore(alertEl, container.firstChild);
        
        setTimeout(() => alertEl.remove(), 5000);
    }
    
    /**
     * Export command list to UI
     */
    window.getSMSCommands = function() {
        return Object.keys(SMS_COMMANDS).map(cmd => ({
            command: cmd,
            ...SMS_COMMANDS[cmd]
        }));
    };
    
    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initOfflineSMS);
    } else {
        initOfflineSMS();
    }
})();
