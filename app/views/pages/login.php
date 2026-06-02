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
$loginInlineAlert = ['title' => '', 'message' => '', 'variant' => 'danger'];
$loginFieldError = ['field' => '', 'message' => ''];

if (!empty($_SESSION['flash_session_expired'])) {
    $loginInlineAlert = [
        'title' => 'Sesion finalizada',
        'message' => 'Tu sesion expiro por inactividad. Inicia sesion de nuevo.',
        'variant' => 'warning',
    ];
    $loginSessionExpired = true;
    unset($_SESSION['flash_session_expired']);
} elseif ($loginErrorCode === 'invalidemail') {
    $loginFieldError = ['field' => 'email', 'message' => 'El formato del correo no es valido.'];
} elseif ($loginErrorCode === 'empty') {
    $loginFieldError = ['field' => 'email', 'message' => 'Debes completar correo y contrasena.'];
} elseif ($loginErrorCode === 'invalid') {
    $loginFieldError = ['field' => 'password', 'message' => 'Usuario o contrasena incorrectos.'];
} elseif ($loginErrorText !== '') {
    $loginInlineAlert = [
        'title' => 'No fue posible iniciar',
        'message' => $loginErrorText,
        'variant' => 'danger',
    ];
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
                <?php if ($loginInlineAlert['message'] !== ''): ?>
                    <?php sm_render_alert($loginInlineAlert['message'], $loginInlineAlert['title'], $loginInlineAlert['variant'], true); ?>
                <?php endif; ?>
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

