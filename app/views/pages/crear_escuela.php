<br>
<br>
<?php
$viewData = get_defined_vars();
$formData = is_array($viewData['formData'] ?? null) ? $viewData['formData'] : [];
$errorDetails = is_array($viewData['errorDetails'] ?? null) ? $viewData['errorDetails'] : [];
$isEdit = !empty($viewData['isEdit']);
$schoolId = (string)($viewData['schoolId'] ?? '');
$formAction = $isEdit ? ('editar_escuela&id=' . urlencode($schoolId)) : 'crear_escuela';
$title = $isEdit ? 'Editar Escuela' : 'Crear Escuela';
$buttonText = $isEdit ? 'Actualizar escuela' : 'Crear escuela';
$formData = array_merge([
    'nombre' => '',
    'disciplina' => '',
    'dia_pago' => '',
    'valor_inscripcion' => '',
    'valor_mensualidad' => '',
    'correo' => '',
    'telefono' => '',
    'direccion' => '',
    'escudo_path' => '',
    'firma_path' => '',
    'color_primario' => '#0d6efd',
    'color_secundario' => '#198754',
    'metodos_pago' => [
        [
            'id_metodo' => '',
            'nombre_entidad' => '',
            'tipo' => 'offline',
            'qr_path' => '',
        ],
    ],
], $formData);
$metodosPago = is_array($formData['metodos_pago'] ?? null) ? $formData['metodos_pago'] : [];
if ($metodosPago === []) {
    $metodosPago = [
        [
            'id_metodo' => '',
            'nombre_entidad' => '',
            'tipo' => 'offline',
            'qr_path' => '',
        ],
    ];
}
?>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="text-center mb-0"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <?php sm_render_alert((string)$error, 'No se pudo guardar', 'danger', true); ?>
                    <?php endif; ?>
                    <?php if (!empty($errorDetails)): ?>
                        <div class="alert alert-warning">
                            <strong>Detalle de errores:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errorDetails as $detail): ?>
                                    <li><?= htmlspecialchars((string)$detail, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="nombre">Nombre de escuela</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" maxlength="30" value="<?= htmlspecialchars((string)$formData['nombre'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="disciplina">Disciplina</label>
                                <input type="text" class="form-control" id="disciplina" name="disciplina" maxlength="20" value="<?= htmlspecialchars((string)$formData['disciplina'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="dia_pago">Dia de pago</label>
                                <input type="number" class="form-control" id="dia_pago" name="dia_pago" min="1" max="31" value="<?= htmlspecialchars((string)$formData['dia_pago'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="valor_inscripcion">Valor inscripcion</label>
                                <input type="number" class="form-control" id="valor_inscripcion" name="valor_inscripcion" min="0" step="0.01" value="<?= htmlspecialchars((string)$formData['valor_inscripcion'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="valor_mensualidad">Valor mensualidad</label>
                                <input type="number" class="form-control" id="valor_mensualidad" name="valor_mensualidad" min="0" step="0.01" value="<?= htmlspecialchars((string)$formData['valor_mensualidad'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="correo">Correo oficial</label>
                                <input type="email" class="form-control" id="correo" name="correo" maxlength="50" value="<?= htmlspecialchars((string)$formData['correo'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="telefono">Telefono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" maxlength="10" pattern="\d{10}" inputmode="numeric" value="<?= htmlspecialchars((string)$formData['telefono'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="direccion">Direccion</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" maxlength="50" value="<?= htmlspecialchars((string)$formData['direccion'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="color_primario">Color primario</label>
                                <input type="color" class="form-control form-control-color w-100" id="color_primario" name="color_primario" value="<?= htmlspecialchars((string)$formData['color_primario'], ENT_QUOTES, 'UTF-8') ?>" title="Selecciona color primario">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="color_secundario">Color secundario</label>
                                <input type="color" class="form-control form-control-color w-100" id="color_secundario" name="color_secundario" value="<?= htmlspecialchars((string)$formData['color_secundario'], ENT_QUOTES, 'UTF-8') ?>" title="Selecciona color secundario">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="escudo_file">Escudo de la escuela (archivo)</label>
                                <input type="file" class="form-control" id="escudo_file" name="escudo_file" accept=".jpg,.jpeg,.png,.webp,.gif">
                                <input type="hidden" name="current_escudo_path" value="<?= htmlspecialchars((string)$formData['escudo_path'], ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ((string)$formData['escudo_path'] !== ''): ?>
                                    <div class="mt-2">
                                        <img src="<?= htmlspecialchars((string)$formData['escudo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Escudo actual" style="max-height: 80px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="firma_path">Ruta firma (opcional)</label>
                                <input type="text" class="form-control" id="firma_path" name="firma_path" maxlength="255" value="<?= htmlspecialchars((string)$formData['firma_path'], ENT_QUOTES, 'UTF-8') ?>" placeholder="assets/img/firma.png">
                            </div>
                        </div>
                        <div class="mt-4 border-top pt-4">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                                <div>
                                    <h5 class="mb-1">Métodos de pago</h5>
                                    <p class="text-muted small mb-0">Solo las opciones de esta lista aparecerán al pagar dentro del sistema.</p>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addPaymentMethod">+ Agregar método</button>
                            </div>
                            <div id="paymentMethods" class="d-grid gap-3">
                                <?php foreach ($metodosPago as $index => $method): ?>
                                    <?php
                                    $methodId = (string)($method['id_metodo'] ?? '');
                                    $methodName = (string)($method['nombre_entidad'] ?? '');
                                    $methodType = (string)($method['tipo'] ?? 'offline');
                                    $methodQr = (string)($method['qr_path'] ?? '');
                                    ?>
                                    <div class="payment-method-row border rounded-3 p-3 bg-light-subtle">
                                        <input type="hidden" name="metodos_pago[id_metodo][]" value="<?= htmlspecialchars($methodId, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="metodos_pago[qr_path][]" value="<?= htmlspecialchars($methodQr, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-5">
                                                <label class="form-label">Entidad o metodo</label>
                                                <input type="text" class="form-control" name="metodos_pago[nombre_entidad][]" maxlength="50" value="<?= htmlspecialchars($methodName, ENT_QUOTES, 'UTF-8') ?>" <?= $index === 0 ? 'required' : '' ?>>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Tipo</label>
                                                <select class="form-select" name="metodos_pago[tipo][]">
                                                    <?php
                                                    $types = [
                                                        'offline' => 'General',
                                                        'transferencia' => 'Transferencia',
                                                        'bancolombia' => 'Bancolombia',
                                                        'nequi' => 'Nequi',
                                                        'daviplata' => 'Daviplata',
                                                        'efectivo' => 'Efectivo',
                                                    ];
                                                    ?>
                                                    <?php foreach ($types as $value => $label): ?>
                                                        <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $methodType === $value ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">QR o imagen</label>
                                                <input type="file" class="form-control" name="metodo_pago_qr[]" accept=".jpg,.jpeg,.png,.webp,.gif">
                                            </div>
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-payment-method">Quitar método</button>
                                            </div>
                                            <?php if ($methodQr !== ''): ?>
                                                <div class="col-12">
                                                    <img src="<?= htmlspecialchars($methodQr, ENT_QUOTES, 'UTF-8') ?>" alt="QR metodo" style="max-height: 90px;">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?= htmlspecialchars($buttonText, ENT_QUOTES, 'UTF-8') ?></button>
                            <a href="gestion_escuelas" class="btn btn-secondary">Volver</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('paymentMethods');
    const addButton = document.getElementById('addPaymentMethod');
    if (!container || !addButton) {
        return;
    }

    const syncRequiredField = function () {
        container.querySelectorAll('input[name="metodos_pago[nombre_entidad][]"]').forEach(function (field, index) {
            if (index === 0) {
                field.setAttribute('required', 'required');
            } else {
                field.removeAttribute('required');
            }
        });
    };

    container.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-payment-method');
        if (!button) {
            return;
        }
        const row = button.closest('.payment-method-row');
        if (!row) {
            return;
        }
        const rows = container.querySelectorAll('.payment-method-row');
        if (rows.length === 1) {
            row.querySelectorAll('input').forEach(function (field) { field.value = ''; });
            row.querySelectorAll('select').forEach(function (field) { field.value = 'offline'; });
            row.querySelectorAll('img').forEach(function (image) { image.remove(); });
        } else {
            row.remove();
        }
        syncRequiredField();
    });

    addButton.addEventListener('click', function () {
        const firstRow = container.querySelector('.payment-method-row');
        if (!firstRow) {
            return;
        }

        const clone = firstRow.cloneNode(true);
        clone.querySelectorAll('input, select').forEach(function (field) {
            if (field.type === 'hidden' || field.type === 'text') {
                field.value = '';
            }
            if (field.type === 'file') {
                field.value = '';
            }
            if (field.tagName === 'SELECT') {
                field.value = 'offline';
            }
            field.removeAttribute('required');
        });
        clone.querySelectorAll('img').forEach(function (image) {
            image.remove();
        });
        container.appendChild(clone);
        syncRequiredField();
    });

    syncRequiredField();
});
</script>
