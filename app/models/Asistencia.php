<?php
class Asistencia
{
    /**
     * @param array<int,int> $ids
     * @return array<int,int|string>
     */
    public static function findExisting(string $fecha, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $pdo = Database::pdo();

        $placeholders = [];
        $params = [':fecha' => $fecha];
        foreach ($ids as $i => $id) {
            $key = ':id' . $i;
            $placeholders[] = $key;
            $params[$key] = $id;
        }

        $sql = 'SELECT id_deportista FROM asistencia WHERE DATE(fecha) = :fecha AND id_deportista IN (' . implode(',', $placeholders) . ')';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $type = $key === ':fecha' ? PDO::PARAM_STR : PDO::PARAM_INT;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param array<int,int> $ids
     * @return array<int,array{estado:string,comentario:string}>
     */
    public static function forDateAndIds(string $fecha, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $pdo = Database::pdo();

        $placeholders = [];
        $params = [':fecha' => $fecha];
        foreach ($ids as $i => $id) {
            $key = ':id' . $i;
            $placeholders[] = $key;
            $params[$key] = $id;
        }

        $sql = 'SELECT id_deportista, estado, COALESCE(comentario, "") AS comentario
                FROM asistencia
                WHERE DATE(fecha) = :fecha
                  AND id_deportista IN (' . implode(',', $placeholders) . ')';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $type = $key === ':fecha' ? PDO::PARAM_STR : PDO::PARAM_INT;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();

        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int)($row['id_deportista'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $rows[$id] = [
                'estado' => trim((string)($row['estado'] ?? '')),
                'comentario' => trim((string)($row['comentario'] ?? '')),
            ];
        }

        return $rows;
    }

    /**
     * @param array<int,array{id_deportista:int,estado:string,comentario:?string}> $rows
     */
    public static function insertMany(array $rows, string $fecha): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO asistencia (id_deportista, fecha, estado, comentario) VALUES (:id_deportista, :fecha, :estado, :comentario)'
        );

        $fechaCompleta = $fecha . ' 00:00:00';

        foreach ($rows as $row) {
            $stmt->execute([
                ':id_deportista' => $row['id_deportista'],
                ':fecha' => $fechaCompleta,
                ':estado' => $row['estado'],
                ':comentario' => $row['comentario'],
            ]);
        }

        $pdo->commit();
    }

    /**
     * @param array<int,array{id_deportista:int,estado:string,comentario:?string}> $rows
     */
    public static function replaceMany(array $rows, string $fecha): void
    {
        if (empty($rows)) {
            return;
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        $ids = array_values(array_filter(array_map(static fn(array $row): int => (int)($row['id_deportista'] ?? 0), $rows), static fn(int $id): bool => $id > 0));
        if (!empty($ids)) {
            $placeholders = [];
            $params = [':fecha' => $fecha];
            foreach ($ids as $i => $id) {
                $key = ':id' . $i;
                $placeholders[] = $key;
                $params[$key] = $id;
            }

            $deleteSql = 'DELETE FROM asistencia WHERE DATE(fecha) = :fecha AND id_deportista IN (' . implode(',', $placeholders) . ')';
            $deleteStmt = $pdo->prepare($deleteSql);
            foreach ($params as $key => $value) {
                $type = $key === ':fecha' ? PDO::PARAM_STR : PDO::PARAM_INT;
                $deleteStmt->bindValue($key, $value, $type);
            }
            $deleteStmt->execute();
        }

        $stmt = $pdo->prepare(
            'INSERT INTO asistencia (id_deportista, fecha, estado, comentario) VALUES (:id_deportista, :fecha, :estado, :comentario)'
        );

        $fechaCompleta = $fecha . ' 00:00:00';
        foreach ($rows as $row) {
            $id = (int)($row['id_deportista'] ?? 0);
            $estado = trim((string)($row['estado'] ?? ''));
            if ($id <= 0 || $estado === '') {
                continue;
            }

            $stmt->execute([
                ':id_deportista' => $id,
                ':fecha' => $fechaCompleta,
                ':estado' => $estado,
                ':comentario' => $row['comentario'] ?? null,
            ]);
        }

        $pdo->commit();
    }

    /**
     * @return array<int,string>
     */
    public static function datesForGuardian(int $userId): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT DISTINCT DATE(a.fecha) AS fecha
             FROM asistencia a
             INNER JOIN deportistas d ON d.id_deportista = a.id_deportista
             WHERE d.id_usuario = :id_usuario
             ORDER BY fecha DESC"
        );
        $stmt->execute([':id_usuario' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function forGuardianByDate(int $userId, string $fecha): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT d.id_deportista,
                    d.nombres,
                    d.apellidos,
                    d.jornada,
                    c.nombre_cat AS categoria,
                    n.nombre AS nivel,
                    a.estado,
                    a.comentario,
                    DATE(a.fecha) AS fecha
             FROM asistencia a
             INNER JOIN deportistas d ON d.id_deportista = a.id_deportista
             LEFT JOIN categoria c ON c.id_categoria = d.id_categoria
             LEFT JOIN nivel n ON n.id_nivel = d.id_nivel
             WHERE d.id_usuario = :id_usuario
               AND DATE(a.fecha) = :fecha
             ORDER BY d.nombres ASC, d.apellidos ASC"
        );
        $stmt->execute([
            ':id_usuario' => $userId,
            ':fecha' => $fecha,
        ]);
        return $stmt->fetchAll();
    }
}
