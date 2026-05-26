<?php
$viewData = get_defined_vars();
$user = is_array($viewData['user'] ?? null) ? $viewData['user'] : [
    'id_usuario' => '',
    'id_escuela' => '',
    'nombres' => '',
    'apellidos' => '',
    'email' => '',
    'telefono' => '',
    'id_rol' => 1,
    'estado' => '',
];
$schools = is_array($viewData['schools'] ?? null) ? $viewData['schools'] : [];
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
        <input type="text" name="telefono" value="<?= htmlspecialchars((string)$user['telefono']) ?>" class="form-control mb-2" maxlength="10" pattern="\d{10}" inputmode="numeric">
        <input type="password" name="nueva_contrasena" class="form-control mb-2" placeholder="Nueva contrasena (opcional)">

        <select name="id_escuela" class="form-control mb-2">
            <option value="">Sin escuela</option>
            <?php foreach ($schools as $school): ?>
                <?php $schoolId = (string)($school->id_escuela ?? ''); ?>
                <option value="<?= htmlspecialchars($schoolId, ENT_QUOTES, 'UTF-8') ?>" <?= (string)$user['id_escuela'] === $schoolId ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string)(($school->nombre ?? '') . ' - ' . ($school->disciplina ?? '')), ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>

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

        <a href="admin_usuarios" class="btn btn-secondary">Volver</a>
        <button type="submit" name="accion" value="guardar" class="btn btn-success">Actualizar</button>
        <?php if ($user['estado'] === 'aprobado'): ?>
            <button type="submit" name="accion" value="deshabilitar" class="btn btn-warning">Deshabilitar</button>
        <?php else: ?>
            <button type="submit" name="accion" value="activar" class="btn btn-primary">Activar</button>
        <?php endif; ?>
    </form>
</div>
