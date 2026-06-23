</main>
<?php
$indexControllerPath = __DIR__ . '/../../../assets/js/indexcontroller.js';
$passwordTogglePath = __DIR__ . '/../../../assets/js/password-toggle.js';
$modalManagerPath = __DIR__ . '/../../../assets/js/modal-manager.js';
$indexControllerVersion = is_file($indexControllerPath) ? (string)filemtime($indexControllerPath) : (string)time();
$passwordToggleVersion = is_file($passwordTogglePath) ? (string)filemtime($passwordTogglePath) : (string)time();
$modalManagerVersion = is_file($modalManagerPath) ? (string)filemtime($modalManagerPath) : (string)time();
?>
<footer class="site-footer">
    <div class="container site-footer__grid">
        <div class="site-footer__brand">
            <div class="site-footer__brand-row">
                <img src="assets/img/balonfutbol.png" alt="Sport Manager" class="site-footer__logo">
                <div>
                    <h2>Sport Manager</h2>
                    <p>Gestión deportiva con estilo, control y cercanía.</p>
                </div>
            </div>
            <p class="site-footer__description">
                Centralizamos el seguimiento de escuelas, deportistas, eventos y pagos en una experiencia más clara y elegante.
            </p>
        </div>

        <div class="site-footer__column">
            <h3>Explora</h3>
            <a href="home#sobre-nosotros">Sobre nosotros</a>
            <a href="home#planes">Planes</a>
            <a href="home#beneficios">Beneficios</a>
            <a href="home#contacto">Contacto</a>
        </div>

        <div class="site-footer__column">
            <h3>Soporte</h3>
            <a href="login">Iniciar sesión</a>
            <a href="register">Crear cuenta</a>
            <a href="mailto:sportmanager.escuelas@gmail.com">Escríbenos</a>
            <a href="https://www.instagram.com/sport_manager_escuelas/" rel="noopener">Instagram</a>
        </div>

        <div class="site-footer__column">
            <h3>Contacto</h3>
            <p>Email: sportmanager.escuelas@gmail.com</p>
            <p>Tel: 601 577 1818</p>
            <p>Bogotá, Colombia</p>
        </div>
    </div>

    <div class="site-footer__bottom">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <span>© 2026 Sport Manager. Todos los derechos reservados.</span>
            <div class="site-footer__social">
                <a href="https://www.facebook.com/profile.php?id=100083328903404">Facebook</a>
                <a href="https://x.com/spmanager20">X</a>
                <a href="https://www.instagram.com/sport_manager_escuelas/">Instagram</a>
            </div>
        </div>
    </div>
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
<button id="backToTop" class="btn-back-to-top" aria-hidden="true" title="Volver arriba">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <path d="M12 5v14" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M19 12l-7-7-7 7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/modal-manager.js?v=<?= urlencode($modalManagerVersion) ?>"></script>
<script src="assets/js/password-toggle.js?v=<?= urlencode($passwordToggleVersion) ?>"></script>
<script src="assets/js/indexcontroller.js?v=<?= urlencode($indexControllerVersion) ?>"></script>
<script>
    (function () {
        const numericLimits = {
            id_usuario: 11,
            id_deportista: 11,
            num_documento: 11,
            dni: 11,
            telefono: 10
        };

        function normalizeNumericInput(input) {
            const fieldName = input.name || input.id || '';
            const maxLen = numericLimits[fieldName] || null;
            let value = String(input.value || '').replace(/\D+/g, '');
            if (maxLen !== null) {
                value = value.slice(0, maxLen);
            }
            if (input.value !== value) {
                input.value = value;
            }
        }

        document.addEventListener('input', function (event) {
            const target = event.target;
            if (!target || !(target instanceof HTMLInputElement)) {
                return;
            }
            const fieldName = target.name || target.id || '';
            if (!(fieldName in numericLimits)) {
                return;
            }
            normalizeNumericInput(target);
        });

        document.addEventListener('paste', function (event) {
            const target = event.target;
            if (!target || !(target instanceof HTMLInputElement)) {
                return;
            }
            const fieldName = target.name || target.id || '';
            if (!(fieldName in numericLimits)) {
                return;
            }
            setTimeout(function () {
                normalizeNumericInput(target);
            }, 0);
        });
    })();
</script>
</body>
</html>

