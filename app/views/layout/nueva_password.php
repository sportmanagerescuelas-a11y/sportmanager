<?php
$token = (string)($_GET['token'] ?? '');
$stylePath = __DIR__ . '/../../../assets/css/style.css';
$passwordTogglePath = __DIR__ . '/../../../assets/js/password-toggle.js';
$styleVersion = is_file($stylePath) ? (string)filemtime($stylePath) : (string)time();
$passwordToggleVersion = is_file($passwordTogglePath) ? (string)filemtime($passwordTogglePath) : (string)time();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css?v=<?= urlencode($styleVersion) ?>" rel="stylesheet">
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
<script src="assets/js/password-toggle.js?v=<?= urlencode($passwordToggleVersion) ?>"></script>
</body>
</html>
