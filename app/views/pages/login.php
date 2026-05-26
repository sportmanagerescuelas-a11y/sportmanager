<br>
<br>
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
$loginFieldError = ['field' => '', 'message' => ''];

if (!empty($_SESSION['flash_session_expired'])) {
    $loginModalTitle = 'Sesion finalizada';
    $loginModalMessage = 'Tu sesion expiro por inactividad. Inicia sesion de nuevo.';
    $loginModalType = 'warning';
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
<div class="container-fluid mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card shadow-lg p-4 login-card">
                <div class="card-header">
                    <h2 class="text-center">Iniciar sesi&oacute;n</h2>
                </div>
                <div class="card-body">
                    <form action="controllers/loginController.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electr&oacute;nico</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="tu@correo.com" required>
                            <div class="invalid-feedback" id="emailFeedback">Correo invalido.</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contrase&ntilde;a</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Contrase&ntilde;a" required>
                            <div class="invalid-feedback" id="passwordFeedback">Contrasena invalida.</div>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Ingresar</button>
                    </form>
                    <div class="text-end mt-2">
                        <a href="recuperar" class="btn btn-link p-0">&iquest;Olvidaste tu contrase&ntilde;a?</a>
                    </div>
                    <div class="mt-3 text-center">
                        <p>&iquest;No tienes cuenta? <a href="register">Reg&iacute;strate aqu&iacute;</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($loginModalMessage !== ''): ?>
    <?php sm_render_modal_message('loginMessageModal', $loginModalTitle, $loginModalMessage, $loginModalType); ?>
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
