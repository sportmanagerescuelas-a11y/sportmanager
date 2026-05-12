<?php
$viewData = get_defined_vars();
$facturas = is_array($viewData['facturas'] ?? null) ? $viewData['facturas'] : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Facturas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container my-5">
    
    <div class="mb-4">
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle"></i> Volver al Panel Principal
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <h4 class="mb-0">📜 Listado de Facturas Registradas</h4>
            <span class="badge bg-primary"><?= count($facturas) ?> Factura(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID Factura</th>
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
                                            <i class="bi bi-eye"></i> Visualizar
                                        </a>
                                        
                                        <a href="index.php?action=pdf&id=<?= urlencode((string)($f['id_factura'] ?? '')) ?>" target="_blank" class="btn btn-sm btn-danger">
                                            <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                                        </a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
