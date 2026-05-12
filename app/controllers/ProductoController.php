<?php
require_once dirname(__DIR__) . '/models/Producto.php';

class ProductoController {
    private Producto $modelo;

    public function __construct(PDO $pdo) {
        $this->modelo = new Producto($pdo);
    }

    public function listar(): array {
        return $this->modelo->listar();
    }

    public function obtenerPorId(string|int|null $id): array|null {
        if ($id === null || $id === '' || !is_numeric($id)) {
            return null;
        }
        $producto = $this->modelo->obtenerPorId($id);
        return is_array($producto) ? $producto : null;
    }

    public function mostrarProductos(): array {
        return $this->modelo->listar();
    }

    public function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->modelo->crear(
                $_POST['nombre'] ?? null,
                $_POST['precio'] ?? null,
                $_POST['descripcion'] ?? null,
                $_POST['imagen'] ?? null
            );
            header("Location: productos.php");
            exit;
        }
    }

    public function borrar(string|int|null $id): void {
        if ($id === null || $id === '' || !is_numeric($id)) {
            header("Location: productos.php");
            exit;
        }

        $this->modelo->eliminar($id);
        header("Location: productos.php");
        exit;
    }

    public function actualizar(string|int|null $id): void {
        if ($id === null || $id === '' || !is_numeric($id)) {
            header("Location: productos.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->modelo->actualizar(
                $id,
                $_POST['nombre'] ?? null,
                $_POST['precio'] ?? null,
                $_POST['descripcion'] ?? null,
                $_POST['imagen'] ?? null
            );
            header("Location: productos.php");
            exit;
        }
    }
}
?>
