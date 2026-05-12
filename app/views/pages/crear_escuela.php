<br>
<br>
<?php
$viewData = get_defined_vars();
$formData = is_array($viewData['formData'] ?? null) ? $viewData['formData'] : [];
$errorDetails = is_array($viewData['errorDetails'] ?? null) ? $viewData['errorDetails'] : [];
$isEdit = !empty($viewData['isEdit']);
$schoolId = (string)($viewData['schoolId'] ?? '');
$formAction = $isEdit ? ('index.php?url=editar_escuela&id=' . urlencode($schoolId)) : 'index.php?url=crear_escuela';
$title = $isEdit ? 'Editar Escuela' : 'Crear Escuela';
$buttonText = $isEdit ? 'Actualizar escuela' : 'Crear escuela';
$formData = array_merge([
    'nombre' => '',
    'disciplina' => '',
    'dia_pago' => '',
    'valor_inscripcion' => '',
    'valor_mensualidad' => '',
    'correo' => '',
    'pass_app' => '',
    'telefono' => '',
    'direccion' => '',
    'escudo_path' => '',
    'firma_path' => '',
], $formData);
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

                    <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="POST" class="needs-validation" novalidate>
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
                                <label class="form-label" for="pass_app">Clave de app/correo</label>
                                <input type="text" class="form-control" id="pass_app" name="pass_app" maxlength="60" value="<?= htmlspecialchars((string)$formData['pass_app'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="telefono">Telefono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" maxlength="11" value="<?= htmlspecialchars((string)$formData['telefono'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="direccion">Direccion</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" maxlength="50" value="<?= htmlspecialchars((string)$formData['direccion'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="escudo_path">Ruta escudo (opcional)</label>
                                <input type="text" class="form-control" id="escudo_path" name="escudo_path" maxlength="255" value="<?= htmlspecialchars((string)$formData['escudo_path'], ENT_QUOTES, 'UTF-8') ?>" placeholder="assets/img/escudo.png">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="firma_path">Ruta firma (opcional)</label>
                                <input type="text" class="form-control" id="firma_path" name="firma_path" maxlength="255" value="<?= htmlspecialchars((string)$formData['firma_path'], ENT_QUOTES, 'UTF-8') ?>" placeholder="assets/img/firma.png">
                            </div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?= htmlspecialchars($buttonText, ENT_QUOTES, 'UTF-8') ?></button>
                            <a href="index.php?url=gestion_escuelas" class="btn btn-secondary">Volver</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
