<?php
class Producto {
    private PDO $db;

    public function __construct(PDO $conexion) {
        $this->db = $conexion;
    }

    public function listar(): array {
        $sql = "SELECT * FROM productos";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear(?string $nombre, string|float|int|null $precio, ?string $descripcion, ?string $imagen): bool {
        $sql = "INSERT INTO productos (nombre, precio, descripcion, imagen) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $precio, $descripcion, $imagen]);
    }

    public function obtenerPorId(string|int $id): array|false {
        $sql = "SELECT * FROM productos WHERE id_producto = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar(string|int $id, ?string $nombre, string|float|int|null $precio, ?string $descripcion, ?string $imagen): bool {
        $sql = "UPDATE productos SET nombre=?, precio=?, descripcion=?, imagen=? WHERE id_producto=?";
        return $this->db->prepare($sql)->execute([$nombre, $precio, $descripcion, $imagen, $id]);
    }

    public function eliminar(string|int $id): bool {
        $sql = "DELETE FROM productos WHERE id_producto = ?";
        return $this->db->prepare($sql)->execute([$id]);
    }
}
?>
