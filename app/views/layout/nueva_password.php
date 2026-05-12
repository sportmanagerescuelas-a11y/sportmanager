<?php
$token = (string)($_GET['token'] ?? '');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
<div class="card p-4 shadow">
<h4>Nueva contraseña</h4>
<form method="POST" action="index.php?url=guardar">
<input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
<input type="password" name="password" class="form-control" required>
<button class="btn btn-success mt-3">Guardar</button>
</form>
</div>
</div>
</body>
</html>
