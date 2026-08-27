<?php
$viewData = get_defined_vars();
$data = is_object($viewData['athlete'] ?? null) ? $viewData['athlete'] : (object)[
    'foto' => 'default.png',
    'nombres' => '',
    'apellidos' => '',
    'tipo_documento' => 'CC',
    'id_deportista' => '',
    'fecha_nacimiento' => '',
    'genero' => 'Masculino',
    'jornada' => 'Manana',
    'id_categoria' => 0,
    'id_nivel' => 0,
];
$categorias = is_array($viewData['categorias'] ?? null) ? $viewData['categorias'] : [];
$niveles = is_array($viewData['niveles'] ?? null) ? $viewData['niveles'] : [];
$error = $viewData['error'] ?? null;
$errorDetails = is_array($viewData['errorDetails'] ?? null) ? $viewData['errorDetails'] : [];
$fotoActual = !empty($data->foto) ? 'fotos/' . ltrim((string)$data->foto, '/\\') : 'fotos/default.png';
$categoriaActual = sm_birth_date_to_category_label((string)$data->fecha_nacimiento);
if ($categoriaActual === '') {
    $categoriaActual = 'Categoria';
}
$categoriaActualId = 0;
foreach ($categorias as $categoriaItem) {
    if (strcasecmp((string)$categoriaItem->nombre_cat, $categoriaActual) === 0) {
        $categoriaActualId = (int)$categoriaItem->id_categoria;
        break;
    }
}
$nivelActual = 'Nivel';
foreach ($niveles as $nivelItem) {
    if ((int)$nivelItem->id_nivel === (int)$data->id_nivel) {
        $nivelActual = (string)$nivelItem->nombre;
        break;
    }
}
?>

<div class="container py-5 mt-4 school-style-page athlete-create-page">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-end gap-3 mb-4">
        <div>
            <span class="payment-eyebrow">Edicion de deportista</span>
            <h1 class="h2 fw-bold mb-2">Editar deportista</h1>
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

    <form method="POST" action="editar_deportista&id=<?= urlencode((string)$data->id_deportista) ?>" enctype="multipart/form-data">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <div class="d-flex justify-content-center">
                    <?php sm_render_gold_card([
                        'rating' => '99',
                        'position' => 'DC',
                        'name' => trim((string)$data->nombres . ' ' . (string)$data->apellidos) ?: 'Tu nombre',
                        'category' => $categoriaActual,
                        'jornada' => (string)$data->jornada,
                        'gender' => (string)$data->genero,
                        'imageSrc' => $fotoActual,
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
                                    <option value="CC" <?= $data->tipo_documento === 'CC' ? 'selected' : '' ?>>Cedula de Ciudadania</option>
                                    <option value="TI" <?= $data->tipo_documento === 'TI' ? 'selected' : '' ?>>Tarjeta de Identidad</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Numero Documento</label>
                                <input type="text" name="num_documento" class="form-control" value="<?= htmlspecialchars((string)$data->id_deportista, ENT_QUOTES, 'UTF-8') ?>" maxlength="11" pattern="\d{1,11}" inputmode="numeric" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombres</label>
                                <input type="text" name="nombres" id="nombres" class="form-control" value="<?= htmlspecialchars((string)$data->nombres, ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control" value="<?= htmlspecialchars((string)$data->apellidos, ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" value="<?= htmlspecialchars((string)$data->fecha_nacimiento, ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Genero</label>
                                <select name="genero" id="genero" class="form-control" required>
                                    <option value="Masculino" <?= $data->genero === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                    <option value="Femenino" <?= $data->genero === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jornada</label>
                                <select name="jornada" class="form-control" required>
                                    <option value="Manana" <?= $data->jornada === 'Manana' ? 'selected' : '' ?>>Manana</option>
                                    <option value="Tarde" <?= $data->jornada === 'Tarde' ? 'selected' : '' ?>>Tarde</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoria</label>
                                <select id="categoria" class="form-control" disabled>
                                    <option value="">Se calcula con la fecha de nacimiento</option>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= (int)$c->id_categoria ?>" <?= $categoriaActualId === (int)$c->id_categoria ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string)$c->nombre_cat, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted d-block mt-1">La categoria se ajusta automaticamente segun la fecha de nacimiento.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nivel</label>
                                <select name="id_nivel" id="nivel" class="form-control" required>
                                    <?php foreach ($niveles as $n): ?>
                                        <option value="<?= (int)$n->id_nivel ?>" <?= (int)$data->id_nivel === (int)$n->id_nivel ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string)$n->nombre, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Foto</label>
                                <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                            </div>
                            <div class="col-12 mt-3 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary px-4">Actualizar</button>
                                <a href="deportistas" class="btn btn-outline-secondary px-4">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="assets/js/gold-card-preview.js"></script>
