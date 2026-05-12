<?php
$viewData = get_defined_vars();
$factura = is_array($viewData['factura'] ?? null) ? $viewData['factura'] : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #<?= htmlspecialchars((string)($factura['id_factura'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="d-flex justify-content-between mb-4">
        <a href="index.php?action=listar" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Listado
        </a>
        
        <a href="index.php?action=pdf&id=<?= urlencode((string)($factura['id_factura'] ?? '')) ?>" target="_blank" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <h4 class="mb-0">Factura Electrónica</h4>
            <span>N°: <strong><?= str_pad($factura['id_factura'] ?? 0, 6, "0", STR_PAD_LEFT) ?></strong></span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="mb-3 text-muted">De / Empresa:</h6>
                    <div><strong>Gestor Deportivo</strong></div>
                    <div>Atendido por: <?= htmlspecialchars((string)($factura['nombre_usuario'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div class="col-sm-6">
                    <h6 class="mb-3 text-muted">Para / Deportista:</h6>
                    <div>Deportista: <strong><?= htmlspecialchars((string)($factura['nombre_deportista'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></strong></div>
                </div>
            </div>

            <div class="table-responsive-sm">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Concepto / Evento</th>
                            <th class="text-center">Tipo de Pago</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Inscripción: <?= htmlspecialchars((string)($factura['nombre_evento'] ?? 'Sin Evento'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-center">
                                <span class="badge bg-success">
                                    <?= htmlspecialchars(strtoupper((string)($factura['metodo_pago_texto'] ?? 'N/A')), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-end">$<?= number_format($factura['total'] ?? $factura['monto'] ?? 0, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-lg-4 col-sm-5 ms-auto">
                    <table class="table table-clear">
                        <tbody>
                            <tr>
                                <td class="left"><strong>Subtotal</strong></td>
                                <td class="text-end">$<?= number_format($factura['total'] ?? $factura['monto'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td class="left"><strong>Total</strong></td>
                                <td class="text-end"><strong>$<?= number_format($factura['total'] ?? $factura['monto'] ?? 0, 2) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
