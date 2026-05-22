<?php
$viewData = get_defined_vars();
$usuariosPendientes = is_array($viewData['usuariosPendientes'] ?? null) ? $viewData['usuariosPendientes'] : [];
$usuariosAprobados = is_array($viewData['usuariosAprobados'] ?? null) ? $viewData['usuariosAprobados'] : [];

if (!function_exists('rol_nombre')) {
function rol_nombre(int $rol): string
{
    return [1 => 'Acudiente', 2 => 'Entrenador', 3 => 'Administrador', 4 => 'Superadmin'][$rol] ?? 'Desconocido';
}
}
?>
<br>
<br>
<div class="container mt-5">
    <h2 class="text-center mb-4">Usuarios Pendientes</h2>
    <table class="table table-bordered text-center">
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Escuela</th><th>Rol solicitado</th><th>Pago admin</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach ($usuariosPendientes as $user): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$user['id_usuario']) ?></td>
                    <td><?= htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']) ?></td>
                    <td><?= htmlspecialchars((string)$user['email']) ?></td>
                    <td><?= htmlspecialchars((string)($user['nombre_escuela'] ?? 'Sin escuela')) ?></td>
                    <td><?= htmlspecialchars(rol_nombre((int)$user['id_rol'])) ?></td>
                    <td>
                        <?php if ((int)$user['id_rol'] === 3): ?>
                            <?php if (!empty($user['comprobante_path'])): ?>
                                <a href="<?= htmlspecialchars((string)$user['comprobante_path']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">Ver comprobante</a>
                            <?php endif; ?>
                            <span class="badge <?= ($user['estado_pago_admin'] ?? '') === 'verificado' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= htmlspecialchars((string)($user['estado_pago_admin'] ?? 'pendiente')) ?>
                            </span>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int)$user['id_rol'] === 3 && ($user['estado_pago_admin'] ?? '') !== 'verificado'): ?>
                            <form action="controllers/adminUsuarioController.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string)$user['id_usuario']) ?>">
                                <button name="verificar_pago" class="btn btn-warning btn-sm">Verificar pago</button>
                            </form>
                        <?php endif; ?>
                        <form action="controllers/adminUsuarioController.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string)$user['id_usuario']) ?>">
                            <button name="aprobar" class="btn btn-success btn-sm">Aprobar</button>
                        </form>
                        <form action="controllers/adminUsuarioController.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars((string)$user['id_usuario']) ?>">
                            <button name="rechazar" class="btn btn-danger btn-sm">Rechazar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr class="mt-5 mb-4">
    <h2 class="text-center mb-4">Usuarios Activos y Deshabilitados</h2>
    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Escuela</th><th>Rol</th><th>Deportistas</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach ($usuariosAprobados as $user): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$user['id_usuario']) ?></td>
                    <td><?= htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']) ?></td>
                    <td><?= htmlspecialchars((string)$user['email']) ?></td>
                    <td><?= htmlspecialchars((string)($user['nombre_escuela'] ?? 'Sin escuela')) ?></td>
                    <td><?= htmlspecialchars(rol_nombre((int)$user['id_rol'])) ?></td>
                    <td><?= (int)$user['total_deportistas'] ?></td>
                    <td><?= htmlspecialchars((string)$user['estado']) ?></td>
                    <td>
                        <a href="index.php?url=editar_usuario&id=<?= urlencode((string)$user['id_usuario']) ?>" class="btn btn-primary btn-sm">Editar</a>
                        <a href="index.php?url=ver_deportistas_usuario&id=<?= urlencode((string)$user['id_usuario']) ?>" class="btn btn-info btn-sm">Ver Deportistas</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="index.php?url=dashboard" class="btn btn-primary mt-3">Volver</a>
</div>
<br>
