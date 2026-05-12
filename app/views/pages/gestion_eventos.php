<?php
$viewData = get_defined_vars();
$eventos = is_array($viewData['eventos'] ?? null) ? $viewData['eventos'] : [];
?>
<br>
<br>
<div class="container mt-5">
    <h2>Gestion de Eventos</h2>
    <a href="crear_evento.php" class="btn btn-success mb-3">+ Crear Evento</a>
    <table class="table table-bordered">
        <thead>
            <tr><th>Titulo</th><th>Fecha</th><th>Tipo</th><th>Inscritos</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach ($eventos as $e): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$e->titulo) ?></td>
                    <td><?= htmlspecialchars((string)$e->fecha) ?></td>
                    <td><?= htmlspecialchars((string)$e->tipo_evento) ?></td>
                    <td><?= (int)$e->total_inscritos ?></td>
                    <td>
                        <?php if ((int)$e->estado === 1): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="ver_inscritos.php?id=<?= urlencode((string)$e->id_evento) ?>" class="btn btn-primary btn-sm">Ver</a>
                        <a href="editar_evento.php?id=<?= urlencode((string)$e->id_evento) ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="toggle_evento.php?id=<?= urlencode((string)$e->id_evento) ?>" class="btn btn-sm <?= (int)$e->estado === 1 ? 'btn-danger' : 'btn-success' ?>">
                            <?= (int)$e->estado === 1 ? 'Inhabilitar' : 'Activar' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
