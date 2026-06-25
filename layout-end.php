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
        });
    </script>
    <?php if (isset($extraJs)): ?>
    <?= $extraJs ?>
    <?php endif; ?>
</body>
</html>
