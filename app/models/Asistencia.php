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
}
