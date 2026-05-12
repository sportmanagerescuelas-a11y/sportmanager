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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar Producto' : 'Nuevo Producto' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php?url=productos">Gestion de Productos</a>
            <div class="ms-auto dropdown">
                <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="adminMenuProductos" data-bs-toggle="dropdown" aria-expanded="false">
                    Menu admin
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminMenuProductos">
                    <li><a class="dropdown-item" href="index.php?url=dashboard">Mi panel</a></li>
                    <li><a class="dropdown-item" href="index.php?url=admin_usuarios">Gestionar usuarios</a></li>
                    <li><a class="dropdown-item" href="index.php?url=deportistas">Deportistas</a></li>
                    <li><a class="dropdown-item" href="index.php?url=gestion_eventos">Eventos</a></li>
                    <li><a class="dropdown-item" href="index.php?url=reportes">Reportes</a></li>
                    <li><a class="dropdown-item" href="index.php?url=productos">Productos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="index.php?url=logout">Cerrar sesion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="mb-3">
            <a href="index.php?url=productos" class="btn btn-secondary">Volver a productos</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><?= $isEdit ? 'Editar Producto' : 'Agregar Producto' ?></h5>
            </div>
            <div class="card-body">
                <?php if ($isEdit && empty($producto)): ?>
                    <div class="alert alert-danger mb-3">Producto no encontrado.</div>
                    <a href="index.php?url=productos" class="btn btn-secondary">Volver</a>
                <?php else: ?>
                    <form method="POST" action="index.php?url=productos&product_action=<?= $isEdit ? 'actualizar' : 'guardar' ?><?= $isEdit ? '&id=' . urlencode((string)($producto['id_producto'] ?? '')) : '' ?>">
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
                            <a href="index.php?url=productos" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
