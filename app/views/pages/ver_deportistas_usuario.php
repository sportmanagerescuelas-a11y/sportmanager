<?php
$viewData = get_defined_vars();
$deportistas = is_array($viewData['deportistas'] ?? null) ? $viewData['deportistas'] : [];
?>
<br>
<br>
<div class="container mt-5">
    <h2>Deportistas del Usuario</h2>
    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr><th>ID</th><th>Foto</th><th>Nombres</th><th>Apellidos</th><th>Categoria</th><th>Nivel</th></tr>
        </thead>
        <tbody>
            <?php foreach ($deportistas as $d): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$d['id_deportista']) ?></td>
                    <td><img src="fotos/<?= htmlspecialchars((string)$d['foto']) ?>" width="50" alt=""></td>
                    <td><?= htmlspecialchars((string)$d['nombres']) ?></td>
                    <td><?= htmlspecialchars((string)$d['apellidos']) ?></td>
                    <td><?= htmlspecialchars((string)$d['nombre_cat']) ?></td>
                    <td><?= htmlspecialchars((string)$d['nombre']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="admin_usuarios.php" class="btn btn-secondary">Volver</a>
</div>
