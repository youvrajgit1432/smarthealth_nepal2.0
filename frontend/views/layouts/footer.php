<?php
/**
 * Footer Layout
 */
?>
    </main>
    
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>SmartHealth Nepal</h5>
                    <p><?php echo isset($lang['tagline']) ? $lang['tagline'] : 'Sustainable Digital Healthcare'; ?></p>
                </div>
                <div class="col-md-4">
                    <h5><?php echo isset($lang['about']) ? $lang['about'] : 'About'; ?></h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-decoration-none text-light">About Us</a></li>
                        <li><a href="#" class="text-decoration-none text-light"><?php echo isset($lang['contact']) ? $lang['contact'] : 'Contact'; ?></a></li>
                        <li><a href="#" class="text-decoration-none text-light"><?php echo isset($lang['services']) ? $lang['services'] : 'Services'; ?></a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5><?php echo isset($lang['contact_us']) ? $lang['contact_us'] : 'Contact Us'; ?></h5>
                    <p>
                        <i class="fas fa-phone"></i> +977-1-XXXXXXX<br>
                        <i class="fas fa-envelope"></i> info@smarthealth.npl<br>
                        <i class="fas fa-map-marker-alt"></i> Kathmandu, Nepal
                    </p>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center">
                <p>&copy; 2024 SmartHealth Nepal. All rights reserved.</p>
                <p>
                    <a href="#" class="text-decoration-none text-light"><?php echo isset($lang['privacy_policy']) ? $lang['privacy_policy'] : 'Privacy Policy'; ?></a> |
                    <a href="#" class="text-decoration-none text-light"><?php echo isset($lang['terms_conditions']) ? $lang['terms_conditions'] : 'Terms & Conditions'; ?></a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/smarthealth_nepal/frontend/public/assets/js/main.js"></script>
    
    <script>
        // Language switcher
        document.addEventListener('DOMContentLoaded', function() {
            const langSelect = document.querySelector('.language-switcher');
            if (langSelect) {
                langSelect.addEventListener('change', function() {
                    const currentUrl = window.location.href;
                    const separator = currentUrl.includes('?') ? '&' : '?';
                    window.location.href = currentUrl + separator + 'lang=' + this.value;
                });
            }
        });
    </script>
</body>
</html>
