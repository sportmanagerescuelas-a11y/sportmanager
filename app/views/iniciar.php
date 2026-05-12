<?php
$viewData = get_defined_vars();
$transaction = is_array($viewData['transaction'] ?? null) ? $viewData['transaction'] : null;
$payuContext = is_array($viewData['payuContext'] ?? null) ? $viewData['payuContext'] : [];

if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(180deg, #f4f8ff 0%, #eef2f7 100%);
            min-height: 100vh;
        }
        .pay-shell {
            max-width: 920px;
        }
        .pay-title {
            color: #123a66;
            font-weight: 800;
            letter-spacing: 0.2px;
        }
    </style>
</head>
<body>
    <div class="container py-5 pay-shell">
        <h2 class="mb-3 pay-title">Procesar pago</h2>

        <?php if (!empty($transaction) && is_array($transaction)): ?>
            <?php
            $estado = $transaction["estado"]["label"] ?? "Estado desconocido";
            $estadoCode = (int)($transaction["estado"]["code"] ?? 0);
            $alertClass = "alert-secondary";
            if ($estadoCode === 4) $alertClass = "alert-success";
            if ($estadoCode === 6 || $estadoCode === 104) $alertClass = "alert-danger";
            if ($estadoCode === 7) $alertClass = "alert-warning";
            ?>
            <div class="alert shadow-sm <?= htmlspecialchars($alertClass, ENT_QUOTES, "UTF-8") ?>">
                <strong>Resultado PayU:</strong> <?= htmlspecialchars($estado, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <?php require APP_PATH . "/views/partials/payu_form.php"; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
