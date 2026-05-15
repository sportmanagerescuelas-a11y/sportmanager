<?php
$viewData = get_defined_vars();
$facturasUsuario = is_array($viewData['facturasUsuario'] ?? null) ? $viewData['facturasUsuario'] : [];
$idEvento = (int)($viewData['idEvento'] ?? 0);
?>
<section class="container py-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1">Mis pagos</h2>
            <p class="text-muted mb-0">Historial de facturas del usuario activo.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($idEvento > 0): ?>
                <a href="index.php?url=iniciar&id_evento=<?= urlencode((string)$idEvento) ?>" class="btn btn-primary">
                    Pagar evento
                </a>
            <?php endif; ?>
            <a href="index.php?url=dashboard" class="btn btn-outline-secondary">Volver al dashboard</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="mb-0">Facturas registradas</h5>
                <span class="badge text-bg-primary"><?= count($facturasUsuario) ?> registro(s)</span>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if (count($facturasUsuario) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Factura</th>
                                <th>Referencia</th>
                                <th>Fecha</th>
                                <th>Deportista</th>
                                <th>Evento</th>
                                <th>Metodo</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($facturasUsuario as $factura): ?>
                                <tr>
                                    <td class="fw-semibold">#<?= htmlspecialchars((string)($factura['id_factura'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($factura['numero_factura'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($factura['fecha_emision'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($factura['nombre_deportista'] ?? 'No aplica'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($factura['nombre_evento'] ?? $factura['descripcion'] ?? 'Sin descripcion'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge text-bg-success"><?= htmlspecialchars((string)($factura['metodo_pago_texto'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="text-end fw-semibold">$<?= number_format((float)($factura['monto'] ?? 0), 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <a href="index.php?action=ver&id=<?= urlencode((string)($factura['id_factura'] ?? '')) ?>" class="btn btn-sm btn-outline-primary">
                                            Ver comprobante
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <h6 class="mb-2">Aun no tienes facturas registradas.</h6>
                    <p class="text-muted mb-0">Cuando completes un pago exitoso, aparecera aqui.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

