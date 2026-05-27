<?php
class Producto {
    private PDO $db;

    public function __construct(PDO $conexion) {
        $this->db = $conexion;
        $this->ensureSchoolColumn();
    }

    public function listar(?int $schoolId = null): array {
        if ($schoolId !== null && $schoolId > 0) {
            $sql = "SELECT * FROM productos WHERE id_escuela = ? ORDER BY id_producto DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$schoolId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = "SELECT * FROM productos ORDER BY id_producto DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear(?string $nombre, string|float|int|null $precio, ?string $descripcion, ?string $imagen, ?int $schoolId): bool {
        $sql = "INSERT INTO productos (nombre, precio, descripcion, imagen, id_escuela) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $precio, $descripcion, $imagen, $schoolId]);
    }

    public function obtenerPorId(string|int $id, ?int $schoolId = null): array|false {
        $sql = "SELECT * FROM productos WHERE id_producto = ?";
        $params = [$id];
        if ($schoolId !== null && $schoolId > 0) {
            $sql .= " AND id_escuela = ?";
            $params[] = $schoolId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar(string|int $id, ?string $nombre, string|float|int|null $precio, ?string $descripcion, ?string $imagen, ?int $schoolId): bool {
        $sql = "UPDATE productos SET nombre=?, precio=?, descripcion=?, imagen=? WHERE id_producto=? AND id_escuela=?";
        return $this->db->prepare($sql)->execute([$nombre, $precio, $descripcion, $imagen, $id, $schoolId]);
    }

    public function eliminar(string|int $id, ?int $schoolId): bool {
        $sql = "DELETE FROM productos WHERE id_producto = ? AND id_escuela = ?";
        return $this->db->prepare($sql)->execute([$id, $schoolId]);
    }

    private function ensureSchoolColumn(): void {
        try {
            $existsStmt = $this->db->query("SHOW COLUMNS FROM productos LIKE 'id_escuela'");
            $exists = $existsStmt !== false && $existsStmt->fetch(PDO::FETCH_ASSOC) !== false;
            if (!$exists) {
                $this->db->exec("ALTER TABLE productos ADD COLUMN id_escuela INT(11) NULL AFTER imagen");
            }
        } catch (Throwable) {
            // Mantener compatibilidad sin romper flujo en entornos restringidos.
        }
    }
}
?>
