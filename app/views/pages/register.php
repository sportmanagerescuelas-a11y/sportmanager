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
    '404' => 'La pÃ¡gina solicitada no existe o fue movida.',
    'empty' => 'Debes completar todos los campos del formulario.',
    'invalidemail' => 'El correo electrÃ³nico no tiene un formato vÃ¡lido.',
    'phone' => 'El telefono debe tener entre 7 y 11 digitos.',
    'password' => 'La contrasena no cumple los requisitos minimos.',
    'duplicateid' => 'Ya existe un usuario con ese numero de documento.',
    'duplicateemail' => 'Ya existe un usuario con ese correo electronico.',
    'schoolnone' => 'Aun no hay escuelas disponibles para inscripcion.',
    'school' => 'La escuela seleccionada no existe. Elige una escuela valida.',
    'db' => 'No se pudo crear la cuenta en este momento. IntÃ©ntalo nuevamente.',
];
$registerErrorText = sm_error_text($registerErrorCode, $registerErrorMap);
?>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="text-center">Crear cuenta</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($schools)): ?>
                        <?php sm_render_alert('Aun no hay escuelas registradas. Un superadmin debe crear una escuela antes de que los usuarios puedan registrarse.', 'Escuela requerida', 'warning', true); ?>
                    <?php endif; ?>

                    <form action="controllers/registerController.php" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="id_usuario" class="form-label">Numero de Documento</label>
                            <input type="number" class="form-control" id="id_usuario" name="id_usuario" placeholder="Tu numero de documento" required>
                            <div class="invalid-feedback">Por favor ingrese su numero de documento.</div>
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
                            <div class="invalid-feedback">Seleccione un tipo de documento.</div>
                        </div>
                        <div class="mb-3">
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
                            <div class="invalid-feedback">Debes seleccionar una escuela.</div>
                        </div>
                        <div class="mb-3">
                            <label for="nombres" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Tu nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Tus apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electronico</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="tu@correo.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contrasena</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Crea una contrasena" required>
                            <small id="passwordHelp" class="form-text text-muted">
                                La contrasena debe cumplir todos estos requisitos:
                            </small>
                            <ul id="passwordRequirements" class="mt-2 mb-0 ps-3 small">
                                <li id="req-length" class="text-danger">âœ– Minimo 8 caracteres</li>
                                <li id="req-upper" class="text-danger">âœ– Una letra mayuscula</li>
                                <li id="req-lower" class="text-danger">âœ– Una letra minuscula</li>
                                <li id="req-number" class="text-danger">âœ– Un numero</li>
                                <li id="req-special" class="text-danger">âœ– Un caracter especial (@$!%*?&._-)</li>
                            </ul>
                            <div id="passwordMissing" class="mt-2 small text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Telefono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Tu telefono" required>
                        </div>
                        <div class="mb-3">
                            <label for="id_rol" class="form-label">Rol</label>
                            <select class="form-select" id="id_rol" name="id_rol" required>
                                <option value="1">Acudiente</option>
                                <option value="2">Formador (requiere aprobacion)</option>
                                <option value="3">Administrador (requiere aprobacion)</option>
                            </select>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100" <?= empty($schools) ? 'disabled' : '' ?>>Registrarse</button>
                    </form>

                    <?php if ($registerErrorText !== ''): ?>
                        <?php sm_render_alert($registerErrorText, 'Fuera de juego', 'danger', true); ?>
                    <?php endif; ?>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="mt-3 alert alert-success text-center">
                            <?= $_GET['success'] === '1' ? 'Registro exitoso.' : 'Registro exitoso. Tu cuenta esta pendiente de aprobacion.' ?>
                            <div class="mt-2"><a href="index.php?url=login" class="btn btn-success btn-sm">Aceptar</a></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/registercontroller.js?v=<?= urlencode($registerControllerVersion) ?>"></script>


