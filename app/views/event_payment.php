<?php
$viewData = get_defined_vars();
$user = is_array($viewData['user'] ?? null) ? $viewData['user'] : null;
$school = is_array($viewData['school'] ?? null) ? $viewData['school'] : null;
$event = is_array($viewData['event'] ?? null) ? $viewData['event'] : null;
$methods = is_array($viewData['methods'] ?? null) ? $viewData['methods'] : [];
$quantity = max(0, (int)($viewData['quantity'] ?? 0));
$registeredQuantity = max(0, (int)($viewData['registeredQuantity'] ?? 0));
$paidQuantity = max(0, (int)($viewData['paidQuantity'] ?? 0));
$unitCost = (float)($viewData['unitCost'] ?? 0);
$total = (float)($viewData['total'] ?? 0);
$error = (string)($viewData['error'] ?? '');
$paymentToken = (string)($viewData['paymentToken'] ?? '');
$selectedMethod = (string)($_POST['id_metodo_pago'] ?? ($methods[0]['id_metodo'] ?? ''));
$userName = trim((string)($user['nombres'] ?? '') . ' ' . (string)($user['apellidos'] ?? ''));
?>
<section class="payment-page">
    <div class="container payment-page__container">
        <nav class="payment-breadcrumb" aria-label="Navegación">
            <a href="eventos">Eventos</a>
            <span aria-hidden="true">/</span>
            <span>Registrar pago</span>
        </nav>

        <div class="payment-heading">
            <div>
                <span class="payment-eyebrow">Pago seguro dentro de la plataforma</span>
                <h1>Completa el pago del evento</h1>
                <p>Selecciona una opción de la escuela y adjunta el soporte de la transacción.</p>
            </div>
            <a href="eventos" class="btn btn-outline-secondary rounded-pill px-4">Volver a eventos</a>
        </div>

        <?php if ($error !== ''): ?>
            <?php sm_render_alert($error, 'No se pudo completar el pago', 'danger', true); ?>
        <?php endif; ?>

        <?php if (!$user || !$school): ?>
            <?php sm_render_alert('Tu usuario no tiene una escuela asociada.', 'Pago no disponible', 'warning', true); ?>
        <?php elseif (!$event): ?>
            <?php sm_render_alert('El evento no está disponible para tu escuela.', 'Evento no disponible', 'warning', true); ?>
        <?php elseif ($registeredQuantity <= 0): ?>
            <div class="payment-empty-state">
                <span class="payment-empty-state__icon" aria-hidden="true">!</span>
                <h2>Primero realiza la inscripción</h2>
                <p>Debes inscribir al menos un deportista antes de registrar el pago de este evento.</p>
                <a href="dashboard" class="btn btn-primary rounded-pill px-4">Ir a inscripciones</a>
            </div>
        <?php elseif ($unitCost <= 0): ?>
            <?php sm_render_alert('Este evento no requiere pago.', 'Evento gratuito', 'info', true); ?>
        <?php elseif ($quantity <= 0): ?>
            <div class="payment-empty-state payment-empty-state--success">
                <span class="payment-empty-state__icon" aria-hidden="true">✓</span>
                <h2>Este evento ya está pagado</h2>
                <p>Todos los deportistas inscritos en tu cuenta ya tienen una factura registrada.</p>
                <a href="pagos" class="btn btn-primary rounded-pill px-4">Ver mis facturas</a>
            </div>
        <?php elseif (count($methods) === 0): ?>
            <?php sm_render_alert('La escuela aún no tiene métodos de pago activos. Comunícate con su administrador.', 'Sin métodos de pago', 'warning', true); ?>
        <?php else: ?>
            <form action="pago_evento" method="POST" enctype="multipart/form-data" class="payment-layout" id="eventPaymentForm">
                <input type="hidden" name="id_evento" value="<?= (int)$event['id_evento'] ?>">
                <input type="hidden" name="payment_token" value="<?= htmlspecialchars($paymentToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="payment-main">
                    <article class="payment-panel">
                        <div class="payment-panel__step">1</div>
                        <div class="payment-panel__content">
                            <div class="payment-panel__title">
                                <div>
                                    <h2>Elige cómo pagar</h2>
                                    <p>Estas son las opciones activas configuradas por <?= htmlspecialchars((string)$school['nombre'], ENT_QUOTES, 'UTF-8') ?>.</p>
                                </div>
                            </div>

                            <div class="payment-methods" role="radiogroup" aria-label="Métodos de pago">
                                <?php foreach ($methods as $index => $method): ?>
                                    <?php
                                    $methodId = (string)($method['id_metodo'] ?? '');
                                    $methodName = (string)($method['nombre_entidad'] ?? 'Método de pago');
                                    $methodType = (string)($method['tipo'] ?? 'offline');
                                    $methodQr = (string)($method['qr_path'] ?? '');
                                    $isSelected = $selectedMethod === $methodId || ($selectedMethod === '' && $index === 0);
                                    ?>
                                    <label class="payment-method <?= $isSelected ? 'is-selected' : '' ?>">
                                        <input
                                            type="radio"
                                            name="id_metodo_pago"
                                            value="<?= htmlspecialchars($methodId, ENT_QUOTES, 'UTF-8') ?>"
                                            data-name="<?= htmlspecialchars($methodName, ENT_QUOTES, 'UTF-8') ?>"
                                            data-type="<?= htmlspecialchars($methodType, ENT_QUOTES, 'UTF-8') ?>"
                                            data-qr="<?= htmlspecialchars($methodQr, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $isSelected ? 'checked' : '' ?>
                                            required
                                        >
                                        <span class="payment-method__mark" aria-hidden="true"></span>
                                        <span class="payment-method__body">
                                            <strong><?= htmlspecialchars($methodName, ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small><?= htmlspecialchars(ucfirst($methodType), ENT_QUOTES, 'UTF-8') ?></small>
                                        </span>
                                        <span class="payment-method__arrow" aria-hidden="true">›</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div id="methodQrPreview" class="payment-qr d-none">
                                <div>
                                    <span class="payment-qr__label">Código de pago</span>
                                    <strong id="methodQrName">Escanea el QR del método seleccionado</strong>
                                    <p>Realiza la transferencia por el total exacto indicado en el resumen.</p>
                                </div>
                                <img src="" alt="Código QR del método seleccionado">
                            </div>
                        </div>
                    </article>

                    <article class="payment-panel">
                        <div class="payment-panel__step">2</div>
                        <div class="payment-panel__content">
                            <div class="payment-panel__title">
                                <div>
                                    <h2>Adjunta el comprobante</h2>
                                    <p>El archivo es obligatorio y quedará asociado a la factura.</p>
                                </div>
                            </div>

                            <label class="payment-upload" for="comprobante" id="receiptDropzone">
                                <span class="payment-upload__icon" aria-hidden="true">↑</span>
                                <span class="payment-upload__copy">
                                    <strong id="receiptFileName">Seleccionar comprobante</strong>
                                    <small>JPG, PNG, WEBP o PDF · máximo 5 MB</small>
                                </span>
                                <span class="btn btn-outline-primary rounded-pill px-3">Buscar archivo</span>
                            </label>
                            <input type="file" class="visually-hidden" id="comprobante" name="comprobante" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <div class="form-text mt-2">Verifica que el valor y la referencia sean legibles antes de enviarlo.</div>
                        </div>
                    </article>
                </div>

                <aside class="payment-summary" aria-live="polite">
                    <div class="payment-summary__header">
                        <span>Resumen del pago</span>
                        <span class="payment-summary__status">Pendiente</span>
                    </div>
                    <div class="payment-summary__event">
                        <span class="payment-summary__event-icon" aria-hidden="true">★</span>
                        <div>
                            <strong><?= htmlspecialchars((string)$event['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars((string)$event['fecha'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)$event['tipo_evento'], ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                    </div>
                    <dl class="payment-summary__rows">
                        <div><dt>Responsable</dt><dd><?= htmlspecialchars($userName !== '' ? $userName : 'Usuario', ENT_QUOTES, 'UTF-8') ?></dd></div>
                        <div><dt>Inscripciones pendientes</dt><dd><?= $quantity ?></dd></div>
                        <?php if ($paidQuantity > 0): ?>
                            <div><dt>Ya facturadas</dt><dd><?= $paidQuantity ?></dd></div>
                        <?php endif; ?>
                        <div><dt>Valor por deportista</dt><dd>$<?= number_format($unitCost, 0, ',', '.') ?></dd></div>
                        <div><dt>Método</dt><dd id="summaryMethod">Por seleccionar</dd></div>
                    </dl>
                    <div class="payment-summary__total">
                        <span>Total a pagar</span>
                        <strong>$<?= number_format($total, 0, ',', '.') ?></strong>
                        <small>Valor calculado por el sistema</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill" id="submitPayment">
                        Registrar pago y factura
                    </button>
                    <p class="payment-summary__note">Al continuar confirmas que el comprobante corresponde a este pago.</p>
                </aside>
            </form>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('eventPaymentForm');
    if (!form) return;

    const methods = Array.from(form.querySelectorAll('input[name="id_metodo_pago"]'));
    const summaryMethod = document.getElementById('summaryMethod');
    const preview = document.getElementById('methodQrPreview');
    const previewImage = preview ? preview.querySelector('img') : null;
    const previewName = document.getElementById('methodQrName');
    const receipt = document.getElementById('comprobante');
    const receiptName = document.getElementById('receiptFileName');
    const submit = document.getElementById('submitPayment');

    function refreshMethod() {
        const selected = methods.find(function (method) { return method.checked; });
        form.querySelectorAll('.payment-method').forEach(function (card) {
            const input = card.querySelector('input[type="radio"]');
            card.classList.toggle('is-selected', Boolean(input && input.checked));
        });
        if (!selected) return;

        const name = selected.dataset.name || 'Método seleccionado';
        const qr = selected.dataset.qr || '';
        if (summaryMethod) summaryMethod.textContent = name;
        if (previewName) previewName.textContent = name;
        if (preview && previewImage && qr) {
            previewImage.src = qr;
            preview.classList.remove('d-none');
        } else if (preview && previewImage) {
            previewImage.removeAttribute('src');
            preview.classList.add('d-none');
        }
    }

    methods.forEach(function (method) { method.addEventListener('change', refreshMethod); });
    refreshMethod();

    if (receipt) {
        receipt.addEventListener('change', function () {
            const file = receipt.files && receipt.files[0];
            if (receiptName) receiptName.textContent = file ? file.name : 'Seleccionar comprobante';
        });
    }

    form.addEventListener('submit', function () {
        if (!form.checkValidity()) return;
        if (submit) {
            submit.disabled = true;
            submit.textContent = 'Registrando pago…';
        }
    });
});
</script>
