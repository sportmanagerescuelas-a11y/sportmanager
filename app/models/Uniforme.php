<?php

namespace App\Models;

use PDO;
use Throwable;

final class Uniforme
{
    private PDO $db;
    private string $lastError = '';

    public function __construct()
    {
        require APP_BASE_PATH . '/config/conexion.php';
        if (!isset($conexion) || !($conexion instanceof PDO)) {
            throw new \RuntimeException('No se pudo inicializar la conexion de base de datos.');
        }

        $this->db = $conexion;
    }

    public function lastError(): string
    {
        return $this->lastError;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function allForRole(int $role, int $userId): array
    {
        $sql = "
            SELECT u.*,
                   d.nombres,
                   d.apellidos,
                   d.id_categoria,
                   c.nombre_cat,
                   owner.nombres AS acudiente_nombres,
                   owner.apellidos AS acudiente_apellidos
            FROM uniformes u
            INNER JOIN deportistas d ON d.id_deportista = u.id_deportista
            INNER JOIN categoria c ON c.id_categoria = d.id_categoria
            INNER JOIN usuarios owner ON owner.id_usuario = d.id_usuario
        ";

        if ($role === 1) {
            $stmt = $this->db->prepare($sql . ' WHERE d.id_usuario = :id_usuario ORDER BY c.nombre_cat ASC, u.numero_camiseta ASC');
            $stmt->execute([':id_usuario' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $stmt = $this->db->query($sql . ' ORDER BY c.nombre_cat ASC, u.numero_camiseta ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById(int $id, int $role, int $userId): ?array
    {
        $sql = "
            SELECT u.*,
                   d.nombres,
                   d.apellidos,
                   d.id_categoria,
                   c.nombre_cat
            FROM uniformes u
            INNER JOIN deportistas d ON d.id_deportista = u.id_deportista
            INNER JOIN categoria c ON c.id_categoria = d.id_categoria
            WHERE u.id_uniforme = :id_uniforme
        ";

        $params = [':id_uniforme' => $id];
        if ($role === 1) {
            $sql .= ' AND d.id_usuario = :id_usuario';
            $params[':id_usuario'] = $userId;
        }

        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function athletesForAssignment(?int $currentUniformId = null): array
    {
        $sql = "
            SELECT d.id_deportista,
                   d.nombres,
                   d.apellidos,
                   c.nombre_cat,
                   owner.nombres AS acudiente_nombres,
                   owner.apellidos AS acudiente_apellidos
            FROM deportistas d
            INNER JOIN categoria c ON c.id_categoria = d.id_categoria
            INNER JOIN usuarios owner ON owner.id_usuario = d.id_usuario
            LEFT JOIN uniformes u ON u.id_deportista = d.id_deportista
            WHERE u.id_uniforme IS NULL
        ";
        $params = [];

        if ($currentUniformId !== null) {
            $sql .= ' OR u.id_uniforme = :current_uniform_id';
            $params[':current_uniform_id'] = $currentUniformId;
        }

        $stmt = $this->db->prepare($sql . ' ORDER BY c.nombre_cat ASC, d.nombres ASC, d.apellidos ASC');
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function athleteExists(int $athleteId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM deportistas WHERE id_deportista = :id_deportista');
        $stmt->execute([':id_deportista' => $athleteId]);
        return (bool)$stmt->fetchColumn();
    }

    public function athleteHasUniform(int $athleteId, ?int $ignoreUniformId = null): bool
    {
        $sql = 'SELECT 1 FROM uniformes WHERE id_deportista = :id_deportista';
        $params = [':id_deportista' => $athleteId];

        if ($ignoreUniformId !== null) {
            $sql .= ' AND id_uniforme <> :id_uniforme';
            $params[':id_uniforme'] = $ignoreUniformId;
        }

        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    public function numberExistsInAthleteCategory(int $number, int $athleteId, ?int $ignoreUniformId = null): bool
    {
        $sql = "
            SELECT 1
            FROM uniformes u
            INNER JOIN deportistas assigned ON assigned.id_deportista = u.id_deportista
            INNER JOIN deportistas target ON target.id_deportista = :id_deportista
            WHERE u.numero_camiseta = :numero_camiseta
              AND assigned.id_categoria = target.id_categoria
        ";
        $params = [
            ':id_deportista' => $athleteId,
            ':numero_camiseta' => $number,
        ];

        if ($ignoreUniformId !== null) {
            $sql .= ' AND u.id_uniforme <> :id_uniforme';
            $params[':id_uniforme'] = $ignoreUniformId;
        }

        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): bool
    {
        try {
            $nextId = (int)$this->db->query('SELECT COALESCE(MAX(id_uniforme), 0) + 1 FROM uniformes')->fetchColumn();
            $stmt = $this->db->prepare("
                INSERT INTO uniformes
                (id_uniforme, numero_camiseta, id_deportista, tipo_uniforme, nombre_camiseta, descripcion_uniforme)
                VALUES
                (:id_uniforme, :numero_camiseta, :id_deportista, :tipo_uniforme, :nombre_camiseta, :descripcion_uniforme)
            ");

            $ok = $stmt->execute([
                ':id_uniforme' => $nextId,
                ':numero_camiseta' => $data['numero_camiseta'],
                ':id_deportista' => $data['id_deportista'],
                ':tipo_uniforme' => $data['tipo_uniforme'],
                ':nombre_camiseta' => $data['nombre_camiseta'],
                ':descripcion_uniforme' => $data['descripcion_uniforme'],
            ]);

            $this->lastError = $ok ? '' : 'No se pudo registrar el uniforme.';
            return $ok;
        } catch (Throwable $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE uniformes
                SET numero_camiseta = :numero_camiseta,
                    id_deportista = :id_deportista,
                    tipo_uniforme = :tipo_uniforme,
                    nombre_camiseta = :nombre_camiseta,
                    descripcion_uniforme = :descripcion_uniforme
                WHERE id_uniforme = :id_uniforme
            ");

            $ok = $stmt->execute([
                ':id_uniforme' => $id,
                ':numero_camiseta' => $data['numero_camiseta'],
                ':id_deportista' => $data['id_deportista'],
                ':tipo_uniforme' => $data['tipo_uniforme'],
                ':nombre_camiseta' => $data['nombre_camiseta'],
                ':descripcion_uniforme' => $data['descripcion_uniforme'],
            ]);

            $this->lastError = $ok ? '' : 'No se pudo actualizar el uniforme.';
            return $ok;
        } catch (Throwable $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM uniformes WHERE id_uniforme = :id_uniforme');
            $ok = $stmt->execute([':id_uniforme' => $id]);
            $this->lastError = $ok ? '' : 'No se pudo eliminar el uniforme.';
            return $ok;
        } catch (Throwable $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
}

