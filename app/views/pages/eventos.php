<?php
$viewData = get_defined_vars();
$eventos = is_array($viewData['eventos'] ?? null) ? $viewData['eventos'] : [];
$rol = (int)($_SESSION['rol'] ?? 0);
?>
<br>
<br>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Eventos</h2>
    <?php if ($rol === 3): ?>
        <div class="mb-3 text-end">
            <a href="crear_evento.php" class="btn btn-success">+ Crear Evento</a>
        </div>
    <?php endif; ?>
    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr><th>Titulo</th><th>Fecha</th><th>Tipo</th><th>Costo</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach ($eventos as $e): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$e['titulo']) ?></td>
                    <td><?= htmlspecialchars((string)$e['fecha']) ?></td>
                    <td><?= htmlspecialchars((string)$e['tipo_evento']) ?></td>
                    <td>$<?= htmlspecialchars((string)$e['costo']) ?></td>
                    <td>
                        <?php if ($rol === 3): ?>
                            <a href="editar_evento.php?id=<?= urlencode((string)$e['id_evento']) ?>" class="btn btn-warning btn-sm">Editar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-primary mt-3">Volver</a>
</div>
