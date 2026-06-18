<?php
$viewData = get_defined_vars();
$productoData = is_array($viewData['producto'] ?? null) ? $viewData['producto'] : [];
$producto = array_merge([
    'id_producto' => '',
    'nombre' => '',
    'precio' => '',
    'descripcion' => '',
    'imagen' => '',
], $productoData);
$isEdit = (bool)($viewData['isEdit'] ?? false);
$productExists = !$isEdit || $productoData !== [];

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

$imgSrc = normalize_image_src((string)$producto['imagen']);
?>
<div class="container my-5 pt-5">
        <div class="mb-3">
            <a href="productos" class="btn btn-secondary">Volver a productos</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><?= $isEdit ? 'Editar Producto' : 'Agregar Producto' ?></h5>
            </div>
            <div class="card-body">
                <?php if (!$productExists): ?>
                    <div class="alert alert-danger mb-3">Producto no encontrado.</div>
                    <a href="productos" class="btn btn-secondary">Volver</a>
                <?php else: ?>
                    <?php if (!empty($_SESSION['flash_product_error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars((string)$_SESSION['flash_product_error'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php unset($_SESSION['flash_product_error']); ?>
                    <?php endif; ?>
                    <form method="POST" action="productos&product_action=<?= $isEdit ? 'actualizar' : 'guardar' ?><?= $isEdit ? '&id=' . urlencode((string)($producto['id_producto'] ?? '')) : '' ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars((string)($producto['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Precio</label>
                                <input type="number" name="precio" class="form-control" step="0.01" required value="<?= htmlspecialchars((string)($producto['precio'] ?? '')) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripcion</label>
                                <textarea name="descripcion" class="form-control" rows="3" required><?= htmlspecialchars((string)($producto['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Imagen (URL o archivo en assets/img)</label>
                                <input type="text" name="imagen" class="form-control" value="<?= htmlspecialchars((string)($producto['imagen'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ej: foto.jpg o assets/img/foto.jpg o https://...">
                                <div class="form-text">Se guarda como texto en la BD (no sube archivos).</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Preview</label>
                                <?php if ($imgSrc !== ''): ?>
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Preview" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="border rounded bg-white d-flex align-items-center justify-content-center text-muted" style="height: 120px;">
                                        Sin imagen
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?= $isEdit ? 'Guardar Cambios' : 'Crear Producto' ?>
                            </button>
                            <a href="productos" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
</div>
