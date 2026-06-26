</div>
            </main>
        </div>
    </div>
    <script src="<?= htmlspecialchars($assetBase . '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js?v=' . $assetVersion, ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".datepicker", {
                dateFormat: "Y-m-d",
                allowInput: true,
                placeholder: "Select date"
            });
            
            document.querySelectorAll('.toggle-password').forEach(function(icon) {
                icon.addEventListener('click', function() {
                    const wrapper = this.closest('.password-wrapper');
                    const input = wrapper.querySelector('input');
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    }
                });
            });

            const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
            const sidebarCloseTargets = document.querySelectorAll('[data-sidebar-close], .sidebar a');
            const setSidebarOpen = function(isOpen) {
                document.body.classList.toggle('sidebar-open', isOpen);
                if (sidebarToggle) {
                    sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                }
            };

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    setSidebarOpen(!document.body.classList.contains('sidebar-open'));
                });
            }

            sidebarCloseTargets.forEach(function(target) {
                target.addEventListener('click', function() {
                    setSidebarOpen(false);
                });
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    setSidebarOpen(false);
                }
            });
        });
    </script>
    <?php if (isset($extraJs)): ?>
    <?= $extraJs ?>
    <?php endif; ?>
</body>
</html>
