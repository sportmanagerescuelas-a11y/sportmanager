<?php
$viewData = get_defined_vars();
$facturasUsuario = is_array($viewData['facturasUsuario'] ?? null) ? $viewData['facturasUsuario'] : [];
$idEvento = (int)($viewData['idEvento'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h3 class="mb-1">Mis pagos</h3>
                <p class="mb-0 small opacity-75">Consulta y visualiza tus comprobantes de pago.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($idEvento > 0): ?>
                    <a href="index.php?url=iniciar&id_evento=<?= urlencode((string)$idEvento) ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-credit-card me-1"></i> Ir a pagar
                    </a>
                <?php endif; ?>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left-circle me-1"></i> Volver al panel
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pagos del deportista activo</h5>
            <span class="badge bg-primary"><?= count($facturasUsuario) ?> registro(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>Factura</th>
                            <th>Fecha</th>
                            <th>Deportista</th>
                            <th>Evento</th>
                            <th>Metodo</th>
                            <th>Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($facturasUsuario)): ?>
                            <?php foreach ($facturasUsuario as $factura): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars((string)($factura['id_factura'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td><?= htmlspecialchars((string)($factura['fecha_emision'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($factura['nombre_deportista'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($factura['nombre_evento'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars((string)($factura['metodo_pago_texto'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="text-end"><strong>$<?= number_format((float)($factura['total'] ?? $factura['monto'] ?? 0), 0, ',', '.') ?></strong></td>
                                    <td class="text-center">
                                        <a href="index.php?action=ver&id=<?= urlencode((string)($factura['id_factura'] ?? '')) ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i> Ver comprobante
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No tienes pagos registrados todavia.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
