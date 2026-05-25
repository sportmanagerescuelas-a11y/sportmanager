<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="assets/img/balonfutbol.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php
$recoverErrorCode = isset($_GET['error']) ? (string)$_GET['error'] : '';
$recoverFieldError = ['field' => '', 'message' => ''];
if ($recoverErrorCode === 'empty') {
    $recoverFieldError = ['field' => 'email', 'message' => 'Debes ingresar tu correo electronico.'];
} elseif ($recoverErrorCode === 'invalidemail') {
    $recoverFieldError = ['field' => 'email', 'message' => 'El formato del correo no es valido.'];
}
?>
<div class="container mt-5">
<div class="card p-4 shadow">
<h4>Recuperar contrasena</h4>
<p class="text-muted mb-3">Escribe el correo electronico asociado a tu cuenta y te enviaremos las instrucciones para restablecer tu contrasena.</p>
<form method="POST" action="index.php?url=enviar">
<label for="email" class="form-label">Correo electronico</label>
<input type="email" id="email" name="email" class="form-control" placeholder="tu@correo.com" required>
<div class="invalid-feedback" id="emailFeedback">Correo invalido.</div>
<div class="mt-3 d-flex gap-2">
  <button class="btn btn-primary" type="submit">Enviar</button>
  <a href="index.php?url=login" class="btn btn-secondary text-white">Cancelar</a>
</div>
</form>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
        const modal = new bootstrap.Modal(modalElement);
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
