<?php
/**
 * Admin Footer Layout
 */
?>
    </div>
</div>
    
    <footer class="text-center text-muted py-3 mt-5">
        <small>&copy; 2024 SmartHealth Nepal Admin Panel. All rights reserved.</small>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/smarthealth_nepal/admin/public/assets/js/admin.js"></script>
    
    <script>
        // Toggle sidebar and overlay
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarWrapper');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
            if (overlay) {
                overlay.classList.toggle('show');
            }
        }
        
        // Close sidebar and overlay when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebarWrapper');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.querySelector('.toggle-btn');
            if (sidebar && overlay && toggleBtn && window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            }
        });
        
        // Profile dropdown toggle
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            if (menu) {
                menu.classList.toggle('show');
            }
        }
        
        // Close profile menu when clicking outside
        document.addEventListener('click', function(event) {
            const profileDropdown = document.querySelector('.profile-dropdown');
            const profileMenu = document.getElementById('profileMenu');
            if (profileDropdown && profileMenu && !profileDropdown.contains(event.target)) {
                profileMenu.classList.remove('show');
            }
        });
        
        // Search functionality
        if (document.getElementById('headerSearch')) {
            document.getElementById('headerSearch').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchQuery = this.value.trim();
                    if (searchQuery) {
                        console.log('Search query:', searchQuery);
                        // Implement search across pages or redirect to search results
                        // window.location.href = '/smarthealth_nepal/admin/frontend/views/search.php?q=' + encodeURIComponent(searchQuery);
                    }
                }
            });
        }
    </script>
</body>
</html>
