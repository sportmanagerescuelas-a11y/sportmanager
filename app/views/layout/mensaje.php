<?php
$assetBase = '/sportmanager/';
$modalManagerPath = __DIR__ . '/../../../assets/js/modal-manager.js';
$modalManagerVersion = is_file($modalManagerPath) ? (string)filemtime($modalManagerPath) : (string)time();
$messageMode = (string)($messageMode ?? 'reset_sent');

$config = [
    'reset_sent' => [
        'title' => 'Solicitud enviada',
        'kicker' => 'Recuperacion de cuenta',
        'subtitle' => 'Revisa tu bandeja de entrada.',
        'image' => 'editar.gif',
        'body' => 'Si el correo existe, recibirás instrucciones para continuar.',
        'button' => 'Aceptar',
        'href' => 'login',
    ],
    'reset_failed' => [
        'title' => 'No se pudo enviar',
        'kicker' => 'Recuperacion de cuenta',
        'subtitle' => 'Hubo un problema con el correo.',
        'image' => 'editar.gif',
        'body' => 'Generamos la solicitud, pero el sistema no pudo entregar el mensaje. Revisa la configuracion de correo del servidor e intenta de nuevo.',
        'button' => 'Volver a intentar',
        'href' => 'recuperar',
    ],
    'password_success' => [
        'title' => 'Contrasena actualizada',
        'kicker' => 'Cambio exitoso',
        'subtitle' => 'Ya puedes iniciar sesion.',
        'image' => 'controlar.gif',
        'body' => 'Tu contrasena fue cambiada correctamente. Ahora puedes volver a acceder con tu nueva clave.',
        'button' => 'Ir al inicio',
        'href' => 'login',
    ],
    'athlete_payment_pending' => [
        'title' => 'Registro bloqueado',
        'kicker' => 'Pago pendiente',
        'subtitle' => 'Aun no puedes registrar deportistas.',
        'image' => 'silbato-deportivo.gif',
        'body' => 'Tu cuenta de acudiente sigue pendiente de validacion. Hasta que el administrador de la escuela apruebe el pago, no podras registrar deportistas.',
        'button' => 'Volver a mis deportistas',
        'href' => 'deportistas',
    ],
    'receipt_saved' => [
        'title' => 'Comprobante guardado',
        'kicker' => 'Carga completada',
        'subtitle' => 'Tu soporte quedo registrado.',
        'image' => 'controlar.gif',
        'body' => 'El comprobante fue guardado correctamente. Ahora puedes volver a tus pagos o continuar con la gestion que necesites.',
        'button' => 'Iniciar sesion',
        'href' => 'logout',
    ],
];

$current = $config[$messageMode] ?? $config['reset_sent'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="<?= htmlspecialchars($assetBase . 'assets/img/balonfutbol.png', ENT_QUOTES, 'UTF-8') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body.recover-message-page {
        min-height: 100vh;
        margin: 0;
        background:
            radial-gradient(circle at 18% 18%, rgba(64, 130, 255, 0.24), transparent 28%),
            radial-gradient(circle at 82% 22%, rgba(255, 255, 255, 0.16), transparent 24%),
            linear-gradient(135deg, #07111f 0%, #0f2340 48%, #eef4fb 48.8%, #f7fbff 100%);
        overflow: hidden;
        position: relative;
    }
    body.recover-message-page::before,
    body.recover-message-page::after {
        content: "";
        position: fixed;
        width: 26rem;
        height: 26rem;
        border-radius: 999px;
        filter: blur(16px);
        opacity: 0.35;
        pointer-events: none;
        z-index: 0;
        animation: smFloat 12s ease-in-out infinite;
    }
    body.recover-message-page::before {
        top: -7rem;
        right: -7rem;
        background: radial-gradient(circle, rgba(47, 127, 189, 0.75) 0%, rgba(47, 127, 189, 0) 68%);
    }
    body.recover-message-page::after {
        bottom: -8rem;
        left: -7rem;
        background: radial-gradient(circle, rgba(25, 135, 84, 0.55) 0%, rgba(25, 135, 84, 0) 68%);
        animation-delay: -4s;
    }
</style>
</head>
<body class="recover-message-page">
<div class="modal fade" id="recoverMessageModal" tabindex="-1" aria-labelledby="recoverMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
      <div class="modal-header border-0 justify-content-center text-white" style="background: linear-gradient(135deg, #07111f 0%, #16314c 55%, #2f7fbd 100%);">
        <h5 class="modal-title text-center w-100 fw-bold" id="recoverMessageModalLabel"><?= htmlspecialchars($current['title'], ENT_QUOTES, 'UTF-8') ?></h5>
      </div>
      <div class="modal-body text-center p-4 p-md-5">
        <div class="mb-2 text-uppercase fw-semibold" style="letter-spacing: .12em; color: #2f7fbd; font-size: .72rem;">
          <?= htmlspecialchars($current['kicker'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <h2 class="h4 mb-3"><?= htmlspecialchars($current['subtitle'], ENT_QUOTES, 'UTF-8') ?></h2>
        <img src="<?= htmlspecialchars($assetBase . 'assets/img/' . $current['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($current['title'], ENT_QUOTES, 'UTF-8') ?>" class="img-fluid mb-3" style="max-height: 180px;">
        <p class="mb-0 fs-6"><?= htmlspecialchars($current['body'], ENT_QUOTES, 'UTF-8') ?></p>
      </div>
      <div class="modal-footer border-0 justify-content-center pb-4 pt-0">
        <a href="<?= htmlspecialchars($current['href'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success px-4 py-2 fw-semibold">
          <?= htmlspecialchars($current['button'], ENT_QUOTES, 'UTF-8') ?>
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/modal-manager.js?v=<?= urlencode($modalManagerVersion) ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalElement = document.getElementById('recoverMessageModal');
  if (modalElement && window.bootstrap && bootstrap.Modal) {
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
      backdrop: 'static',
      keyboard: false
    });
    modal.show();
  }
});
</script>
</body>
</html>
