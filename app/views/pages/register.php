<?php
$viewData = get_defined_vars();
$schools = is_array($viewData['schools'] ?? null) ? $viewData['schools'] : [];
$schoolPaymentData = is_array($viewData['schoolPaymentData'] ?? null) ? $viewData['schoolPaymentData'] : [];
$selectedSchool = (string)($_GET['id_escuela'] ?? '');
$registerControllerPath = __DIR__ . '/../../../assets/js/registercontroller.js';
$registerControllerVersion = is_file($registerControllerPath) ? (string)filemtime($registerControllerPath) : (string)time();
$registerErrorCode = isset($_GET['error']) ? (string)$_GET['error'] : '';
$registerDebug = isset($_GET['debug']) ? trim((string)$_GET['debug']) : '';
$registerErrorMap = [
    '404' => 'La pÃ¡gina solicitada no existe o fue movida.',
    'empty' => 'Debes completar todos los campos del formulario.',
    'invalidemail' => 'El correo electrÃ³nico no tiene un formato vÃ¡lido.',
    'phone' => 'El telefono debe tener exactamente 10 digitos.',
    'password' => 'La contrasena no cumple los requisitos minimos.',
    'duplicateid' => 'Ya existe un usuario con ese numero de documento.',
    'idrange' => 'El numero de documento es demasiado grande. El maximo permitido es 2,147,483,647.',
    'duplicateemail' => 'Ya existe un usuario con ese correo electronico.',
    'schoolnone' => 'Aun no hay escuelas disponibles para inscripcion.',
    'school' => 'La escuela seleccionada no existe. Elige una escuela valida.',
    'paymentmethod' => 'Selecciona un metodo de pago valido para la escuela.',
    'paymentrequired' => 'Debes seleccionar un metodo de pago y adjuntar el comprobante.',
    'receipt' => 'Debes adjuntar un comprobante de pago valido.',
    'receiptsize' => 'El comprobante no puede superar 5 MB.',
    'receipttype' => 'El comprobante debe ser imagen JPG, PNG, WEBP o PDF.',
    'db' => 'No se pudo crear la cuenta en este momento. IntÃ©ntalo nuevamente.',
    'adminregister' => 'No se pudo registrar el usuario administrador. Revisa la estructura de la base de datos y vuelve a intentarlo.',
    'adminschema' => 'No se pudo ajustar la estructura necesaria para el registro del administrador.',
    'adminsession' => 'No se pudo preparar la sesion temporal del administrador.',
];
$registerErrorText = sm_error_text($registerErrorCode, $registerErrorMap);
$fieldErrorMap = [
    'id_usuario' => [
        'empty' => 'Debes ingresar tu numero de documento.',
        'duplicateid' => 'Ya existe un usuario con ese numero de documento.',
        'idrange' => 'El numero de documento es demasiado grande. El maximo permitido es 2,147,483,647.',
    ],
    'tipo_documento' => [
        'empty' => 'Debes seleccionar un tipo de documento.',
    ],
    'id_escuela' => [
        'empty' => 'Debes seleccionar una escuela.',
        'school' => 'La escuela seleccionada no existe. Elige una escuela valida.',
    ],
    'nombres' => [
        'empty' => 'Debes ingresar tus nombres.',
    ],
    'apellidos' => [
        'empty' => 'Debes ingresar tus apellidos.',
    ],
    'email' => [
        'empty' => 'Debes ingresar tu correo electronico.',
        'invalidemail' => 'El formato del correo no es valido.',
        'duplicateemail' => 'Este correo ya esta registrado.',
    ],
    'password' => [
        'empty' => 'Debes ingresar una contrasena.',
        'password' => 'La contrasena no cumple los requisitos minimos.',
    ],
    'telefono' => [
        'empty' => 'Debes ingresar tu telefono.',
        'phone' => 'El telefono debe tener exactamente 10 digitos.',
    ],
];

$activeFieldError = ['field' => '', 'message' => ''];
foreach ($fieldErrorMap as $fieldId => $codes) {
    if (isset($codes[$registerErrorCode])) {
        $activeFieldError['field'] = $fieldId;
        $activeFieldError['message'] = $codes[$registerErrorCode];
        break;
    }
}

