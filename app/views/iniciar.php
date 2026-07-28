<?php
$viewData = get_defined_vars();
$transaction = is_array($viewData['transaction'] ?? null) ? $viewData['transaction'] : null;
$payuContext = is_array($viewData['payuContext'] ?? null) ? $viewData['payuContext'] : [];
?>

<section class="container py-5 mt-4 school-style-page payu-page">
    <div class="payu-page__intro">
        <nav class="payment-breadcrumb" aria-label="Navegación">
            <a href="home">Inicio</a>
            <span aria-hidden="true">/</span>
            <span>Pasarela de pago</span>
        </nav>

        <div class="payment-heading payment-heading--compact">
            <div>
                <span class="payment-eyebrow">Pago seguro</span>
                <h1>Procesar pago</h1>
                <p>Completa los datos de la pasarela con el mismo estilo visual del resto del sistema.</p>
            </div>
            <a href="panel" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
        </div>
    </div>

    <?php if (!empty($transaction) && is_array($transaction)): ?>
        <?php
        $estado = (string)($transaction['estado']['label'] ?? 'Estado desconocido');
        $estadoCode = (int)($transaction['estado']['code'] ?? 0);
        $alertClass = 'alert-secondary';
        if ($estadoCode === 4) {
            $alertClass = 'alert-success';
        } elseif ($estadoCode === 6 || $estadoCode === 104) {
            $alertClass = 'alert-danger';
        } elseif ($estadoCode === 7) {
            $alertClass = 'alert-warning';
        }
        ?>
        <div class="alert shadow-sm <?= htmlspecialchars($alertClass, ENT_QUOTES, 'UTF-8') ?>">
            <strong>Resultado PayU:</strong> <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="payu-page__card">
        <?php require APP_PATH . '/views/partials/payu_form.php'; ?>
    </div>
</section>
