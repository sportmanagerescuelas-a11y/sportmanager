<?php
$viewData = get_defined_vars();
$paymentResult = is_array($viewData['paymentResult'] ?? null) ? $viewData['paymentResult'] : [];
$paymentDetails = is_array($viewData['paymentDetails'] ?? null) ? $viewData['paymentDetails'] : [];
$paymentError = (string)($viewData['paymentError'] ?? '');
$invoiceResult = is_array($viewData['invoiceResult'] ?? null) ? $viewData['invoiceResult'] : [];
$refreshUrl = (string)($viewData['refreshUrl'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de pago</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(180deg, #eef3f9 0%, #f7f9fc 100%);
            min-height: 100vh;
        }
        .result-shell {
            max-width: 760px;
        }
    </style>
</head>
<body>
    <div class="container py-5 result-shell">
        <h2 class="mb-4 fw-bold">Resultado de transaccion</h2>
        <?php require APP_PATH . "/views/partials/payment_result_card.php"; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

