<?php
require_once dirname(__DIR__, 2) . '/config/conexion.php';

class ReporteModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function obtenerTablas(): array {
        $stmt = $this->db->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function obtenerDatos(string $tabla): array {
        $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
        $stmt = $this->db->query("SELECT * FROM $tabla");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
