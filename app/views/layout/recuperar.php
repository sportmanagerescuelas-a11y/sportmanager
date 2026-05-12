<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
<div class="card p-4 shadow">
<h4>Recuperar contraseña</h4>
<p class="text-muted mb-3">Escribe el correo electr&oacute;nico asociado a tu cuenta y te enviaremos las instrucciones para restablecer tu contrase&ntilde;a.</p>
<form method="POST" action="index.php?url=enviar">
<label for="email" class="form-label">Correo electr&oacute;nico</label>
<input type="email" id="email" name="email" class="form-control" placeholder="tu@correo.com" required>
<button class="btn btn-primary mt-3">Enviar</button>
</form>
</div>
</div>
</body>
</html>
