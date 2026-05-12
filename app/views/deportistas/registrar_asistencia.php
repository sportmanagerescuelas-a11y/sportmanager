<?php
$viewData = get_defined_vars();
$rows = is_array($viewData['rows'] ?? null) ? $viewData['rows'] : [];
$total = (int)($viewData['total'] ?? count($rows));
$page = max(1, (int)($viewData['page'] ?? 1));
$perPage = max(1, (int)($viewData['perPage'] ?? 10));
$totalPages = max(1, (int)($viewData['totalPages'] ?? 1));
$filters = is_array($viewData['filters'] ?? null) ? $viewData['filters'] : [];
$categorias = is_array($viewData['categorias'] ?? null) ? $viewData['categorias'] : [];
$jornadas = is_array($viewData['jornadas'] ?? null) ? $viewData['jornadas'] : [];
$ok = (bool)($viewData['ok'] ?? false);
$error = $viewData['error'] ?? null;
$errorFecha = $viewData['errorFecha'] ?? '';
$errorCount = (int)($viewData['errorCount'] ?? 0);

$search = (string)($filters['search'] ?? '');
$categoriaActual = (string)($filters['categoria'] ?? '');
$jornadaActual = (string)($filters['jornada'] ?? '');
$estados = ['Presente', 'Ausente', 'Tarde', 'Excusado'];

$queryParams = [
    'url' => 'registrar-asistencia',
    'per_page' => $perPage,
];
if ($search !== '') $queryParams['search'] = $search;
if ($categoriaActual !== '') $queryParams['categoria'] = $categoriaActual;
if ($jornadaActual !== '') $queryParams['jornada'] = $jornadaActual;
$queryBase = 'index.php?' . http_build_query($queryParams);
?>
<?php if (!empty($ok)): ?>
    <div class="alert alert-success">Asistencia guardada correctamente.</div>
<?php endif; ?>
<?php if (!empty($error) && $error === 'duplicado'): ?>
    <div class="alert alert-danger">
        Ya existe asistencia para <?= htmlspecialchars((string)$errorFecha) ?>. Registros duplicados: <?= (int)$errorCount ?>.
    </div>
<?php endif; ?>

<section class="attendance-shell">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="attendance-filter-title">Buscar y filtrar</div>
            <div class="attendance-filters">
                <form method="get" action="index.php" class="attendance-filter-form">
                    <input type="hidden" name="url" value="registrar-asistencia">
                    <input type="hidden" name="per_page" value="<?= (int)$perPage ?>">
                    <div>
                        <label for="search" class="form-label small mb-1">Nombre o documento</label>
                        <input type="search" id="search" name="search" class="form-control form-control-sm" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Buscar deportista">
                    </div>
                    <div>
                        <label for="categoria" class="form-label small mb-1">Categoria</label>
                        <select id="categoria" name="categoria" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                                <?php $catId = (string)($cat['id_categoria'] ?? ''); ?>
                                <option value="<?= htmlspecialchars($catId, ENT_QUOTES, 'UTF-8') ?>" <?= $categoriaActual === $catId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)($cat['nombre_cat'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="jornada" class="form-label small mb-1">Jornada</label>
                        <select id="jornada" name="jornada" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            <?php foreach ($jornadas as $jornada): ?>
                                <option value="<?= htmlspecialchars((string)$jornada, ENT_QUOTES, 'UTF-8') ?>" <?= $jornadaActual === (string)$jornada ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)$jornada, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="attendance-filter-buttons">
                        <button type="submit" class="btn btn-primary btn-sm">Aplicar</button>
                        <a href="index.php?url=registrar-asistencia" class="btn btn-outline-secondary btn-sm">Quitar filtros</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="asistenciaForm" method="post" action="index.php?url=guardar-asistencia">
        <input type="hidden" name="payload" id="payload">
        <input type="hidden" name="fecha" id="fechaInput">

        <div class="attendance-toolbar card shadow-sm">
            <div class="card-body">
                <div class="attendance-title-row">
                    <div>
                        <h1 class="h4 mb-1">Registrar asistencia</h1>
                        <div class="text-muted small"><?= (int)$total ?> deportistas encontrados</div>
                    </div>
                    <div class="attendance-actions">
                        <button type="submit" class="btn btn-success" id="submitAsistencia" disabled>Guardar asistencia</button>
                        <button type="button" class="btn btn-outline-secondary" id="clearAsistencia">Limpiar</button>
                        <a href="index.php?url=dashboard" class="btn btn-outline-primary">Volver</a>
                    </div>
                </div>

                <div class="attendance-controls">
                    <div>
                        <label for="fechaAsistencia" class="form-label small mb-1">Fecha de asistencia</label>
                        <input type="date" id="fechaAsistencia" class="form-control form-control-sm">
                    </div>
                    <div>
                        <label for="perPage" class="form-label small mb-1">Registros por pagina</label>
                        <select id="perPage" class="form-select form-select-sm">
                            <?php foreach ([5, 10, 20, 50] as $size): ?>
                                <option value="<?= $size ?>" <?= $perPage === $size ? 'selected' : '' ?>><?= $size ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="attendance-list" id="deportistasTable" data-page="<?= (int)$page ?>" data-per-page="<?= (int)$perPage ?>">
            <?php if (empty($rows)): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">No hay deportistas con los filtros seleccionados.</div>
                </div>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <?php
                    $id = (int)($row['id_deportista'] ?? 0);
                    $foto = trim((string)($row['foto'] ?? ''));
                    $fotoPath = $foto !== '' ? 'fotos/' . $foto : 'fotos/default.png';
                    $nombreCompleto = trim((string)($row['nombres'] ?? '') . ' ' . (string)($row['apellidos'] ?? ''));
                    ?>
                    <article class="attendance-card" data-id="<?= $id ?>">
                        <div class="attendance-person">
                            <img src="<?= htmlspecialchars($fotoPath, ENT_QUOTES, 'UTF-8') ?>" alt="Foto deportista">
                            <div class="attendance-person-main">
                                <h2><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></h2>
                                <div class="attendance-meta">
                                    <span>Doc. <?= htmlspecialchars((string)($row['id_deportista'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span><?= htmlspecialchars((string)($row['categoria'] ?? 'Sin categoria'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span>Nivel <?= htmlspecialchars((string)($row['nivel'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span><?= htmlspecialchars((string)($row['jornada'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="text-muted small">Acudiente: <?= htmlspecialchars((string)($row['usuario_creador'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        </div>
                        <div class="attendance-mark">
                            <div class="attendance-state-grid" role="group" aria-label="Asistencia de <?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>">
                                <?php foreach ($estados as $estado): ?>
                                    <button type="button" class="btn btn-sm asistencia-btn" data-estado="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?></button>
                                <?php endforeach; ?>
                            </div>
                            <input type="text" class="form-control form-control-sm comentario-input" placeholder="Comentario opcional" maxlength="255">
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </form>
</section>

<nav aria-label="Paginacion" class="mt-3">
    <ul class="pagination justify-content-center flex-wrap mb-0">
        <?php
            $prev = max(1, $page - 1);
            $next = min($totalPages, $page + 1);
        ?>
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" data-page="<?= $prev ?>" href="<?= htmlspecialchars($queryBase . '&page=' . $prev, ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" data-page="<?= $i ?>" href="<?= htmlspecialchars($queryBase . '&page=' . $i, ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" data-page="<?= $next ?>" href="<?= htmlspecialchars($queryBase . '&page=' . $next, ENT_QUOTES, 'UTF-8') ?>">Siguiente</a>
        </li>
    </ul>
</nav>