$registerSuccessCode = isset($_GET['success']) ? (string)$_GET['success'] : '';
$registerSuccessText = '';
if ($registerSuccessCode !== '') {
    $registerSuccessText = $registerSuccessCode === '1'
        ? 'Registro exitoso.'
        : ($registerSuccessCode === 'registered'
            ? 'Registro exitoso. Tu cuenta ya quedo activa y puedes ingresar con tus credenciales.'
            : ($registerSuccessCode === 'pending_approval'
                ? 'Registro exitoso. Tu cuenta quedo pendiente de aprobacion por el administrador de la escuela.'
                : ($registerSuccessCode === 'payment_pending'
            ? 'Registro exitoso. Tu pago sera revisado por el superadmin antes de aprobar tu cuenta.'
            : ($registerSuccessCode === 'payment_registered'
                ? 'Registro exitoso. Tu comprobante quedo guardado y podras verlo en tus pagos despues de iniciar sesion.'
                : 'Registro exitoso. Tu cuenta esta pendiente de aprobacion.'))));
}

$modalTitle = '';
$modalMessage = '';
$modalType = '';

if ($registerErrorText !== '' && $activeFieldError['field'] === '') {
    $modalTitle = 'Fuera de juego';
    $modalMessage = $registerErrorText;
    if (in_array($registerErrorCode, ['db', 'adminregister', 'adminschema', 'adminsession'], true) && $registerDebug !== '') {
        $modalMessage .= ' Detalle tecnico: ' . $registerDebug;
    }
    $modalType = 'danger';
} elseif ($registerSuccessText !== '') {
    $modalTitle = 'Registro completado';
    $modalMessage = $registerSuccessText;
    $modalType = 'success';
} elseif (empty($schools)) {
    $modalTitle = 'Sin escuelas';
    $modalMessage = 'Aun no hay escuelas registradas. Puedes registrarte como administrador para que, tras aprobacion, crees la primera escuela.';
    $modalType = 'warning';
}
$assetBase = '/sportmanager/';
?>
<div class="auth-page auth-page--register py-2 py-lg-3">
<div class="auth-shell auth-shell--register container">
    <section class="auth-panel reveal-up">
        <div class="auth-kicker">Nuevo registro</div>
        <h1 class="auth-title">Crea tu cuenta y entra al ecosistema deportivo.</h1>
        <p class="auth-copy mb-0">
            Un proceso mÃ¡s guiado, claro y visual para que el alta sea rÃ¡pida tanto para acudientes como para formadores y administradores.
        </p>
        <div class="auth-badges">
            <div class="auth-badge"><span></span> ValidaciÃ³n en tiempo real</div>
            <div class="auth-badge"><span></span> Seguridad reforzada</div>
            <div class="auth-badge"><span></span> DiseÃ±ado para mÃ³vil</div>
        </div>
    </section>

    <section class="auth-card auth-card--register card reveal-up delay-1">
        <div class="card-header">
            <div class="auth-kicker auth-kicker--dark mx-auto mb-2">Nuevo registro</div>
            <h2 class="text-center mb-1">Crear cuenta</h2>
            <p class="auth-subtitle text-center mb-0">Completa tus datos para comenzar.</p>
        </div>
        <div class="card-body">
            <form action="registro-submit" method="POST" enctype="multipart/form-data" class="needs-validation auth-form-grid" novalidate>
                <input type="hidden" name="register" value="1">
                <div class="row g-2 g-lg-3 auth-register-grid">
                    <div class="col-12 col-lg-4">
                        <label for="id_usuario" class="form-label">Numero de Documento</label>
                        <div class="position-relative">
                            <input type="text" class="form-control auth-input" id="id_usuario" name="id_usuario" placeholder="Tu numero de documento" maxlength="11" pattern="\d{1,11}" inputmode="numeric" required>
                            <span id="idSpinner" class="spinner-border spinner-border-sm text-primary position-absolute top-50 end-0 translate-middle-y me-3" style="display:none;" role="status" aria-hidden="true"></span>
                        </div>
                        <div class="invalid-feedback" id="id_usuarioFeedback">Por favor ingrese su numero de documento.</div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                        <select class="form-select auth-input" id="tipo_documento" name="tipo_documento" required>
                            <option value="" selected disabled>Selecciona un tipo...</option>
                            <option value="CC">CC - Cedula de Ciudadania</option>
                            <option value="TI">TI - Tarjeta de Identidad</option>
                            <option value="CE">CE - Cedula de Extranjeria</option>
                            <option value="PAS">PAS - Pasaporte</option>
                            <option value="PEP">PEP - Permiso Especial de Permanencia</option>
                        </select>
                        <div class="invalid-feedback" id="tipo_documentoFeedback">Seleccione un tipo de documento.</div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label for="nombres" class="form-label">Nombres</label>
                        <input type="text" class="form-control auth-input" id="nombres" name="nombres" placeholder="Tu nombre" required>
                        <div class="invalid-feedback" id="nombresFeedback">Debes ingresar tus nombres.</div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label for="apellidos" class="form-label">Apellidos</label>
                        <input type="text" class="form-control auth-input" id="apellidos" name="apellidos" placeholder="Tus apellidos" required>
                        <div class="invalid-feedback" id="apellidosFeedback">Debes ingresar tus apellidos.</div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label for="email" class="form-label">Correo Electronico</label>
                        <div class="position-relative">
                            <input type="email" class="form-control auth-input" id="email" name="email" placeholder="tu@correo.com" required>
                            <span id="emailSpinner" class="spinner-border spinner-border-sm text-primary position-absolute top-50 end-0 translate-middle-y me-3" style="display:none;" role="status" aria-hidden="true"></span>
                        </div>
                        <div id="emailFeedback" class="invalid-feedback">Este correo ya esta registrado.</div>
                        <div id="emailHelp" class="form-text"></div>
                    </div>
                    <div class="col-12 col-lg-4 password-field-wrap">
                        <label for="password" class="form-label">Contrasena</label>
                        <input type="password" class="form-control auth-input" id="password" name="password" placeholder="Crea una contrasena" required>
                        <div class="invalid-feedback" id="passwordFeedback">Debes ingresar una contrasena valida.</div>
                        <div id="passwordRequirementsBox" class="border rounded-3 p-3 bg-white shadow-sm d-none">
                            <small id="passwordHelp" class="form-text text-muted d-block">
                                La contrasena debe cumplir todos estos requisitos:
                            </small>
                            <ul id="passwordRequirements" class="mt-2 mb-0 ps-3 small">
                                <li id="req-length" class="text-danger">- Minimo 8 caracteres</li>
                                <li id="req-upper" class="text-danger">- Una letra mayuscula</li>
                                <li id="req-lower" class="text-danger">- Una letra minuscula</li>
                                <li id="req-number" class="text-danger">- Un numero</li>
                                <li id="req-special" class="text-danger">- Un caracter especial (@$!%*?&._-)</li>
                            </ul>
                        </div>
                        <div id="passwordMissing" class="mt-2 small text-danger"></div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label for="telefono" class="form-label">Telefono</label>
                        <input type="tel" class="form-control auth-input" id="telefono" name="telefono" placeholder="Tu telefono" maxlength="10" pattern="\d{10}" inputmode="numeric" required>
                        <div class="invalid-feedback" id="telefonoFeedback">Debes ingresar un telefono valido.</div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label for="id_rol" class="form-label">Rol</label>
                        <select class="form-select auth-input" id="id_rol" name="id_rol" required>
                            <option value="1">Acudiente</option>
                            <option value="2">Formador</option>
                            <option value="3">Administrador de escuela (requiere validacion de pago)</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-4" id="schoolWrap">
                        <label for="id_escuela" class="form-label">Escuela</label>
                        <select class="form-select auth-input" id="id_escuela" name="id_escuela">
                            <option value="" selected disabled>Selecciona tu escuela...</option>
                            <?php foreach ($schools as $school): ?>
                                <?php $value = (string)($school->id_escuela ?? ''); ?>
                                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedSchool === $value ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)(($school->nombre ?? '') . ' - ' . ($school->disciplina ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback" id="id_escuelaFeedback">Debes seleccionar una escuela.</div>
                    </div>
                    <div class="col-12" id="comprobantePagoWrap" style="display:none;">
                        <div class="alert alert-info mb-0 py-2">
                            El acudiente se enviara a la pasarela de pago despues de registrarse. El formador se crea con aprobacion del administrador.
                        </div>
                    </div>
                    <input type="hidden" name="id_metodo_pago" id="registrationPaymentMethod" value="">
                    <input type="file" class="visually-hidden" id="registrationReceiptInput" name="comprobante" accept=".jpg,.jpeg,.png,.webp,.pdf">
                    <div class="col-12">
                        <button type="submit" name="register" class="btn btn-primary w-100 auth-action">Registrarse</button>
                    </div>
                </div>
            </form>
        </div>
</section>
</div>
</div>
<div class="modal fade" id="registrationPaymentModal" tabindex="-1" aria-labelledby="registrationPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="registrationPaymentModalLabel">Completa el pago de registro</h5>
                    <p class="text-muted mb-0 small">Selecciona un metodo activo de la escuela y adjunta el comprobante.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="registrationPaymentAlert" class="alert alert-warning d-none"></div>
                <div class="payment-layout payment-layout--modal">
                    <div class="payment-main">
                        <article class="payment-panel">
                            <div class="payment-panel__step">1</div>
                            <div class="payment-panel__content">
                                <div class="payment-panel__title">
                                    <div>
                                        <h2>Elige como pagar</h2>
                                        <p id="registrationPaymentSchoolText">Metodos activos de la escuela seleccionada.</p>
                                    </div>
                                </div>
                                <div id="registrationPaymentMethods" class="payment-methods" role="radiogroup" aria-label="Metodos de pago"></div>
                                <div id="registrationMethodQrPreview" class="payment-qr d-none">
                                    <div>
                                        <span class="payment-qr__label">Codigo de pago</span>
                                        <strong id="registrationMethodQrName">Escanea el QR del metodo seleccionado</strong>
                                        <p>Realiza la transferencia por el valor indicado en el resumen.</p>
                                    </div>
                                    <img src="" alt="Codigo QR del metodo seleccionado">
                                </div>
                            </div>
                        </article>

                        <article class="payment-panel">
                            <div class="payment-panel__step">2</div>
                            <div class="payment-panel__content">
                                <div class="payment-panel__title">
                                    <div>
                                        <h2>Adjunta el comprobante</h2>
                                        <p>El archivo es obligatorio y quedara asociado a la factura del usuario.</p>
                                    </div>
                                </div>
                                <label class="payment-upload" for="registrationReceiptInput" id="registrationReceiptDropzone">
                                    <span class="payment-upload__icon" aria-hidden="true">â†‘</span>
                                    <span class="payment-upload__copy">
                                        <strong id="registrationReceiptFileName">Seleccionar comprobante</strong>
                                        <small>JPG, PNG, WEBP o PDF Â· maximo 5 MB</small>
                                    </span>
                                    <span class="btn btn-outline-primary rounded-pill px-3">Buscar archivo</span>
                                </label>
                                <div class="form-text mt-2">Verifica que el valor y la referencia sean legibles antes de continuar.</div>
                            </div>
                        </article>
                    </div>
                    <aside class="payment-summary" aria-live="polite">
                        <div class="payment-summary__header">
                            <span>Resumen del pago</span>
                            <span class="payment-summary__status">Pendiente</span>
                        </div>
                        <div class="payment-summary__event">
                            <span class="payment-summary__event-icon" aria-hidden="true">â˜…</span>
                            <div>
                                <strong id="registrationSummarySchool">Escuela seleccionada</strong>
                                <small>Pago de registro de usuario</small>
                            </div>
                        </div>
                        <dl class="payment-summary__rows">
                            <div><dt>Usuario</dt><dd id="registrationSummaryUser">Por completar</dd></div>
                            <div><dt>Rol</dt><dd id="registrationSummaryRole">Usuario</dd></div>
                            <div><dt>Metodo</dt><dd id="registrationSummaryMethod">Por seleccionar</dd></div>
                        </dl>
                        <div class="payment-summary__total">
                            <span>Total a pagar</span>
                            <strong id="registrationSummaryTotal">$0</strong>
                            <small>Valor de inscripcion configurado por la escuela</small>
                        </div>
                    </aside>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                <button type="button" class="btn btn-primary" id="confirmRegistrationPayment">Confirmar pago y registrar</button>
            </div>
        </div>
    </div>
</div>
<?php if ($registerSuccessText !== ''): ?>
    <div class="modal fade" id="registerMessageModal" tabindex="-1" aria-labelledby="registerMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-white border border-success border-3">
                <div class="modal-header border-0 justify-content-center pb-0">
                    <h5 class="modal-title text-center w-100" id="registerMessageModalLabel">Registro exitoso</h5>
                </div>
                <div class="modal-body text-center pt-2">
                    <img src="<?= htmlspecialchars($assetBase . 'assets/img/controlar.gif', ENT_QUOTES, 'UTF-8') ?>" alt="Registro exitoso" class="img-fluid mb-3" style="max-height: 180px;">
                    <p class="mb-0"><?= htmlspecialchars($registerSuccessText, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0">
                    <a href="login" class="btn btn-success px-4">Aceptar</a>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('registerMessageModal');
        if (modalElement && window.bootstrap && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement, { backdrop: 'static', keyboard: false });
            modalElement.addEventListener('hidden.bs.modal', function () {
                if (document.activeElement instanceof HTMLElement) {
                    document.activeElement.blur();
                }
            });
            modal.show();
        }
    });
    </script>
