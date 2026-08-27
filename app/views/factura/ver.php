<?php
$viewData = get_defined_vars();
$factura = is_array($viewData['factura'] ?? null) ? $viewData['factura'] : [];
?>

<div class="container py-5 mt-4 school-style-page invoice-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <a href="index.php?action=listar" class="btn btn-outline-secondary">
            Volver al listado
        </a>

        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <?php if (!empty($factura['comprobante_path'])): ?>
                <a href="index.php?action=comprobante&id=<?= urlencode((string)($factura['id_factura'] ?? '')) ?>" class="btn btn-outline-primary">Ver comprobante</a>
                <a href="index.php?action=subir_comprobante&id=<?= urlencode((string)($factura['id_factura'] ?? '')) ?>" class="btn btn-outline-secondary">Reemplazar comprobante</a>
            <?php else: ?>
                <a href="index.php?action=subir_comprobante&id=<?= urlencode((string)($factura['id_factura'] ?? '')) ?>" class="btn btn-outline-secondary">Subir comprobante</a>
            <?php endif; ?>
            <a href="index.php?action=pdf&id=<?= urlencode((string)($factura['id_factura'] ?? '')) ?>" class="btn btn-outline-danger" target="_blank" rel="noopener noreferrer">Descargar PDF</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <h4 class="mb-0">Factura Electronica</h4>
            <span class="badge text-bg-primary">N°: <?= str_pad((int)($factura['id_factura'] ?? 0), 6, "0", STR_PAD_LEFT) ?></span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="invoice-info-card h-100">
                        <h6 class="mb-3 text-muted">De / Empresa</h6>
                        <div><strong>Gestor Deportivo</strong></div>
                        <div>Atendido por: <?= htmlspecialchars((string)($factura['nombre_usuario'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="invoice-info-card h-100">
                        <h6 class="mb-3 text-muted">Para / Deportista</h6>
                        <div>Deportista: <strong><?= htmlspecialchars((string)($factura['nombre_deportista'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></strong></div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>Concepto / Evento</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Tipo de Pago</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Concepto: <?= htmlspecialchars((string)($factura['nombre_evento'] ?? $factura['descripcion'] ?? 'Sin concepto'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-center"><?= max(1, (int)($factura['cantidad'] ?? 1)) ?></td>
                            <td class="text-center">
                                <span class="badge text-bg-success">
                                    <?= htmlspecialchars(strtoupper((string)($factura['metodo_pago_texto'] ?? 'N/A')), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-end">$<?= number_format((float)($factura['total'] ?? $factura['monto'] ?? 0), 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-lg-4 col-sm-6 ms-auto">
                    <div class="invoice-summary-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Subtotal</strong>
                            <span>$<?= number_format((float)($factura['total'] ?? $factura['monto'] ?? 0), 2) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center fs-5">
                            <strong>Total</strong>
                            <strong>$<?= number_format((float)($factura['total'] ?? $factura['monto'] ?? 0), 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
