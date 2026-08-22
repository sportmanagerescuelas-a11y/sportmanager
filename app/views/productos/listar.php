<?php
$viewData = get_defined_vars();
$productos = is_array($viewData['productos'] ?? null) ? $viewData['productos'] : [];

if (!function_exists('normalize_image_src')) {
function normalize_image_src(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $value = str_replace('\\', '/', $value);

    if (preg_match('~proyecto_crud/(.+)$~i', $value, $m)) {
        $value = $m[1];
    }

    if (preg_match('~^(https?:)?//~i', $value) || str_starts_with($value, 'data:')) {
        return $value;
    }

    if (str_starts_with($value, '/')) {
        return str_replace(' ', '%20', $value);
    }

    if (str_contains($value, '/')) {
        return str_replace(' ', '%20', $value);
    }

    return 'assets/img/' . rawurlencode($value);
}
}
?>
<div class="container my-5 pt-5 products-page">
    <?php if (!empty($_SESSION['flash_product_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars((string)$_SESSION['flash_product_error'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php unset($_SESSION['flash_product_error']); ?>
    <?php endif; ?>
        <div class="mb-3">
            <a href="dashboard" class="btn btn-secondary">Volver al panel</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <h5 class="mb-0">Listado General</h5>
                <a href="productos&product_action=nuevo" class="btn btn-primary btn-sm fw-bold">
                    + Agregar Nuevo
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Descripcion</th>
                                <th>Imagen</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No hay productos registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars((string)($p['id_producto'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars((string)($p['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>$<?= number_format((float)($p['precio'] ?? 0), 2) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars((string)($p['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php $imgSrc = normalize_image_src((string)($p['imagen'] ?? '')); ?>
                                        <?php if ($imgSrc !== ''): ?>
                                            <img
                                                src="<?= htmlspecialchars($imgSrc) ?>"
                                                alt="<?= htmlspecialchars((string)($p['nombre'] ?? 'Producto'), ENT_QUOTES, 'UTF-8') ?>"
                                                class="img-thumbnail"
                                                style="width: 56px; height: 56px; object-fit: cover;"
                                            >
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="productos&product_action=editar&id=<?= urlencode((string)($p['id_producto'] ?? '')) ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                                            <form method="POST" action="productos&product_action=eliminar" class="d-inline" onsubmit="return confirm('Seguro que quieres deshabilitarlo?')">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)($p['id_producto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                <?php sm_csrf_input(); ?>
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Deshabilitar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>
