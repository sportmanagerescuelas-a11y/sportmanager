<br>
<br>
<?php
$viewData = get_defined_vars();
$schools = is_array($viewData['schools'] ?? null) ? $viewData['schools'] : [];
$selectedSchool = (string)($_GET['id_escuela'] ?? '');
$registerControllerPath = __DIR__ . '/../../../assets/js/registercontroller.js';
$registerControllerVersion = is_file($registerControllerPath) ? (string)filemtime($registerControllerPath) : (string)time();
$registerErrorCode = isset($_GET['error']) ? (string)$_GET['error'] : '';
$registerErrorMap = [
    '404' => 'La página solicitada no existe o fue movida.',
    'empty' => 'Debes completar todos los campos del formulario.',
    'invalidemail' => 'El correo electrónico no tiene un formato válido.',
    'phone' => 'El telefono debe tener exactamente 10 digitos.',
    'password' => 'La contrasena no cumple los requisitos minimos.',
    'duplicateid' => 'Ya existe un usuario con ese numero de documento.',
    'duplicateemail' => 'Ya existe un usuario con ese correo electronico.',
    'schoolnone' => 'Aun no hay escuelas disponibles para inscripcion.',
    'school' => 'La escuela seleccionada no existe. Elige una escuela valida.',
    'comprobante' => 'Para registrarte como administrador debes adjuntar un comprobante de pago.',
    'comprobante_upload' => 'No se pudo guardar el comprobante. Intentalo de nuevo.',
    'db' => 'No se pudo crear la cuenta en este momento. Inténtalo nuevamente.',
];
$registerErrorText = sm_error_text($registerErrorCode, $registerErrorMap);
$fieldErrorMap = [
    'id_usuario' => [
        'empty' => 'Debes ingresar tu numero de documento.',
        'duplicateid' => 'Ya existe un usuario con ese numero de documento.',
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
    'comprobante_pago' => [
        'comprobante' => 'Debes adjuntar el comprobante de pago para administrador.',
        'comprobante_upload' => 'No se pudo guardar el comprobante. Intentalo de nuevo.',
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
        : ($registerSuccessCode === 'payment_pending'
            ? 'Registro exitoso. Tu pago sera revisado por el superadmin antes de aprobar tu cuenta.'
            : 'Registro exitoso. Tu cuenta esta pendiente de aprobacion.');
}

$modalTitle = '';
$modalMessage = '';
$modalType = '';

if ($registerErrorText !== '' && $activeFieldError['field'] === '') {
    $modalTitle = 'Fuera de juego';
    $modalMessage = $registerErrorText;
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
?>
<style>
.password-popover-wrap {
    position: relative;
}

.register-card,
.register-card .card-body {
    overflow: visible;
}

.password-popover {
    position: absolute;
    top: 0;
    left: calc(100% + 12px);
    width: 290px;
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    padding: 12px;
    z-index: 20;
    opacity: 0;
    visibility: hidden;
    transform: translateY(4px);
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
}

.password-popover-wrap:hover .password-popover,
.password-popover-wrap:focus-within .password-popover {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

@media (max-width: 991.98px) {
    .password-popover {
        position: static;
        width: 100%;
        margin-top: 10px;
        opacity: 1;
        visibility: visible;
        transform: none;
    }
}
</style>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm register-card">
                <div class="card-header">
                    <h2 class="text-center">Crear cuenta</h2>
                </div>
                <div class="card-body">
                    <form action="controllers/registerController.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="id_usuario" class="form-label">Numero de Documento</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="id_usuario" name="id_usuario" placeholder="Tu numero de documento" maxlength="11" pattern="\d{1,11}" inputmode="numeric" required>
                                <span id="idSpinner" class="spinner-border spinner-border-sm text-primary position-absolute top-50 end-0 translate-middle-y me-3" style="display:none;" role="status" aria-hidden="true"></span>
                            </div>
                            <div class="invalid-feedback" id="id_usuarioFeedback">Por favor ingrese su numero de documento.</div>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                            <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                <option value="" selected disabled>Selecciona un tipo...</option>
                                <option value="CC">CC - Cedula de Ciudadania</option>
                                <option value="TI">TI - Tarjeta de Identidad</option>
                                <option value="CE">CE - Cedula de Extranjeria</option>
                                <option value="PAS">PAS - Pasaporte</option>
                                <option value="PEP">PEP - Permiso Especial de Permanencia</option>
                            </select>
                            <div class="invalid-feedback" id="tipo_documentoFeedback">Seleccione un tipo de documento.</div>
                        </div>
                        <div class="mb-3">
                            <label for="nombres" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Tu nombre" required>
                            <div class="invalid-feedback" id="nombresFeedback">Debes ingresar tus nombres.</div>
                        </div>
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Tus apellidos" required>
                            <div class="invalid-feedback" id="apellidosFeedback">Debes ingresar tus apellidos.</div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electronico</label>
                            <div class="position-relative">
                                <input type="email" class="form-control" id="email" name="email" placeholder="tu@correo.com" required>
                                <span id="emailSpinner" class="spinner-border spinner-border-sm text-primary position-absolute top-50 end-0 translate-middle-y me-3" style="display:none;" role="status" aria-hidden="true"></span>
                            </div>
                            <div id="emailFeedback" class="invalid-feedback">Este correo ya esta registrado.</div>
                            <div id="emailHelp" class="form-text"></div>
                        </div>
                        <div class="mb-3 password-popover-wrap">
                            <label for="password" class="form-label">Contrasena</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Crea una contrasena" required>
                            <div class="invalid-feedback" id="passwordFeedback">Debes ingresar una contrasena valida.</div>
                            <div class="password-popover" role="note" aria-live="polite">
                                <small id="passwordHelp" class="form-text text-muted d-block mb-2">
                                    La contrasena debe cumplir estos requisitos:
                                </small>
                                <ul id="passwordRequirements" class="mb-0 ps-3 small">
                                    <li id="req-length" class="text-danger">✖ Minimo 8 caracteres</li>
                                    <li id="req-upper" class="text-danger">✖ Una letra mayuscula</li>
                                    <li id="req-lower" class="text-danger">✖ Una letra minuscula</li>
                                    <li id="req-number" class="text-danger">✖ Un numero</li>
                                    <li id="req-special" class="text-danger">✖ Un caracter especial (@$!%*?&._-)</li>
                                </ul>
                                <div id="passwordMissing" class="mt-2 small text-danger"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Telefono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Tu telefono" maxlength="10" pattern="\d{10}" inputmode="numeric" required>
                            <div class="invalid-feedback" id="telefonoFeedback">Debes ingresar un telefono valido.</div>
                        </div>
                        <div class="mb-3">
                            <label for="id_rol" class="form-label">Rol</label>
                            <select class="form-select" id="id_rol" name="id_rol" required>
                                <option value="1">Acudiente</option>
                                <option value="2">Formador (requiere aprobacion)</option>
                                <option value="3">Administrador de escuela (requiere validacion de pago)</option>
                            </select>
                        </div>
                        <div class="mb-3" id="schoolWrap">
                            <label for="id_escuela" class="form-label">Escuela</label>
                            <select class="form-select" id="id_escuela" name="id_escuela" required>
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
                        <div class="mb-3" id="comprobantePagoWrap" style="display:none;">
                            <label for="comprobante_pago" class="form-label">Comprobante de pago (solo administrador)</label>
                            <input type="file" class="form-control" id="comprobante_pago" name="comprobante_pago" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="invalid-feedback" id="comprobante_pagoFeedback">Debes adjuntar el comprobante.</div>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100">Registrarse</button>
                    </form>
                </div>
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
                    <img src="assets/img/controlar.gif" alt="Registro exitoso" class="img-fluid mb-3" style="max-height: 180px;">
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
            const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false });
            modal.show();
        }
    });
    </script>
<?php elseif ($modalMessage !== ''): ?>
    <?php sm_render_modal_message('registerMessageModal', $modalTitle, $modalMessage, $modalType); ?>
<?php endif; ?>
<script src="assets/js/registercontroller.js?v=<?= urlencode($registerControllerVersion) ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('id_rol');
    const wrap = document.getElementById('comprobantePagoWrap');
    const input = document.getElementById('comprobante_pago');
    const schoolWrap = document.getElementById('schoolWrap');
    const schoolSelect = document.getElementById('id_escuela');

    if (!roleSelect || !wrap || !input || !schoolWrap || !schoolSelect) return;

    const sync = function () {
        const isAdmin = roleSelect.value === '3';
        wrap.style.display = isAdmin ? '' : 'none';
        input.required = isAdmin;
        schoolWrap.style.display = '';
        schoolSelect.required = !isAdmin;
        schoolSelect.disabled = isAdmin;
        if (isAdmin) {
            schoolSelect.value = '';
        }
    };

    roleSelect.addEventListener('change', sync);
    sync();

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


