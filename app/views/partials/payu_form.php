<?php
$viewData = get_defined_vars();
$payuContextData = is_array($viewData['payuContext'] ?? null) ? $viewData['payuContext'] : [];
$payuContext = array_merge([
    'evento_titulo' => 'Pago',
    'monto' => 0,
    'cantidad' => 1,
    'action' => 'index.php?url=procesar_pago',
    'return_to' => 'pagos.php',
    'error' => '',
    'prefill' => [],
], $payuContextData);

$eventoTitulo = (string)($payuContext['evento_titulo'] ?? 'Pago');
$idEvento = (int)($payuContext['id_evento'] ?? 0);
$monto = (float)($payuContext['monto'] ?? 0);
$cantidad = (int)($payuContext['cantidad'] ?? 1);
$action = (string)($payuContext['action'] ?? 'index.php?url=procesar_pago');
$returnTo = (string)($payuContext['return_to'] ?? 'pagos.php');
$error = (string)($payuContext['error'] ?? '');
$prefill = is_array($payuContext['prefill'] ?? null) ? $payuContext['prefill'] : [];
?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="POST" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" id="payuForm" class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <h5 class="card-title mb-3 text-primary fw-bold">Formulario de pago</h5>

        <div class="alert alert-primary border-0">
            <strong>Concepto:</strong> <?= htmlspecialchars($eventoTitulo, ENT_QUOTES, 'UTF-8') ?><br>
            <strong>Total a pagar:</strong> $<?= number_format($monto, 0, ',', '.') ?>
        </div>

        <input type="hidden" name="cantidad" value="<?= max(1, $cantidad) ?>">
        <input type="hidden" name="id_evento" value="<?= $idEvento > 0 ? $idEvento : '' ?>">
        <input type="hidden" name="monto" value="<?= max(0, $monto) ?>">
        <input type="hidden" name="concepto" value="<?= htmlspecialchars($eventoTitulo, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8') ?>">

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Direccion</label>
                <input type="text" class="form-control" name="direccion" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Ciudad</label>
                <input type="text" class="form-control" name="ciudad" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Departamento</label>
                <input type="text" class="form-control" name="departamento" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Codigo postal</label>
                <input type="text" class="form-control" name="codigo_postal" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Pais (ISO2)</label>
                <input type="text" class="form-control" name="pais" value="CO" maxlength="2" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Documento</label>
                <input type="text" class="form-control" name="dni" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Tipo documento</label>
                <select class="form-select" name="tipo_documento" required>
                    <option value="CC">CC</option>
                    <option value="CE">CE</option>
                    <option value="TI">TI</option>
                    <option value="NIT">NIT</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tipo persona</label>
                <select class="form-select" name="tipo_persona" required>
                    <option value="N">Natural</option>
                    <option value="J">Juridica</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Metodo de pago</label>
                <select class="form-select" name="metodo_pago" id="metodoPago" required>
                    <option value="PSE">PSE</option>
                    <option value="VISA">VISA</option>
                    <option value="MASTERCARD">MASTERCARD</option>
                </select>
            </div>
        </div>

        <div class="row g-3 mt-1" id="bloquePse">
            <div class="col-md-12">
                <label class="form-label fw-semibold">Banco PSE</label>
                <select class="form-select" name="pseBank" id="pseBank">
                    <option value="">Cargando bancos...</option>
                </select>
            </div>
        </div>

        <div class="row g-3 mt-1 d-none" id="bloqueTarjeta">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Numero de tarjeta</label>
                <input type="text" class="form-control" name="numero_tarjeta">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nombre en tarjeta</label>
                <input type="text" class="form-control" name="cardName" value="<?= htmlspecialchars((string)($prefill['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">CVV</label>
                <input type="text" class="form-control" name="cardCVV">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Mes expiracion</label>
                <input type="text" class="form-control" name="expiracion_mes" placeholder="MM">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Ano expiracion</label>
                <input type="text" class="form-control" name="expiracion_ano" placeholder="YYYY">
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">Pagar ahora</button>
            <a href="dashboard.php" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>
</form>

<script>
(function () {
    const metodoPago = document.getElementById('metodoPago');
    const bloquePse = document.getElementById('bloquePse');
    const bloqueTarjeta = document.getElementById('bloqueTarjeta');
    const pseBank = document.getElementById('pseBank');

    function refreshMetodo() {
        const metodo = metodoPago.value;
        const esPse = metodo === 'PSE';
        bloquePse.classList.toggle('d-none', !esPse);
        bloqueTarjeta.classList.toggle('d-none', esPse);
    }

    function cargarBancos() {
        fetch('index.php?url=obtener_bancos')
            .then(r => r.json())
            .then(data => {
                pseBank.innerHTML = '';
                if (!data || data.status !== 'success' || !Array.isArray(data.banks) || data.banks.length === 0) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No hay bancos disponibles';
                    pseBank.appendChild(opt);
                    return;
                }
                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = 'Seleccione un banco';
                pseBank.appendChild(defaultOpt);
                data.banks.forEach(bank => {
                    const opt = document.createElement('option');
                    opt.value = bank.pseCode ?? '';
                    opt.textContent = bank.description ?? 'Banco';
                    pseBank.appendChild(opt);
                });
            })
            .catch(() => {
                pseBank.innerHTML = '<option value="">Error cargando bancos</option>';
            });
    }

    metodoPago.addEventListener('change', refreshMetodo);
    refreshMetodo();
    cargarBancos();
})();
</script>
