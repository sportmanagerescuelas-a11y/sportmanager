</main>
<?php
$indexControllerPath = __DIR__ . '/../../../assets/js/indexcontroller.js';
$passwordTogglePath = __DIR__ . '/../../../assets/js/password-toggle.js';
$indexControllerVersion = is_file($indexControllerPath) ? (string)filemtime($indexControllerPath) : (string)time();
$passwordToggleVersion = is_file($passwordTogglePath) ? (string)filemtime($passwordTogglePath) : (string)time();
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
            <a href="index.php#sobre-nosotros">Sobre nosotros</a>
            <a href="index.php#planes">Planes</a>
            <a href="index.php#beneficios">Beneficios</a>
            <a href="index.php#contacto">Contacto</a>
        </div>

        <div class="site-footer__column">
            <h3>Soporte</h3>
            <a href="index.php?url=login">Iniciar sesión</a>
            <a href="index.php?url=register">Crear cuenta</a>
            <a href="mailto:sportmanager.escuelas@gmail.com">Escríbenos</a>
            <a href="https://www.instagram.com/sport_manager_escuelas/" target="_blank" rel="noopener">Instagram</a>
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
                <a href="https://www.facebook.com/profile.php?id=100083328903404" target="blank_">Facebook</a>
                <a href="https://x.com/spmanager20" target="blank_">X</a>
                <a href="https://www.instagram.com/sport_manager_escuelas/" target="blank_">Instagram</a>
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
    </script>
<?php endif; ?>
<button id="backToTop" class="btn-back-to-top" aria-hidden="true" title="Volver arriba">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <path d="M12 5v14" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M19 12l-7-7-7 7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/password-toggle.js?v=<?= urlencode($passwordToggleVersion) ?>"></script>
<script src="assets/js/indexcontroller.js?v=<?= urlencode($indexControllerVersion) ?>"></script>
</body>
</html>
