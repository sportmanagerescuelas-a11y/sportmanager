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
            <a href="crear-evento" class="btn btn-success">+ Crear Evento</a>
        </div>
    <?php endif; ?>
    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr><th>Titulo</th><th>Fecha</th><th>Tipo</th><th>Costo</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach ($eventos as $e): ?>
                <?php
                $idEvento = (int)($e['id_evento'] ?? 0);
                $costoEvento = (float)($e['costo'] ?? 0);
                $registeredQuantity = max(0, (int)($e['registered_quantity'] ?? 0));
                $urlPagar = 'pago_evento&id_evento=' . urlencode((string)$idEvento);
                ?>
                <tr>
                    <td><?= htmlspecialchars((string)$e['titulo']) ?></td>
                    <td><?= htmlspecialchars((string)$e['fecha']) ?></td>
                    <td><?= htmlspecialchars((string)$e['tipo_evento']) ?></td>
                    <td>$<?= htmlspecialchars((string)$e['costo']) ?></td>
                    <td>
                        <?php if ($rol === 3): ?>
                            <a href="editar-evento&id=<?= urlencode((string)$e['id_evento']) ?>" class="btn btn-warning btn-sm">Editar</a>
                        <?php else: ?>
                            <?php if ($costoEvento > 0): ?>
                                <?php if ($registeredQuantity > 0): ?>
                                    <a href="<?= htmlspecialchars($urlPagar, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success btn-sm px-3">Pagar</a>
                                <?php else: ?>
                                    <span class="badge text-bg-warning">Inscríbete para pagar</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Gratis</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="panel" class="btn btn-primary mt-3">Volver</a>
</div>
