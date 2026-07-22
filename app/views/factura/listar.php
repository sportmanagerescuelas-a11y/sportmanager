<?php
$viewData = get_defined_vars();
$facturas = is_array($viewData['facturas'] ?? null) ? $viewData['facturas'] : [];
?>

<div class="container my-5 pt-5 school-style-page payments-page">
    
    <div class="mb-4">
        <a href="panel" class="btn btn-secondary">
            Volver al Panel Principal
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <h4 class="mb-0">📜 Listado de Pagos Registrados</h4>
            <span class="badge bg-primary"><?= count($facturas) ?> Pago(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID Pago</th>
                            <th>Deportista</th>
                            <th>Evento</th>
                            <th>Tipo de Pago</th>
                            <th class="text-end">Monto</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($facturas) > 0): ?>
                            <?php foreach ($facturas as $f): ?>
                                <tr>
                                    <td><strong>#<?= str_pad((string)($f['id_factura'] ?? 0), 5, "0", STR_PAD_LEFT) ?></strong></td>
                                    <td><?= htmlspecialchars((string)($f['nombre_deportista'] ?? 'Sin Nombre'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($f['nombre_evento'] ?? 'Sin Evento'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?= htmlspecialchars(strtoupper((string)($f['metodo_pago_texto'] ?? 'N/A')), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="text-end">$<?= number_format($f['total'] ?? $f['monto'] ?? 0, 2) ?></td>
                                    <td class="text-center">
                                        <a href="index.php?action=ver&id=<?= urlencode((string)($f['id_factura'] ?? '')) ?>" class="btn btn-sm btn-info text-white">
                                            Visualizar
                                        </a>
                                        
                                        <a href="index.php?action=pdf&id=<?= urlencode((string)($f['id_factura'] ?? '')) ?>" class="btn btn-sm btn-danger">
                                            Descargar PDF
                                        </a>
                                        <?php if (!empty($f['comprobante_path'])): ?>
                                            <a href="index.php?action=comprobante&id=<?= urlencode((string)($f['id_factura'] ?? '')) ?>" class="btn btn-sm btn-outline-secondary">
                                                Comprobante
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center p-4">No hay facturas registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
