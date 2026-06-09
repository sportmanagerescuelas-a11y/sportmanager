<?php
$viewData = get_defined_vars();
$user = is_array($viewData['user'] ?? null) ? $viewData['user'] : null;
$school = is_array($viewData['school'] ?? null) ? $viewData['school'] : null;
$event = is_array($viewData['event'] ?? null) ? $viewData['event'] : null;
$methods = is_array($viewData['methods'] ?? null) ? $viewData['methods'] : [];
$quantity = (int)($viewData['quantity'] ?? 1);
$total = (float)($viewData['total'] ?? 0);
$error = (string)($viewData['error'] ?? '');
$selectedMethod = (string)($_POST['id_metodo_pago'] ?? ($methods[0]['id_metodo'] ?? ''));
?>
<br>
<br>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="mb-1">Pago de evento</h2>
                    <p class="text-muted mb-0"><?= htmlspecialchars((string)($school['nombre'] ?? 'Escuela'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <a href="eventos" class="btn btn-outline-secondary">Volver a eventos</a>
            </div>

            <?php if ($error !== ''): ?>
                <?php sm_render_alert($error, 'No se pudo completar el pago', 'danger', true); ?>
            <?php endif; ?>

            <?php if (!$user || !$school): ?>
                <?php sm_render_alert('Tu usuario no tiene una escuela asociada.', 'Pago no disponible', 'warning', true); ?>
            <?php elseif (!$event): ?>
                <?php sm_render_alert('El evento no esta disponible para tu escuela.', 'Evento no disponible', 'warning', true); ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-7">
                                <h5 class="mb-2"><?= htmlspecialchars((string)$event['titulo'], ENT_QUOTES, 'UTF-8') ?></h5>
                                <div class="text-muted">
                                    <?= htmlspecialchars((string)$event['fecha'], ENT_QUOTES, 'UTF-8') ?>
                                    · <?= htmlspecialchars((string)$event['tipo_evento'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <div class="small text-muted">Total</div>
                                <div class="h3 mb-0">$<?= number_format($total, 0, ',', '.') ?></div>
                                <div class="small text-muted"><?= $quantity ?> registro(s)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($total <= 0): ?>
                    <?php sm_render_alert('Este evento no requiere pago.', 'Evento gratuito', 'info', true); ?>
                <?php elseif (count($methods) === 0): ?>
                    <?php sm_render_alert('La escuela aun no tiene metodos de pago activos.', 'Sin metodos de pago', 'warning', true); ?>
                <?php else: ?>
                    <form action="pago_evento" method="POST" enctype="multipart/form-data" class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <input type="hidden" name="id_evento" value="<?= htmlspecialchars((string)$event['id_evento'], ENT_QUOTES, 'UTF-8') ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="id_metodo_pago">Metodo de pago</label>
                                <select class="form-select" id="id_metodo_pago" name="id_metodo_pago" required>
                                    <?php foreach ($methods as $method): ?>
                                        <?php
                                        $methodId = (string)($method['id_metodo'] ?? '');
                                        $methodQr = (string)($method['qr_path'] ?? '');
                                        ?>
                                        <option value="<?= htmlspecialchars($methodId, ENT_QUOTES, 'UTF-8') ?>" data-qr="<?= htmlspecialchars($methodQr, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedMethod === $methodId ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string)($method['nombre_entidad'] ?? 'Metodo'), ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="methodQrPreview" class="mb-3 d-none">
                                <img src="" alt="QR metodo de pago" class="img-fluid border rounded p-2 bg-white" style="max-height: 220px;">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold" for="comprobante">Comprobante</label>
                                <input type="file" class="form-control" id="comprobante" name="comprobante" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-success">Enviar comprobante</button>
                                <a href="eventos" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('id_metodo_pago');
    const preview = document.getElementById('methodQrPreview');
    if (!select || !preview) {
        return;
    }
    const image = preview.querySelector('img');
    const refreshPreview = function () {
        const option = select.options[select.selectedIndex];
        const qr = option ? option.getAttribute('data-qr') : '';
        if (qr && image) {
            image.src = qr;
            preview.classList.remove('d-none');
        } else {
            if (image) {
                image.removeAttribute('src');
            }
            preview.classList.add('d-none');
        }
    };
    select.addEventListener('change', refreshPreview);
    refreshPreview();
});
</script>
