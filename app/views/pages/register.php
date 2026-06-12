<?php
$viewData = get_defined_vars();
$schools = is_array($viewData['schools'] ?? null) ? $viewData['schools'] : [];
$selectedSchool = (string)($_GET['id_escuela'] ?? '');
$registerControllerPath = __DIR__ . '/../../../assets/js/registercontroller.js';
$registerControllerVersion = is_file($registerControllerPath) ? (string)filemtime($registerControllerPath) : (string)time();
$registerErrorCode = isset($_GET['error']) ? (string)$_GET['error'] : '';
$registerDebug = isset($_GET['debug']) ? trim((string)$_GET['debug']) : '';
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
    if ($registerErrorCode === 'db' && $registerDebug !== '') {
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
            Un proceso más guiado, claro y visual para que el alta sea rápida tanto para acudientes como para formadores y administradores.
        </p>
        <div class="auth-badges">
            <div class="auth-badge"><span></span> Validación en tiempo real</div>
            <div class="auth-badge"><span></span> Seguridad reforzada</div>
            <div class="auth-badge"><span></span> Diseñado para móvil</div>
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
                    <div class="col-12 col-lg-4">
                        <label for="password" class="form-label">Contrasena</label>
                        <input type="password" class="form-control auth-input" id="password" name="password" placeholder="Crea una contrasena" required>
                        <div class="invalid-feedback" id="passwordFeedback">Debes ingresar una contrasena valida.</div>
                        <div id="passwordRequirementsBox" class="border rounded-3 p-3 mt-2 bg-white shadow-sm d-none">
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
                            <option value="2">Formador (requiere aprobacion)</option>
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
                            Para administrador ya no necesitas subir comprobante. Al registrarte te llevaremos a la pasarela de pago.
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="register" class="btn btn-primary w-100 auth-action">Registrarse</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
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
            const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false });
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
                const modal = new bootstrap.Modal(modalElement);
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
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('id_rol');
    const wrap = document.getElementById('comprobantePagoWrap');
    const schoolWrap = document.getElementById('schoolWrap');
    const schoolSelect = document.getElementById('id_escuela');
    const passwordInput = document.getElementById('password');
    const passwordRequirementsBox = document.getElementById('passwordRequirementsBox');

    if (!roleSelect || !wrap || !schoolWrap || !schoolSelect) return;

    const sync = function () {
        const isAdmin = roleSelect.value === '3';
        wrap.style.display = isAdmin ? '' : 'none';
        schoolWrap.style.display = isAdmin ? 'none' : '';
        schoolSelect.required = !isAdmin;
        schoolSelect.disabled = isAdmin;
        if (isAdmin) {
            schoolSelect.value = '';
            schoolSelect.classList.remove('is-invalid');
        }
    };

    roleSelect.addEventListener('change', sync);
    sync();

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



