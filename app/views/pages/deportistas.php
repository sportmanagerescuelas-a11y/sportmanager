<?php
$viewData = get_defined_vars();
$rows = is_array($viewData['rows'] ?? null) ? $viewData['rows'] : [];
$rol = (int)($viewData['rol'] ?? ($_SESSION['rol'] ?? 0));
?>
<br>
<br>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Lista de Deportistas</h2>
        <?php if ($rol !== 2): ?>
            <a href="index.php?url=crear_deportista" class="btn btn-success">Nuevo deportista</a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th><th>Foto</th><th>Nombres</th><th>Apellidos</th><th>Categoria</th>
                    <th>Nivel</th><th>Estado</th><th>Creado por</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$row->id_deportista) ?></td>
                        <td><img src="fotos/<?= htmlspecialchars((string)$row->foto) ?>" width="50" alt=""></td>
                        <td><?= htmlspecialchars((string)$row->nombres) ?></td>
                        <td><?= htmlspecialchars((string)$row->apellidos) ?></td>
                        <td><?= htmlspecialchars((string)$row->nombre_cat) ?></td>
                        <td><?= htmlspecialchars((string)$row->nombre) ?></td>
                        <td>
                            <?php if ($rol === 3): ?>
                                <form action="controllers/cambiarEstadoDeportista.php" method="POST">
                                    <input type="hidden" name="id_deportista" value="<?= htmlspecialchars((string)$row->id_deportista) ?>">
                                    <select name="id_estado" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="1" <?= (int)$row->id_estado === 1 ? 'selected' : '' ?>>Activo</option>
                                        <option value="2" <?= (int)$row->id_estado === 2 ? 'selected' : '' ?>>Suspendido</option>
                                        <option value="3" <?= (int)$row->id_estado === 3 ? 'selected' : '' ?>>Retirado</option>
                                    </select>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-<?= $row->nombre_estado === 'activo' ? 'success' : ($row->nombre_estado === 'suspendido' ? 'warning' : 'danger') ?>">
                                    <?= htmlspecialchars(ucfirst((string)$row->nombre_estado)) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars((string)$row->nombre_usuario) ?></td>
                        <td>
                            <a href="editar_deportista.php?id=<?= urlencode((string)$row->id_deportista) ?>" class="btn btn-warning btn-sm">Editar</a>
                            <?php if ($rol === 3): ?>
                                <form action="controllers/cambiarEstadoDeportista.php" method="POST" class="d-inline-block">
                                    <input type="hidden" name="id_deportista" value="<?= htmlspecialchars((string)$row->id_deportista) ?>">
                                    <input type="hidden" name="id_estado" value="2">
                                    <button type="submit" class="btn btn-danger btn-sm">Deshabilitar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="index.php?url=dashboard" class="btn btn-primary mt-3">Volver</a>
</div>
