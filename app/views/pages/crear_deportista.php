<?php
$viewData = get_defined_vars();
$categorias = is_array($viewData['categorias'] ?? null) ? $viewData['categorias'] : [];
$niveles = is_array($viewData['niveles'] ?? null) ? $viewData['niveles'] : [];
$error = $viewData['error'] ?? null;
$errorDetails = is_array($viewData['errorDetails'] ?? null) ? $viewData['errorDetails'] : [];
?>

<div class="container py-5 mt-4 school-style-page athlete-create-page">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-end gap-3 mb-4">
        <div>
            <span class="payment-eyebrow">Registro de deportista</span>
            <h1 class="h2 fw-bold mb-2">Crear deportista</h1>
            <p class="text-muted mb-0"></p>
        </div>
        <a href="deportistas" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></div>
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

    <form method="POST" action="crear_deportista" enctype="multipart/form-data">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <div class="d-flex justify-content-center">
                    <?php sm_render_gold_card([
                        'rating' => '99',
                        'position' => 'DC',
                        'name' => 'Tu nombre',
                        'category' => 'Categoria',
                        'jornada' => 'Jornada',
                        'gender' => 'Genero',
                        'imageSrc' => 'fotos/default.png',
                        'imageAlt' => 'Vista previa del deportista',
                        'imageClass' => 'gold-card__image',
                    ]); ?>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-lg-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo Documento</label>
                                <select name="tipo_documento" class="form-control" required>
                                    <option value="CC">Cedula de Ciudadania</option>
                                    <option value="TI">Tarjeta de Identidad</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Numero Documento</label>
                                <input type="text" name="id_deportista" class="form-control" maxlength="11" pattern="\d{1,11}" inputmode="numeric" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombres</label>
                                <input type="text" name="nombres" id="nombres" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Genero</label>
                                <select name="genero" id="genero" class="form-control">
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jornada</label>
                                <select name="jornada" class="form-control">
                                    <option>Manana</option>
                                    <option>Tarde</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoria</label>
                                <select id="categoria" class="form-control" disabled>
                                    <option value="">Se calcula con la fecha de nacimiento</option>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= (int)$c->id_categoria ?>"><?= htmlspecialchars((string)$c->nombre_cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted d-block mt-1">La categoria se ajusta automaticamente segun la fecha de nacimiento.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nivel</label>
                                <select name="id_nivel" id="nivel" class="form-control">
                                    <?php foreach ($niveles as $n): ?>
                                        <option value="<?= (int)$n->id_nivel ?>"><?= htmlspecialchars((string)$n->nombre) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Foto</label>
                                <input type="file" name="foto" id="foto" class="form-control">
                            </div>
                            <div class="col-12 mt-3 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary px-4">Guardar</button>
                                <a href="deportistas" class="btn btn-outline-secondary px-4">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="assets/js/gold-card-preview.js"></script>
