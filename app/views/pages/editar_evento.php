<?php
$viewData = get_defined_vars();
$evento = is_object($viewData['evento'] ?? null) ? $viewData['evento'] : (object)[
    'titulo' => '',
    'fecha' => '',
    'tipo_evento' => '',
    'costo' => '',
    'cuotas' => '',
    'id_rol' => 1,
];
?>
<br>
<br>
<div class="container mt-5">
    <h2>Editar Evento</h2>
    <form method="POST">
        <input type="text" name="titulo" value="<?= htmlspecialchars((string)$evento->titulo) ?>" class="form-control mb-2">
        <input type="date" name="fecha" value="<?= htmlspecialchars((string)$evento->fecha) ?>" class="form-control mb-2">
        <input type="text" name="tipo_evento" value="<?= htmlspecialchars((string)$evento->tipo_evento) ?>" class="form-control mb-2">
        <input type="number" name="costo" value="<?= htmlspecialchars((string)$evento->costo) ?>" class="form-control mb-2">
        <input type="number" name="cuotas" value="<?= htmlspecialchars((string)$evento->cuotas) ?>" class="form-control mb-2">
        <select name="id_rol" class="form-control mb-2">
            <option value="1" <?= (int)$evento->id_rol === 1 ? 'selected' : '' ?>>Usuarios</option>
            <option value="2" <?= (int)$evento->id_rol === 2 ? 'selected' : '' ?>>Entrenadores</option>
            <option value="3" <?= (int)$evento->id_rol === 3 ? 'selected' : '' ?>>Administradores</option>
        </select>
        <button class="btn btn-success">Actualizar</button>
        <a href="gestion_eventos.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
