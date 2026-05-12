<?php
$viewData = get_defined_vars();
$escuelas = is_array($viewData['escuelas'] ?? null) ? $viewData['escuelas'] : [];
$error = (string)($viewData['error'] ?? '');
?>
<br>
<br>
<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Gestion de Escuelas</h2>
        <a href="index.php?url=crear_escuela" class="btn btn-success">+ Crear escuela</a>
    </div>

    <?php if (isset($_GET['created']) && $_GET['created'] === '1'): ?>
        <?php sm_render_alert('Escuela creada correctamente. Ya aparece disponible para los usuarios en el registro.', 'Escuela creada', 'success', true); ?>
    <?php endif; ?>
    <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
        <?php sm_render_alert('Escuela actualizada correctamente.', 'Cambios guardados', 'success', true); ?>
    <?php endif; ?>
    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
        <?php sm_render_alert('Escuela eliminada correctamente.', 'Escuela eliminada', 'success', true); ?>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <?php sm_render_alert($error, 'No se pudo completar la accion', 'danger', true); ?>
    <?php endif; ?>

    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Disciplina</th>
                <th>Telefono</th>
                <th>Correo</th>
                <th>Mensualidad</th>
                <th>Usuarios</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($escuelas)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No hay escuelas registradas.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($escuelas as $escuela): ?>
                <?php
                $id = (string)($escuela->id_escuela ?? '');
                $totalUsuarios = (int)($escuela->total_usuarios ?? 0);
                ?>
                <tr>
                    <td><?= htmlspecialchars((string)($escuela->nombre ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string)($escuela->disciplina ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string)($escuela->telefono ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string)($escuela->correo ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>$<?= number_format((float)($escuela->valor_mensualidad ?? 0), 2, ',', '.') ?></td>
                    <td><?= $totalUsuarios ?></td>
                    <td>
                        <a href="index.php?url=editar_escuela&id=<?= urlencode($id) ?>" class="btn btn-warning btn-sm">Editar</a>
                        <?php if ($totalUsuarios === 0): ?>
                            <a href="index.php?url=eliminar_escuela&id=<?= urlencode($id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Seguro que deseas eliminar esta escuela?');">Eliminar</a>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary btn-sm" disabled>Eliminar</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="index.php?url=dashboard" class="btn btn-primary mt-3">Volver al panel</a>
</div>
