<?php
require_once dirname(__DIR__) . '/models/Producto.php';

class ProductoController {
    private Producto $modelo;

    public function __construct(PDO $pdo) {
        $this->modelo = new Producto($pdo);
    }

    public function listar(): array {
        return $this->modelo->listar($this->schoolId());
    }

    public function obtenerPorId(string|int|null $id): array|null {
        if ($id === null || $id === '' || !is_numeric($id)) {
            return null;
        }
        $producto = $this->modelo->obtenerPorId($id, $this->schoolId());
        return is_array($producto) ? $producto : null;
    }

    public function mostrarProductos(): array {
        return $this->modelo->listar($this->schoolId());
    }

    public function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $payload = $this->productPayload();
            if ($payload === null) {
                $_SESSION['flash_product_error'] = 'Completa un nombre, una descripción y un precio válido.';
                header('Location: productos&product_action=nuevo');
                exit;
            }

            $ok = $this->modelo->crear($payload['nombre'], $payload['precio'], $payload['descripcion'], $payload['imagen'], $this->schoolId());
            if (!$ok) {
                $_SESSION['flash_product_error'] = $this->modelo->lastError();
                header('Location: productos&product_action=nuevo');
                exit;
            }
            header('Location: productos&created=1');
            exit;
        }
    }

    public function borrar(string|int|null $id): void {
        if ($id === null || $id === '' || !is_numeric($id)) {
            header('Location: productos');
            exit;
        }

        if (!$this->modelo->eliminar($id, $this->schoolId())) {
            $_SESSION['flash_product_error'] = $this->modelo->lastError();
        }
        header('Location: productos');
        exit;
    }

    public function actualizar(string|int|null $id): void {
        if ($id === null || $id === '' || !is_numeric($id)) {
            header('Location: productos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $payload = $this->productPayload();
            if ($payload === null || !$this->modelo->actualizar($id, $payload['nombre'], $payload['precio'], $payload['descripcion'], $payload['imagen'], $this->schoolId())) {
                $_SESSION['flash_product_error'] = $payload === null
                    ? 'Completa un nombre, una descripción y un precio válido.'
                    : $this->modelo->lastError();
                header('Location: productos&product_action=editar&id=' . urlencode((string)$id));
                exit;
            }
            header('Location: productos&updated=1');
            exit;
        }
    }

    /** @return array{nombre:string,precio:float,descripcion:string,imagen:string}|null */
    private function productPayload(): ?array
    {
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $descripcion = trim((string)($_POST['descripcion'] ?? ''));
        $precioRaw = trim((string)($_POST['precio'] ?? ''));
        if ($nombre === '' || strlen($nombre) > 30 || $descripcion === '' || !is_numeric($precioRaw) || (float)$precioRaw < 0) {
            return null;
        }

        return [
            'nombre' => $nombre,
            'precio' => (float)$precioRaw,
            'descripcion' => $descripcion,
            'imagen' => trim((string)($_POST['imagen'] ?? '')),
        ];
    }

    private function schoolId(): ?int
    {
        $id = (int)($_SESSION['usuario']['id_escuela'] ?? 0);
        return $id > 0 ? $id : null;
    }
}
?>
