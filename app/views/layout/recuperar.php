<?php
$stylePath = __DIR__ . '/../../../assets/css/style.css';
$modalManagerPath = __DIR__ . '/../../../assets/js/modal-manager.js';
$styleVersion = is_file($stylePath) ? (string)filemtime($stylePath) : (string)time();
$modalManagerVersion = is_file($modalManagerPath) ? (string)filemtime($modalManagerPath) : (string)time();
$recoverErrorCode = isset($_GET['error']) ? (string)$_GET['error'] : '';
$recoverFieldError = ['field' => '', 'message' => ''];
if ($recoverErrorCode === 'empty') {
    $recoverFieldError = ['field' => 'email', 'message' => 'Debes ingresar tu correo electronico.'];
} elseif ($recoverErrorCode === 'invalidemail') {
    $recoverFieldError = ['field' => 'email', 'message' => 'El formato del correo no es valido.'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="assets/img/balonfutbol.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css?v=<?= urlencode($styleVersion) ?>" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-shell container">
    <section class="auth-panel reveal-up">
        <div class="auth-kicker">Recuperación</div>
        <h1 class="auth-title">Te ayudamos a volver a entrar en minutos.</h1>
        <p class="auth-copy mb-0">
            Envía el correo asociado a tu cuenta y recibirás el enlace para crear una nueva contraseña con vigencia corta y segura.
        </p>
        <div class="auth-badges">
            <div class="auth-badge"><span></span> Token expira en 5 minutos</div>
            <div class="auth-badge"><span></span> Hora de Bogotá</div>
            <div class="auth-badge"><span></span> Flujo protegido</div>
        </div>
    </section>

    <section class="auth-card card reveal-up delay-1">
        <div class="card-header">
            <h2 class="text-center mb-1">Recuperar contrasena</h2>
            <p class="auth-subtitle text-center mb-0">Solo necesitamos tu correo registrado.</p>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?url=enviar">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electronico</label>
                    <input type="email" id="email" name="email" class="form-control auth-input" placeholder="tu@correo.com" required>
                    <div class="invalid-feedback" id="emailFeedback">Correo invalido.</div>
                </div>
                <div class="auth-links">
                    <button class="btn btn-primary auth-action flex-grow-1" type="submit">Enviar enlace</button>
                    <a href="index.php?url=login" class="btn btn-outline-secondary auth-action flex-grow-1">Volver</a>
                </div>
            </form>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/modal-manager.js?v=<?= urlencode($modalManagerVersion) ?>"></script>
<?php
if ($recoverFieldError['field'] === '') {
    ?>
    <div class="modal fade" id="recoverInfoModal" tabindex="-1" aria-labelledby="recoverInfoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white border border-info border-3">
          <div class="modal-header border-0 justify-content-center pb-0">
            <h5 class="modal-title text-center w-100" id="recoverInfoModalLabel">Recuperar contrasena</h5>
          </div>
          <div class="modal-body text-center pt-2">
            <img src="assets/img/editar.gif" alt="Recuperar contrasena" class="img-fluid mb-3" style="max-height: 180px;">
            <p class="mb-0">Ingresa tu correo para enviarte las instrucciones de restablecimiento.</p>
          </div>
          <div class="modal-footer border-0 justify-content-center pt-0">
            <button type="button" class="btn btn-primary px-4 text-white" data-bs-dismiss="modal">Aceptar</button>
          </div>
        </div>
      </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      const modalElement = document.getElementById('recoverInfoModal');
      if (modalElement && window.bootstrap && bootstrap.Modal) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement, { backdrop: 'static', keyboard: false });
        modal.show();
      }
    });
    </script>
    <?php
}
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const field = '<?= htmlspecialchars($recoverFieldError['field'], ENT_QUOTES, 'UTF-8') ?>';
  const message = '<?= htmlspecialchars($recoverFieldError['message'], ENT_QUOTES, 'UTF-8') ?>';

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
</body>
</html>
