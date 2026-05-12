<?php
$viewData = get_defined_vars();
$user = is_array($viewData['user'] ?? null) ? $viewData['user'] : [
    'id_usuario' => '',
    'nombres' => '',
    'apellidos' => '',
    'email' => '',
    'telefono' => '',
    'id_rol' => 1,
    'estado' => '',
];
?>
<br>
<br>
<div class="container mt-5">
    <h2>Editar Usuario</h2>
    <form action="controllers/editarUsuarioController.php" method="POST">
        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string)$user['id_usuario']) ?>">
        <input type="text" name="nombres" value="<?= htmlspecialchars((string)$user['nombres']) ?>" class="form-control mb-2">
        <input type="text" name="apellidos" value="<?= htmlspecialchars((string)$user['apellidos']) ?>" class="form-control mb-2">
        <input type="email" name="email" value="<?= htmlspecialchars((string)$user['email']) ?>" class="form-control mb-2">
        <input type="text" name="telefono" value="<?= htmlspecialchars((string)$user['telefono']) ?>" class="form-control mb-2">
        <input type="password" name="nueva_contrasena" class="form-control mb-2" placeholder="Nueva contrasena (opcional)">

        <select name="id_rol" class="form-control mb-2">
            <option value="1" <?= (int)$user['id_rol'] === 1 ? 'selected' : '' ?>>Acudiente</option>
            <option value="2" <?= (int)$user['id_rol'] === 2 ? 'selected' : '' ?>>Entrenador</option>
            <option value="3" <?= (int)$user['id_rol'] === 3 ? 'selected' : '' ?>>Administrador</option>
        </select>

        <div class="mb-3">
            <label>Estado actual:</label><br>
            <?php if ($user['estado'] === 'aprobado'): ?>
                <span class="badge bg-success">Activo</span>
            <?php else: ?>
                <span class="badge bg-danger">Deshabilitado</span>
            <?php endif; ?>
        </div>

        <a href="admin_usuarios.php" class="btn btn-secondary">Volver</a>
        <button type="submit" name="accion" value="guardar" class="btn btn-success">Actualizar</button>
        <?php if ($user['estado'] === 'aprobado'): ?>
            <button type="submit" name="accion" value="deshabilitar" class="btn btn-warning">Deshabilitar</button>
        <?php else: ?>
            <button type="submit" name="accion" value="activar" class="btn btn-primary">Activar</button>
        <?php endif; ?>
    </form>
</div>
