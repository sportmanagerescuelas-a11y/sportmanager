<br>
<br>
<?php
$loginErrorCode = isset($_GET['error']) ? (string)$_GET['error'] : '';
$loginErrorMap = [
    'invalid' => 'Usuario o contrasena incorrectos.',
    'pending' => 'Tu cuenta esta pendiente de aprobacion por un administrador.',
    'disabled' => 'Tu cuenta esta deshabilitada.',
    '404' => 'La pagina solicitada no existe o fue movida.',
];
$loginErrorText = sm_error_text($loginErrorCode, $loginErrorMap);
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
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contrase&ntilde;a</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Contrase&ntilde;a" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Ingresar</button>
                    </form>
                    <div class="text-end mt-2">
                        <a href="index.php?url=recuperar" class="btn btn-link p-0">&iquest;Olvidaste tu contrase&ntilde;a?</a>
                    </div>
                    <?php if (!empty($_SESSION['flash_session_expired'])): ?>
                        <?php sm_render_alert('Tu sesion expiro por inactividad. Inicia sesion de nuevo.', 'Sesion finalizada', 'warning', true); ?>
                        <?php unset($_SESSION['flash_session_expired']); ?>
                    <?php endif; ?>
                    <?php if ($loginErrorText !== ''): ?>
                        <?php sm_render_alert($loginErrorText, 'No fue posible iniciar', 'danger', true); ?>
                    <?php endif; ?>
                    <div class="mt-3 text-center">
                        <p>&iquest;No tienes cuenta? <a href="index.php?url=register">Reg&iacute;strate aqu&iacute;</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
