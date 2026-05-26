</main>
<?php
$indexControllerPath = __DIR__ . '/../../../assets/js/indexcontroller.js';
$passwordTogglePath = __DIR__ . '/../../../assets/js/password-toggle.js';
$indexControllerVersion = is_file($indexControllerPath) ? (string)filemtime($indexControllerPath) : (string)time();
$passwordToggleVersion = is_file($passwordTogglePath) ? (string)filemtime($passwordTogglePath) : (string)time();
?>
<footer>
    <p> 2026 Proyecto SM. All rights reserved &copy.</p>
</footer>
<?php if (isset($_SESSION['usuario'])): ?>
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        (function () {
            const IDLE_TIMEOUT_MS = 180000; // 3 minutos
            let idleTimer = null;

            function resetIdleTimer() {
                if (idleTimer) {
                    clearTimeout(idleTimer);
                }
                idleTimer = setTimeout(function () {
                    window.location.href = 'logout?reason=inactive';
                }, IDLE_TIMEOUT_MS);
            }

            ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function (eventName) {
                window.addEventListener(eventName, resetIdleTimer, { passive: true });
            });

            resetIdleTimer();
        })();
    </script>
<?php endif; ?>
    <!-- Back to top button (outside footer) -->
    <button id="backToTop" class="btn-back-to-top" aria-hidden="true" title="Volver arriba">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
            <path d="M12 5v14" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M19 12l-7-7-7 7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Tu JS personalizado -->
    <script src="assets/js/password-toggle.js?v=<?= urlencode($passwordToggleVersion) ?>"></script>
    <script src="assets/js/indexcontroller.js?v=<?= urlencode($indexControllerVersion) ?>"></script>
</body>
</html>
