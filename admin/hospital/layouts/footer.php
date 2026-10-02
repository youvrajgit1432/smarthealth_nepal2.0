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
         * Mobile sidebar drawer
         */
        function toggleSidebar(force) {
            const sidebar = document.getElementById('hospitalSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const toggle = document.querySelector('.sidebar-toggle');
            if (!sidebar) return;

            const open = (typeof force === 'boolean') ? force : !sidebar.classList.contains('open');
            sidebar.classList.toggle('open', open);
            if (backdrop) backdrop.classList.toggle('active', open);
            if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        // Close the drawer when a link inside it is used
        document.querySelectorAll('#hospitalSidebar a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) toggleSidebar(false);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') toggleSidebar(false);
        });

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
