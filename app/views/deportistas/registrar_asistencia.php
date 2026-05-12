<?php
$viewData = get_defined_vars();
$rows = is_array($viewData['rows'] ?? null) ? $viewData['rows'] : [];
$total = (int)($viewData['total'] ?? count($rows));
$page = max(1, (int)($viewData['page'] ?? 1));
$perPage = max(1, (int)($viewData['perPage'] ?? 10));
$totalPages = max(1, (int)($viewData['totalPages'] ?? 1));
$ok = (bool)($viewData['ok'] ?? false);
$error = $viewData['error'] ?? null;
$errorFecha = $viewData['errorFecha'] ?? '';
$errorCount = (int)($viewData['errorCount'] ?? 0);

$columns = [
    'id_deportista' => 'ID',
    'tipo_documento' => 'Tipo Documento',
    'foto' => 'Foto',
    'nombres' => 'Nombres',
    'apellidos' => 'Apellidos',
    'fecha_nacimiento' => 'Fecha Nacimiento',
    'jornada' => 'Jornada',
    'fecha_registro' => 'Fecha Registro',
    'categoria' => 'Categoria',
    'nivel' => 'Nivel',
    'genero' => 'Genero',
    'usuario_creador' => 'Registrado Por',
];

$estados = ['Presente', 'Ausente', 'Tarde', 'Excusado'];

$queryBase = 'index.php?url=registrar-asistencia';
?>
<?php if (!empty($ok)): ?>
    <div class="alert alert-success">Asistencia guardada correctamente.</div>
<?php endif; ?>
<?php if (!empty($error) && $error === 'duplicado'): ?>
    <div class="alert alert-danger">
        Ya existe asistencia para <?= htmlspecialchars((string)$errorFecha) ?>. Registros duplicados: <?= (int)$errorCount ?>.
    </div>
<?php endif; ?>

<form id="asistenciaForm" method="post" action="index.php?url=guardar-asistencia">
    <input type="hidden" name="payload" id="payload">
    <input type="hidden" name="fecha" id="fechaInput">

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg">
                    <h1 class="h4 mb-1">Deportistas</h1>
                    <div class="text-muted small">Total: <?= (int)$total ?></div>
                </div>
                <div class="col-12 col-lg-auto">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-sm-auto">
                            <label for="fechaAsistencia" class="form-label small mb-1">Fecha</label>
                            <input type="date" id="fechaAsistencia" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-auto">
                            <label for="perPage" class="form-label small mb-1">Registros por página</label>
                            <select id="perPage" class="form-select form-select-sm">
                                <?php foreach ([5, 10, 20, 50] as $size): ?>
                                    <option value="<?= $size ?>" <?= $perPage === $size ? 'selected' : '' ?>><?= $size ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-auto d-grid">
                            <button type="submit" class="btn btn-success" id="submitAsistencia" disabled>Enviar asistencia</button>
                        </div>
                        <div class="col-12 col-sm-auto d-grid">
                            <button type="button" class="btn btn-outline-secondary" id="clearAsistencia">Limpiar</button>
                        </div>
                        <div class="col-12 col-sm-auto d-grid">
                            <a href="index.php?url=dashboard" class="btn btn-outline-primary">Volver</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <span class="fw-semibold">Listado</span>
            <span class="text-muted small">Página <?= (int)$page ?> de <?= (int)$totalPages ?></span>
        </div>
        <div class="table-responsive app-table-wrap">
            <table class="table table-striped table-hover align-middle mb-0" id="deportistasTable" data-page="<?= (int)$page ?>" data-per-page="<?= (int)$perPage ?>">
                <thead class="table-dark">
                    <tr>
                        <?php foreach ($columns as $label): ?>
                            <th scope="col"><?= htmlspecialchars($label) ?></th>
                        <?php endforeach; ?>
                        <th scope="col">Asistencia</th>
                        <th scope="col">Comentario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="<?= count($columns) + 2 ?>" class="text-center text-muted">No hay registros.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr data-id="<?= (int)($row['id_deportista'] ?? 0) ?>">
                                <?php foreach ($columns as $col => $label): ?>
                                    <td>
                                        <?php if ($col === 'foto'): ?>
                                            <?php
                                            $foto = trim((string)($row['foto'] ?? ''));
                                            $fotoPath = $foto !== '' ? 'fotos/' . $foto : 'fotos/default.png';
                                            ?>
                                            <img src="<?= htmlspecialchars($fotoPath, ENT_QUOTES, 'UTF-8') ?>" alt="Foto deportista" class="rounded border" style="width:48px;height:48px;object-fit:cover;">
                                        <?php else: ?>
                                            <?= htmlspecialchars((string)($row[$col] ?? '')) ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-nowrap">
                                    <div class="btn-group" role="group" aria-label="Asistencia">
                                        <?php foreach ($estados as $estado): ?>
                                            <button type="button" class="btn btn-sm asistencia-btn" data-estado="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?></button>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="app-min-col">
                                    <input type="text" class="form-control form-control-sm comentario-input" placeholder="Comentario" maxlength="255">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<nav aria-label="Paginacion" class="mt-3">
    <ul class="pagination justify-content-center flex-wrap mb-0">
        <?php
            $prev = max(1, $page - 1);
            $next = min($totalPages, $page + 1);
        ?>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" data-page="<?= $prev ?>" href="<?= $queryBase ?>&page=<?= $prev ?>&per_page=<?= $perPage ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" data-page="<?= $i ?>" href="<?= $queryBase ?>&page=<?= $i ?>&per_page=<?= $perPage ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" data-page="<?= $next ?>" href="<?= $queryBase ?>&page=<?= $next ?>&per_page=<?= $perPage ?>">Siguiente</a>
        </li>
    </ul>
</nav>
