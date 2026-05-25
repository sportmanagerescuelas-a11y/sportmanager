<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="assets/img/balonfutbol.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
<div class="container text-center"></div>
<div class="modal fade" id="recoverMessageModal" tabindex="-1" aria-labelledby="recoverMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-white border border-success border-3">
      <div class="modal-header border-0 justify-content-center pb-0">
        <h5 class="modal-title text-center w-100" id="recoverMessageModalLabel">Solicitud enviada</h5>
      </div>
      <div class="modal-body text-center pt-2">
        <img src="assets/img/controlar.gif" alt="Solicitud enviada" class="img-fluid mb-3" style="max-height: 180px;">
        <p class="mb-0">Si el correo existe o la contrasena fue actualizada, recibiras instrucciones.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center pt-0">
        <a href="index.php?url=login" class="btn btn-success px-4">Aceptar</a>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalElement = document.getElementById('recoverMessageModal');
  if (modalElement && window.bootstrap && bootstrap.Modal) {
    const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false });
    modal.show();
  }
});
</script>
</body>
</html>
