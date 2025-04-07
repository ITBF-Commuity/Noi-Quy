            </div> <!-- Kết thúc .admin-content-container -->
        </main>
    </div> <!-- Kết thúc .admin-wrapper -->

    <!-- JavaScript -->
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
    <script src="<?= BASE_URL ?>assets/js/admin.js"></script>
    
    <!-- Script quản lý sidebar -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.admin-sidebar');
        const mainContent = document.querySelector('.admin-main');

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    });
    </script>
</body>
</html>