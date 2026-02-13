<?php
/**
 * Hospital Admin Layout Footer
 */
?>
            </div> <!-- End dashboard-content -->
        </div> <!-- End main-content -->
    </div> <!-- End admin-container -->

    <script>
        /**
         * Toggle User Profile Dropdown
         */
        function toggleProfileDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('active');
        }

        /**
         * Close dropdown when clicking outside
         */
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const userAccountWrapper = document.querySelector('.user-account-wrapper');
            
            if (dropdown && userAccountWrapper && !userAccountWrapper.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        /**
         * Close dropdown when pressing Escape key
         */
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const dropdown = document.getElementById('profileDropdown');
                if (dropdown) {
                    dropdown.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>
