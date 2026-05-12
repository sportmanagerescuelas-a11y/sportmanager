<?php
$viewData = get_defined_vars();
$error = $viewData['error'] ?? null;
?>
<br>
<br>
<div class="container mt-5">
    <h2>Crear Evento</h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <label>Titulo</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-6">
                <label>Tipo Evento</label>
                <input type="text" name="tipo_evento" class="form-control" value="mensualidad">
            </div>
            <div class="col-md-6">
                <label>Costo</label>
                <input type="number" name="costo" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Cuotas</label>
                <input type="number" name="cuotas" class="form-control">
            </div>
            <div class="col-md-6">
                <label>Dirigido a (Rol)</label>
                <select name="id_rol" class="form-control">
                    <option value="1">Usuarios</option>
                    <option value="2">Entrenadores</option>
                    <option value="3">Administradores</option>
                </select>
            </div>
            <div class="col-12 mt-3">
                <button class="btn btn-success">Guardar</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>
    </form>
</div>
