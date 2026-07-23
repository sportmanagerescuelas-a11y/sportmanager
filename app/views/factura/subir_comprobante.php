<?php
$viewData = get_defined_vars();
$factura = is_array($viewData['factura'] ?? null) ? $viewData['factura'] : [];
$error = (string)($viewData['error'] ?? '');
$facturaId = (int)($factura['id_factura'] ?? 0);
$facturaNumero = (string)($factura['numero_factura'] ?? '');
$facturaEvento = (string)($factura['nombre_evento'] ?? $factura['descripcion'] ?? 'Sin descripcion');
$facturaMonto = (float)($factura['monto'] ?? 0);
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">Subir comprobante</h2>
            <p class="text-muted mb-0">Adjunta el soporte manual de la factura #<?= htmlspecialchars((string)$facturaId, ENT_QUOTES, 'UTF-8') ?>.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="index.php?action=ver&id=<?= urlencode((string)$facturaId) ?>" class="btn btn-outline-primary">Volver a la factura</a>
            <a href="index.php?action=pdf&id=<?= urlencode((string)$facturaId) ?>" class="btn btn-danger" target="_blank" rel="noopener noreferrer">Descargar comprobante</a>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($factura['comprobante_path'])): ?>
        <div class="alert alert-info">
            Esta factura ya tiene un comprobante cargado. Si subes uno nuevo, reemplazará el actual.
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Resumen de la factura</h5>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Factura</dt>
                        <dd class="col-sm-7">#<?= htmlspecialchars((string)$facturaId, ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-5">Referencia</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars($facturaNumero !== '' ? $facturaNumero : 'N/A', ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-5">Evento</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars($facturaEvento, ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-5">Total</dt>
                        <dd class="col-sm-7">$<?= number_format($facturaMonto, 0, ',', '.') ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Formulario de carga</h5>
                    <p class="text-muted">El comprobante puede ser JPG, PNG, WEBP o PDF y debe pesar menos de 5 MB.</p>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="comprobante" class="form-label fw-semibold">Archivo del comprobante</label>
                            <input type="file" class="form-control" id="comprobante" name="comprobante" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">Guardar comprobante</button>
                            <a href="pagos" class="btn btn-outline-secondary">Volver a mis pagos</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
