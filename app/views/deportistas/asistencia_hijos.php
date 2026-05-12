<?php
$viewData = get_defined_vars();
$fechas = is_array($viewData['fechas'] ?? null) ? $viewData['fechas'] : [];
$fecha = (string)($viewData['fecha'] ?? '');
$rows = is_array($viewData['rows'] ?? null) ? $viewData['rows'] : [];

function sm_attendance_badge(string $estado): string
{
    return match ($estado) {
        'Presente' => 'success',
        'Ausente' => 'danger',
        'Tarde' => 'warning',
        'Excusado' => 'secondary',
        default => 'primary',
    };
}
?>
<section class="attendance-shell">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="attendance-title-row">
                <div>
                    <h1 class="h4 mb-1">Asistencias de mis hijos</h1>
                    <div class="text-muted small">Consulta los registros por fecha.</div>
                </div>
                <a href="index.php?url=dashboard" class="btn btn-outline-primary">Volver</a>
            </div>

            <form method="get" action="index.php" class="attendance-guardian-filter">
                <input type="hidden" name="url" value="asistencia-hijos">
                <label for="fecha" class="form-label small mb-1">Fecha</label>
                <select id="fecha" name="fecha" class="form-select" onchange="this.form.submit()" <?= empty($fechas) ? 'disabled' : '' ?>>
                    <?php if (empty($fechas)): ?>
                        <option value="">Sin asistencias registradas</option>
                    <?php else: ?>
                        <?php foreach ($fechas as $item): ?>
                            <option value="<?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?>" <?= $fecha === (string)$item ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="attendance-list">
        <?php if (empty($rows)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    No hay asistencias para mostrar en esta fecha.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <?php $estado = (string)($row['estado'] ?? ''); ?>
                <article class="attendance-card attendance-card-readonly">
                    <div class="attendance-person-main">
                        <h2><?= htmlspecialchars(trim((string)$row['nombres'] . ' ' . (string)$row['apellidos']), ENT_QUOTES, 'UTF-8') ?></h2>
                        <div class="attendance-meta">
                            <span>Doc. <?= htmlspecialchars((string)$row['id_deportista'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span><?= htmlspecialchars((string)($row['categoria'] ?? 'Sin categoria'), ENT_QUOTES, 'UTF-8') ?></span>
                            <span>Nivel <?= htmlspecialchars((string)($row['nivel'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span><?= htmlspecialchars((string)($row['jornada'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <?php if (!empty($row['comentario'])): ?>
                            <p class="attendance-comment mb-0"><?= htmlspecialchars((string)$row['comentario'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="badge text-bg-<?= sm_attendance_badge($estado) ?> attendance-status-badge"><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
