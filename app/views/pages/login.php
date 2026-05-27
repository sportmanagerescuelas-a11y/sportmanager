<?php
$loginErrorCode = isset($_GET['error']) ? (string)$_GET['error'] : '';
$loginErrorMap = [
    'empty' => 'Debes completar correo y contrasena.',
    'invalidemail' => 'El formato del correo no es valido.',
    'invalid' => 'Usuario o contrasena incorrectos.',
    'pending' => 'Tu cuenta esta pendiente de aprobacion por un administrador.',
    'payment_pending' => 'Tu pago esta pendiente de verificacion por el superadmin.',
    'disabled' => 'Tu cuenta esta deshabilitada.',
    '404' => 'La pagina solicitada no existe o fue movida.',
];
$loginErrorText = sm_error_text($loginErrorCode, $loginErrorMap);
$loginModalTitle = '';
$loginModalMessage = '';
$loginModalType = '';
$loginSessionExpired = false;
$loginFieldError = ['field' => '', 'message' => ''];

if (!empty($_SESSION['flash_session_expired'])) {
    $loginModalTitle = 'Sesion finalizada';
    $loginModalMessage = 'Tu sesion expiro por inactividad. Inicia sesion de nuevo.';
    $loginModalType = 'warning';
    $loginSessionExpired = true;
    unset($_SESSION['flash_session_expired']);
} elseif ($loginErrorCode === 'invalidemail') {
    $loginFieldError = ['field' => 'email', 'message' => 'El formato del correo no es valido.'];
} elseif ($loginErrorCode === 'empty') {
    $loginFieldError = ['field' => 'email', 'message' => 'Debes completar correo y contrasena.'];
} elseif ($loginErrorCode === 'invalid') {
    $loginFieldError = ['field' => 'password', 'message' => 'Usuario o contrasena incorrectos.'];
} elseif ($loginErrorText !== '') {
    $loginModalTitle = 'No fue posible iniciar';
    $loginModalMessage = $loginErrorText;
    $loginModalType = 'danger';
}
?>
<div class="auth-page py-4 py-lg-5">
    <div class="auth-shell container">
        <section class="auth-panel reveal-up">
            <div class="auth-kicker">Acceso seguro</div>
            <h1 class="auth-title">Vuelve a tu tablero sin perder ritmo.</h1>
            <p class="auth-copy mb-0">
                Entra para gestionar escuelas, deportistas, eventos y reportes desde un panel más claro y rápido.
            </p>
            <div class="auth-badges">
                <div class="auth-badge"><span></span> Sesiones protegidas</div>
                <div class="auth-badge"><span></span> Recuperación asistida</div>
                <div class="auth-badge"><span></span> Diseño responsive</div>
            </div>
        </section>

        <section class="auth-card card reveal-up delay-1">
            <div class="card-header">
                <h2 class="text-center mb-1">Iniciar sesión</h2>
                <p class="auth-subtitle text-center mb-0">Ingresa tus credenciales para continuar.</p>
            </div>
            <div class="card-body">
                <form action="controllers/loginController.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control auth-input" id="email" name="email" placeholder="tu@correo.com" required>
                        <div class="invalid-feedback" id="emailFeedback">Correo inválido.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control auth-input" id="password" name="password" placeholder="Contraseña" required>
                        <div class="invalid-feedback" id="passwordFeedback">Contraseña inválida.</div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 auth-action">Ingresar</button>
                </form>
                <div class="auth-links mt-3">
                    <a href="recuperar" class="btn btn-link p-0">¿Olvidaste tu contraseña?</a>
                    <a href="register" class="btn btn-link p-0">Crear cuenta</a>
                </div>
            </div>
        </section>
    </div>
</div>
<?php if ($loginModalMessage !== ''): ?>
    <?php if ($loginSessionExpired): ?>
        <div class="modal fade" id="loginMessageModal" tabindex="-1" aria-labelledby="loginMessageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border: 2px solid #fd7e14;">
                    <div class="modal-header" style="background-color: #ffffff; border-bottom: 1px solid #fd7e14;">
                        <h5 class="modal-title w-100 text-center" id="loginMessageModalLabel"><?= htmlspecialchars($loginModalTitle, ENT_QUOTES, 'UTF-8') ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body text-center" style="background-color: #ffffff;">
                        <img src="assets/img/reloj.gif" alt="Sesion expirada" class="img-fluid mb-3" style="max-height: 180px;">
                        <p class="mb-0"><?= htmlspecialchars($loginModalMessage, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="modal-footer" style="background-color: #ffffff; border-top: 1px solid #fd7e14;">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('loginMessageModal');
            if (modalElement && window.bootstrap && bootstrap.Modal) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
                    backdrop: true,
                    keyboard: true
                });

                modalElement.addEventListener('hidden.bs.modal', function () {
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                    document.querySelectorAll('.modal-backdrop').forEach(function (el) {
                        el.remove();
                    });
                });

                modal.show();
            }
        });
        </script>
    <?php else: ?>
        <?php sm_render_modal_message('loginMessageModal', $loginModalTitle, $loginModalMessage, $loginModalType); ?>
    <?php endif; ?>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const field = '<?= htmlspecialchars($loginFieldError['field'], ENT_QUOTES, 'UTF-8') ?>';
    const message = '<?= htmlspecialchars($loginFieldError['message'], ENT_QUOTES, 'UTF-8') ?>';

    if (!field || !message) {
        return;
    }

    const input = document.getElementById(field);
    const feedback = document.getElementById(field + 'Feedback');
    if (input) {
        input.classList.add('is-invalid');
        input.focus();
    }
    if (feedback) {
        feedback.textContent = message;
    }
});
</script>