<?php elseif ($modalMessage !== ''): ?>
    <?php if ($modalType === 'danger'): ?>
        <div class="modal fade" id="registerMessageModal" tabindex="-1" aria-labelledby="registerMessageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-white border border-danger border-3">
                    <div class="modal-header border-0 justify-content-center pb-0">
                        <h5 class="modal-title text-center w-100" id="registerMessageModalLabel"><?= htmlspecialchars($modalTitle, ENT_QUOTES, 'UTF-8') ?></h5>
                    </div>
                    <div class="modal-body text-center pt-2">
                        <img src="assets/img/silbato-deportivo.gif" alt="Error al crear cuenta" class="img-fluid mb-3" style="max-height: 180px;">
                        <p class="mb-0"><?= htmlspecialchars($modalMessage, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pt-0">
                        <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('registerMessageModal');
            if (modalElement && window.bootstrap && bootstrap.Modal) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modalElement.addEventListener('hidden.bs.modal', function () {
                    if (document.activeElement instanceof HTMLElement) {
                        document.activeElement.blur();
                    }
                });
                modal.show();
            }
        });
        </script>
    <?php else: ?>
        <?php sm_render_modal_message('registerMessageModal', $modalTitle, $modalMessage, $modalType); ?>
    <?php endif; ?>
