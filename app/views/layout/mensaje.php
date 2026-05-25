<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
<div class="container text-center">
<a href="index.php" class="btn btn-primary">Volver</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php
$actions = ['<a href="index.php" class="btn btn-success">Volver</a>'];
sm_render_modal_message(
    'recoverMessageModal',
    'Solicitud enviada',
    'Si el correo existe o la contrasena fue actualizada, recibiras instrucciones.',
    'success',
    $actions
);
?>
</body>
</html>
