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
    public function obtenerDatos(string $tabla, ?int $schoolId = null): array {
        $tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
        if ($tabla === '') {
            return [];
        }

        $query = "SELECT * FROM $tabla";
        $params = [];

        if ($schoolId !== null && $schoolId > 0) {
            if ($tabla === 'usuarios') {
                $query .= ' WHERE id_escuela = :id_escuela';
                $params[':id_escuela'] = $schoolId;
            } elseif ($tabla === 'deportistas') {
                $query = "SELECT d.*
                          FROM deportistas d
                          INNER JOIN usuarios u ON u.id_usuario = d.id_usuario
                          WHERE u.id_escuela = :id_escuela";
                $params[':id_escuela'] = $schoolId;
            } elseif ($tabla === 'uniformes') {
                $query = "SELECT uni.*
                          FROM uniformes uni
                          INNER JOIN deportistas d ON d.id_deportista = uni.id_deportista
                          INNER JOIN usuarios u ON u.id_usuario = d.id_usuario
                          WHERE u.id_escuela = :id_escuela";
                $params[':id_escuela'] = $schoolId;
            } elseif ($tabla === 'facturas') {
                $query = "SELECT f.*
                          FROM facturas f
                          INNER JOIN usuarios u ON u.id_usuario = f.id
                          WHERE u.id_escuela = :id_escuela";
                $params[':id_escuela'] = $schoolId;
            } elseif ($tabla === 'productos') {
                $columnsStmt = $this->db->query("SHOW COLUMNS FROM productos LIKE 'id_escuela'");
                $hasSchoolColumn = $columnsStmt !== false && $columnsStmt->fetch(PDO::FETCH_ASSOC) !== false;
                if ($hasSchoolColumn) {
                    $query .= ' WHERE id_escuela = :id_escuela';
                    $params[':id_escuela'] = $schoolId;
                }
            }
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
