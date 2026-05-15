<?php
$viewData = get_defined_vars();
$paymentResult = is_array($viewData['paymentResult'] ?? null) ? $viewData['paymentResult'] : [];
$paymentDetails = is_array($viewData['paymentDetails'] ?? null) ? $viewData['paymentDetails'] : [];
$paymentError = (string)($viewData['paymentError'] ?? '');
$invoiceResult = is_array($viewData['invoiceResult'] ?? null) ? $viewData['invoiceResult'] : [];
$refreshUrl = (string)($viewData['refreshUrl'] ?? '');
$retryUrl = (string)($viewData['retryUrl'] ?? 'index.php?url=iniciar');

$status = is_array($paymentResult['status'] ?? null) ? $paymentResult['status'] : [];
$statusTone = (string)($status['tone'] ?? 'secondary');
$statusLabel = (string)($status['label'] ?? 'Estado desconocido');
$statusKey = (string)($status['key'] ?? 'unknown');

$headerClass = 'bg-secondary';
if ($statusTone === 'success') {
    $headerClass = 'bg-success';
} elseif ($statusTone === 'danger') {
    $headerClass = 'bg-danger';
} elseif ($statusTone === 'warning') {
    $headerClass = 'bg-warning text-dark';
}
?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header <?= htmlspecialchars($headerClass, ENT_QUOTES, 'UTF-8') ?> py-3">
        <h5 class="mb-0 fw-bold"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></h5>
    </div>
    <div class="card-body p-4">
        <?php if ($paymentError !== ''): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($paymentError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (!empty($invoiceResult['message']) && ($statusKey === 'approved')): ?>
            <div class="alert <?= !empty($invoiceResult['saved']) ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars((string)$invoiceResult['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <h6 class="fw-bold mb-3">Datos de tu transaccion</h6>
        <div class="table-responsive">
            <table class="table align-middle mb-4">
                <tbody>
                    <?php foreach ($paymentDetails as $row): ?>
                        <tr>
                            <th class="text-muted fw-semibold" style="width: 45%;"><?= htmlspecialchars((string)($row['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></th>
                            <td><?= htmlspecialchars((string)($row['value'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if ($statusKey === 'pending' && $refreshUrl !== ''): ?>
                <a href="<?= htmlspecialchars($refreshUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning">Consultar estado</a>
            <?php endif; ?>

            <?php if ($statusKey === 'rejected' || $statusKey === 'error'): ?>
                <a href="<?= htmlspecialchars($retryUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-primary">Intentar de nuevo</a>
            <?php endif; ?>

            <a href="dashboard.php" class="btn btn-primary">Volver al dashboard</a>
        </div>
    </div>
</div>