<?php endif; ?>
<script src="assets/js/registercontroller.js?v=<?= urlencode($registerControllerVersion) ?>"></script>
<script>
const registrationPaymentData = <?= json_encode($schoolPaymentData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '{}' ?>;

document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('id_rol');
    const wrap = document.getElementById('comprobantePagoWrap');
    const schoolWrap = document.getElementById('schoolWrap');
    const schoolSelect = document.getElementById('id_escuela');
    const passwordInput = document.getElementById('password');
    const passwordRequirementsBox = document.getElementById('passwordRequirementsBox');
    const form = document.querySelector('form[action="registro-submit"]');
    const submitButton = document.querySelector('button[name="register"]');
    const methodInput = document.getElementById('registrationPaymentMethod');
    const receiptInput = document.getElementById('registrationReceiptInput');
    const receiptFileName = document.getElementById('registrationReceiptFileName');
    const paymentModalElement = document.getElementById('registrationPaymentModal');
    const paymentMethodsWrap = document.getElementById('registrationPaymentMethods');
    const paymentAlert = document.getElementById('registrationPaymentAlert');
    const confirmPaymentButton = document.getElementById('confirmRegistrationPayment');
    const paymentSchoolText = document.getElementById('registrationPaymentSchoolText');
    const summarySchool = document.getElementById('registrationSummarySchool');
    const summaryUser = document.getElementById('registrationSummaryUser');
    const summaryRole = document.getElementById('registrationSummaryRole');
    const summaryMethod = document.getElementById('registrationSummaryMethod');
    const summaryTotal = document.getElementById('registrationSummaryTotal');
    const qrPreview = document.getElementById('registrationMethodQrPreview');
    const qrImage = qrPreview ? qrPreview.querySelector('img') : null;
    const qrName = document.getElementById('registrationMethodQrName');
    let paymentConfirmed = false;
    let paymentModal = null;

    if (!roleSelect || !wrap || !schoolWrap || !schoolSelect) return;

    const formatCurrency = function (value) {
        const amount = Number(value || 0);
        return '$' + new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(amount);
    };

    const showPaymentAlert = function (message, type) {
        if (!paymentAlert) return;
        paymentAlert.textContent = message;
        paymentAlert.className = 'alert alert-' + (type || 'warning');
        paymentAlert.classList.toggle('d-none', message === '');
    };

    const selectedSchoolData = function () {
        const schoolId = String(schoolSelect.value || '');
        return schoolId !== '' && registrationPaymentData ? registrationPaymentData[schoolId] : null;
    };

    const resetPaymentState = function (clearReceipt) {
        paymentConfirmed = false;
        if (methodInput) methodInput.value = '';
        if (summaryMethod) summaryMethod.textContent = 'Por seleccionar';
        if (clearReceipt && receiptInput) receiptInput.value = '';
        if (clearReceipt && receiptFileName) receiptFileName.textContent = 'Seleccionar comprobante';
    };

    const refreshQrPreview = function (selected) {
        const qr = selected ? (selected.getAttribute('data-qr') || '') : '';
        const name = selected ? (selected.getAttribute('data-name') || 'Metodo seleccionado') : 'Metodo seleccionado';
        if (summaryMethod) summaryMethod.textContent = name;
        if (qrName) qrName.textContent = name;
        if (qrPreview && qrImage && qr !== '') {
            qrImage.src = qr;
            qrPreview.classList.remove('d-none');
        } else if (qrPreview && qrImage) {
            qrImage.removeAttribute('src');
            qrPreview.classList.add('d-none');
        }
    };

    const selectPaymentMethod = function (input) {
        if (!input) return;
        if (methodInput) methodInput.value = input.value;
        paymentMethodsWrap.querySelectorAll('.payment-method').forEach(function (card) {
            const radio = card.querySelector('input[type="radio"]');
            card.classList.toggle('is-selected', Boolean(radio && radio.checked));
        });
        refreshQrPreview(input);
        paymentConfirmed = false;
    };

    const renderPaymentMethods = function (schoolData) {
        if (!paymentMethodsWrap) return;
        paymentMethodsWrap.innerHTML = '';
        const methods = Array.isArray(schoolData && schoolData.methods) ? schoolData.methods : [];
        methods.forEach(function (method, index) {
            const methodId = String(method.id_metodo || '');
            const methodName = String(method.nombre_entidad || 'Metodo de pago');
            const methodType = String(method.tipo || 'offline');
            const methodQr = String(method.qr_path || '');

            const label = document.createElement('label');
            label.className = 'payment-method' + (index === 0 ? ' is-selected' : '');

            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'registration_modal_payment_method';
            radio.value = methodId;
            radio.required = true;
            radio.checked = index === 0;
            radio.setAttribute('data-name', methodName);
            radio.setAttribute('data-type', methodType);
            radio.setAttribute('data-qr', methodQr);

            const mark = document.createElement('span');
            mark.className = 'payment-method__mark';
            mark.setAttribute('aria-hidden', 'true');

            const body = document.createElement('span');
            body.className = 'payment-method__body';
            const strong = document.createElement('strong');
            strong.textContent = methodName;
            const small = document.createElement('small');
            small.textContent = methodType.charAt(0).toUpperCase() + methodType.slice(1);
            body.append(strong, small);

            const arrow = document.createElement('span');
            arrow.className = 'payment-method__arrow';
            arrow.setAttribute('aria-hidden', 'true');
            arrow.textContent = 'â€º';

            label.append(radio, mark, body, arrow);
            paymentMethodsWrap.appendChild(label);
            radio.addEventListener('change', function () { selectPaymentMethod(radio); });
        });

        const selected = paymentMethodsWrap.querySelector('input[name="registration_modal_payment_method"]:checked');
        if (selected) {
            selectPaymentMethod(selected);
        } else {
            refreshQrPreview(null);
        }
    };

    const openPaymentModal = function () {
        const schoolData = selectedSchoolData();
        const methods = Array.isArray(schoolData && schoolData.methods) ? schoolData.methods : [];
        const schoolName = schoolData ? String(schoolData.nombre || 'Escuela seleccionada') : 'Escuela seleccionada';
        const amount = schoolData ? Number(schoolData.valor_inscripcion || 0) : 0;
        const fullName = [
            document.getElementById('nombres') ? document.getElementById('nombres').value.trim() : '',
            document.getElementById('apellidos') ? document.getElementById('apellidos').value.trim() : ''
        ].filter(Boolean).join(' ');

        if (paymentSchoolText) paymentSchoolText.textContent = 'Metodos activos configurados por ' + schoolName + '.';
        if (summarySchool) summarySchool.textContent = schoolName;
        if (summaryUser) summaryUser.textContent = fullName !== '' ? fullName : 'Usuario por registrar';
        if (summaryRole) summaryRole.textContent = roleSelect.options[roleSelect.selectedIndex] ? roleSelect.options[roleSelect.selectedIndex].textContent : 'Usuario';
        if (summaryTotal) summaryTotal.textContent = formatCurrency(amount);

        renderPaymentMethods(schoolData);

        if (!schoolData) {
            showPaymentAlert('Selecciona una escuela antes de continuar.', 'warning');
            if (confirmPaymentButton) confirmPaymentButton.disabled = true;
        } else if (methods.length === 0) {
            showPaymentAlert('La escuela seleccionada aun no tiene metodos de pago activos. Comunicate con su administrador.', 'warning');
            if (confirmPaymentButton) confirmPaymentButton.disabled = true;
        } else {
            showPaymentAlert('', 'warning');
            if (confirmPaymentButton) confirmPaymentButton.disabled = false;
        }

        if (!paymentModal && paymentModalElement && window.bootstrap && bootstrap.Modal) {
            paymentModal = bootstrap.Modal.getOrCreateInstance(paymentModalElement);
        }
        if (paymentModal) paymentModal.show();
    };

    const sync = function () {
        const needsPayment = roleSelect.value === '3';
        wrap.style.display = needsPayment ? '' : 'none';
        schoolWrap.style.display = needsPayment ? 'none' : '';
        schoolSelect.required = !needsPayment;
        schoolSelect.disabled = needsPayment;
        if (needsPayment) {
            schoolSelect.value = '';
            schoolSelect.classList.remove('is-invalid');
        }
        resetPaymentState(true);
        if (form && typeof form.checkValidity === 'function') {
            form.checkValidity();
        }
    };

    roleSelect.addEventListener('change', sync);
    schoolSelect.addEventListener('change', function () { resetPaymentState(true); });
    sync();

    if (receiptInput) {
        receiptInput.addEventListener('change', function () {
            const file = receiptInput.files && receiptInput.files[0];
            if (receiptFileName) receiptFileName.textContent = file ? file.name : 'Seleccionar comprobante';
            paymentConfirmed = false;
        });
    }

    if (confirmPaymentButton) {
        confirmPaymentButton.addEventListener('click', function () {
            const selected = paymentMethodsWrap ? paymentMethodsWrap.querySelector('input[name="registration_modal_payment_method"]:checked') : null;
            const file = receiptInput && receiptInput.files ? receiptInput.files[0] : null;
            if (!selected || !selected.value) {
                showPaymentAlert('Selecciona un metodo de pago para continuar.', 'warning');
                return;
            }
            if (!file) {
                showPaymentAlert('Adjunta el comprobante de pago antes de registrar.', 'warning');
                return;
            }
            if (file.size > 5242880) {
                showPaymentAlert('El comprobante no puede superar 5 MB.', 'warning');
                return;
            }
            if (!/\.(jpe?g|png|webp|pdf)$/i.test(file.name || '')) {
                showPaymentAlert('El comprobante debe ser JPG, PNG, WEBP o PDF.', 'warning');
                return;
            }

            if (methodInput) methodInput.value = selected.value;
            paymentConfirmed = true;
            if (paymentModal) paymentModal.hide();
            window.requestAnimationFrame(function () {
                if (form && typeof form.requestSubmit === 'function') {
                    form.requestSubmit(submitButton || undefined);
                } else if (form) {
                    form.submit();
                }
            });
        });
    }

    if (passwordInput && passwordRequirementsBox) {
        passwordInput.addEventListener('focus', function () {
            passwordRequirementsBox.classList.remove('d-none');
        });
        passwordInput.addEventListener('blur', function () {
            passwordRequirementsBox.classList.add('d-none');
        });
    }

    const serverFieldError = {
        field: '<?= htmlspecialchars($activeFieldError['field'], ENT_QUOTES, 'UTF-8') ?>',
        message: '<?= htmlspecialchars($activeFieldError['message'], ENT_QUOTES, 'UTF-8') ?>'
    };

    if (serverFieldError.field !== '') {
        const field = document.getElementById(serverFieldError.field);
        const feedback = document.getElementById(serverFieldError.field + 'Feedback');
        if (field) {
            field.classList.add('is-invalid');
            field.focus();
        }
        if (feedback && serverFieldError.message !== '') {
            feedback.textContent = serverFieldError.message;
        }
    }
});
</script>



