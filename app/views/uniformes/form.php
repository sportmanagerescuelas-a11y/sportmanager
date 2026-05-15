<?php
$viewData = get_defined_vars();
$athletes = is_array($viewData['athletes'] ?? null) ? $viewData['athletes'] : [];
$types = is_array($viewData['types'] ?? null) ? $viewData['types'] : [];
$formData = is_array($viewData['formData'] ?? null) ? $viewData['formData'] : [];
$error = (string)($viewData['error'] ?? '');
$isEdit = (bool)($viewData['isEdit'] ?? false);
$uniformId = (int)($viewData['uniformId'] ?? ($formData['id_uniforme'] ?? 0));
$action = $isEdit ? 'index.php?url=editar_uniforme&id=' . urlencode((string)$uniformId) : 'index.php?url=crear_uniforme';
$selectedAthlete = (string)($formData['id_deportista'] ?? '');
$selectedType = (string)($formData['tipo_uniforme'] ?? 'competencia');
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9 col-xl-8">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h2 class="mb-1"><?= $isEdit ? 'Editar uniforme' : 'Registrar uniforme' ?></h2>
                    <p class="text-muted mb-0">Asocia un uniforme a un deportista y conserva numeros unicos por categoria.</p>
                </div>
                <a href="index.php?url=uniformes" class="btn btn-outline-secondary">Volver</a>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <?php if (!$isEdit && count($athletes) === 0): ?>
                        <div class="text-center py-4">
                            <h6 class="mb-2">No hay deportistas disponibles para asignar.</h6>
                            <p class="text-muted mb-0">Todos los deportistas registrados ya tienen uniforme.</p>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="<?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?>" class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Deportista</label>
                                <select name="id_deportista" class="form-select" required>
                                    <option value="">Selecciona un deportista</option>
                                    <?php foreach ($athletes as $athlete): ?>
                                        <?php
                                        $athleteId = (string)($athlete['id_deportista'] ?? '');
                                        $athleteName = trim((string)($athlete['nombres'] ?? '') . ' ' . (string)($athlete['apellidos'] ?? ''));
                                        $category = (string)($athlete['nombre_cat'] ?? 'Sin categoria');
                                        $owner = trim((string)($athlete['acudiente_nombres'] ?? '') . ' ' . (string)($athlete['acudiente_apellidos'] ?? ''));
                                        $label = $athleteName . ' - ' . $category . ($owner !== '' ? ' - Acudiente: ' . $owner : '');
                                        ?>
                                        <option value="<?= htmlspecialchars($athleteId, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedAthlete === $athleteId ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Numero de camiseta</label>
                                <input type="number" name="numero_camiseta" class="form-control" min="1" max="999" value="<?= htmlspecialchars((string)($formData['numero_camiseta'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Tipo de uniforme</label>
                                <select name="tipo_uniforme" class="form-select" required>
                                    <?php foreach ($types as $value => $label): ?>
                                        <option value="<?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedType === (string)$value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Nombre en camiseta</label>
                                <input type="text" name="nombre_camiseta" class="form-control" maxlength="10" value="<?= htmlspecialchars((string)($formData['nombre_camiseta'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripcion</label>
                                <textarea name="descripcion_uniforme" class="form-control" rows="4"><?= htmlspecialchars((string)($formData['descripcion_uniforme'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                                <a href="index.php?url=uniformes" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Guardar cambios' : 'Registrar uniforme' ?></button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

