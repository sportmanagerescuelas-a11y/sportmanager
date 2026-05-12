<?php
$viewData = get_defined_vars();
$evento = is_object($viewData['evento'] ?? null) ? $viewData['evento'] : null;
$inscritos = is_array($viewData['inscritos'] ?? null) ? $viewData['inscritos'] : [];
?>
<br>
<br>
<div class="container mt-5">
    <?php if (!$evento): ?>
        <div class="alert alert-danger">Evento no encontrado</div>
    <?php else: ?>
        <h2><?= htmlspecialchars((string)$evento->titulo) ?></h2>
        <p><b>Fecha:</b> <?= htmlspecialchars((string)$evento->fecha) ?></p>
        <p><b>Tipo:</b> <?= htmlspecialchars((string)$evento->tipo_evento) ?></p>
        <p><b>Costo:</b> $<?= htmlspecialchars((string)$evento->costo) ?></p>
        <hr>
        <h4>Deportistas inscritos</h4>
        <?php if (count($inscritos) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Deportista</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $n = 1;
                    foreach ($inscritos as $i): ?>
                        <tr>
                            <td><?= $n++ ?></td>
                            <td><?= htmlspecialchars($i->nombre_dep . ' ' . $i->apellido_dep) ?></td>
                            <td><?= htmlspecialchars($i->nombre_user . ' ' . $i->apellido_user) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">No hay inscritos en este evento</div>
        <?php endif; ?>
    <?php endif; ?>
    <a href="gestion_eventos.php" class="btn btn-secondary mt-3">Volver</a>
</div>