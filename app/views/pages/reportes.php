<?php
$viewData = get_defined_vars();
$tablas = is_array($viewData['tablas'] ?? null) ? $viewData['tablas'] : [];
?>
<br>
<br>
<div class="container mt-5">
    <h2>Reportes</h2>
    <p>Selecciona una tabla y el formato de descarga.</p>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'forbidden_table'): ?>
        <?php sm_render_alert('No tienes permiso para descargar esa tabla.', 'Acceso restringido', 'warning', true); ?>
    <?php endif; ?>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr><th>Tabla</th><th>Excel</th><th>CSV</th><th>PDF</th></tr>
        </thead>
        <tbody>
            <?php foreach ($tablas as $tabla): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$tabla) ?></td>
                    <td><a class="btn btn-success btn-sm" href="index.php?url=descargar&tabla=<?= urlencode((string)$tabla) ?>&formato=xlsx">XLSX</a></td>
                    <td><a class="btn btn-secondary btn-sm" href="index.php?url=descargar&tabla=<?= urlencode((string)$tabla) ?>&formato=csv">CSV</a></td>
                    <td><a class="btn btn-danger btn-sm" href="index.php?url=descargar&tabla=<?= urlencode((string)$tabla) ?>&formato=pdf">PDF</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-primary mt-3">Volver</a>
</div>
