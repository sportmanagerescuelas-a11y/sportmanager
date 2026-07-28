<?php
$viewData = get_defined_vars();
$paymentResult = is_array($viewData['paymentResult'] ?? null) ? $viewData['paymentResult'] : [];
$paymentDetails = is_array($viewData['paymentDetails'] ?? null) ? $viewData['paymentDetails'] : [];
$paymentError = (string)($viewData['paymentError'] ?? '');
$invoiceResult = is_array($viewData['invoiceResult'] ?? null) ? $viewData['invoiceResult'] : [];
$refreshUrl = (string)($viewData['refreshUrl'] ?? '');
$retryUrl = (string)($viewData['retryUrl'] ?? 'iniciar');
$nextUrl = (string)($viewData['nextUrl'] ?? '');
$nextLabel = (string)($viewData['nextLabel'] ?? 'Continuar');
?>

<section class="container py-5 mt-4 payment-result-page">
    <div class="payment-result-page__intro">
        <nav class="payment-breadcrumb" aria-label="Navegación">
            <a href="home">Inicio</a>
            <span aria-hidden="true">/</span>
            <span>Resultado de pago</span>
        </nav>

        <div class="payment-heading payment-heading--compact">
            <div>
                <span class="payment-eyebrow">Confirmación</span>
                <h1>Resultado de transacción</h1>
                <p>Consulta el estado final de tu pago y descarga el comprobante cuando esté disponible.</p>
            </div>
            <a href="pagos" class="btn btn-outline-secondary rounded-pill px-4">Volver a mis pagos</a>
        </div>
    </div>

    <div class="payment-result-page__card">
        <?php require APP_PATH . '/views/partials/payment_result_card.php'; ?>
    </div>
</section>
