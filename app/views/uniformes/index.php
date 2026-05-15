<?php
$viewData = get_defined_vars();
$uniformes = is_array($viewData['uniformes'] ?? null) ? $viewData['uniformes'] : [];
$role = (int)($viewData['role'] ?? ($_SESSION['rol'] ?? 0));
$canCreate = (bool)($viewData['canCreate'] ?? false);
$canManage = (bool)($viewData['canManage'] ?? false);
$message = (string)($viewData['message'] ?? '');
$messageClass = str_contains(strtolower($message), 'no se') ? 'alert-danger' : 'alert-success';
?>

<section class="container py-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="mb-1">Uniformes</h2>
            <p class="text-muted mb-0">
                <?= $role === 1 ? 'Consulta los uniformes asignados a tus deportistas.' : 'Gestiona la asignacion de uniformes por deportista y categoria.' ?>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($canCreate): ?>
                <a href="index.php?url=crear_uniforme" class="btn btn-primary">Registrar uniforme</a>
            <?php endif; ?>
            <a href="index.php?url=dashboard" class="btn btn-outline-secondary">Volver al dashboard</a>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert <?= htmlspecialchars($messageClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="mb-0">Uniformes registrados</h5>
                <span class="badge text-bg-primary"><?= count($uniformes) ?> registro(s)</span>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if (count($uniformes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Numero</th>
                                <th>Deportista</th>
                                <th>Categoria</th>
                                <th>Tipo</th>
                                <th>Nombre camiseta</th>
                                <th>Descripcion</th>
                                <?php if ($canManage): ?>
                                    <th class="text-center">Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uniformes as $uniforme): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars((string)($uniforme['id_uniforme'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge text-bg-dark"><?= htmlspecialchars((string)($uniforme['numero_camiseta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td>
                                        <?= htmlspecialchars(trim((string)($uniforme['nombres'] ?? '') . ' ' . (string)($uniforme['apellidos'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td><?= htmlspecialchars((string)($uniforme['nombre_cat'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(ucfirst((string)($uniforme['tipo_uniforme'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($uniforme['nombre_camiseta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string)($uniforme['descripcion_uniforme'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <?php if ($canManage): ?>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="index.php?url=editar_uniforme&id=<?= urlencode((string)($uniforme['id_uniforme'] ?? '')) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                                <form method="POST" action="index.php?url=eliminar_uniforme" onsubmit="return confirm('Seguro que deseas eliminar este uniforme?');">
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string)($uniforme['id_uniforme'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Borrar</button>
                                                </form>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <h6 class="mb-2">No hay uniformes registrados.</h6>
                    <p class="text-muted mb-0">
                        <?= $canCreate ? 'Puedes registrar el primer uniforme desde el boton superior.' : 'Cuando se asignen uniformes a tus deportistas apareceran aqui.' ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

