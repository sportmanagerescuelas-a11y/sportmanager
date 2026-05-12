<?php
class Deportista
{
    /**
     * @param array{search?:string,categoria?:string,jornada?:string} $filters
     */
    public static function paginate(int $page, int $perPage, array $filters = []): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $offset = ($page - 1) * $perPage;

        $pdo = Database::pdo();

        $where = [];
        $params = [];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = "(d.nombres LIKE :search_nombres OR d.apellidos LIKE :search_apellidos OR CONCAT(d.nombres, ' ', d.apellidos) LIKE :search_nombre_completo OR d.id_deportista LIKE :search_documento)";
            $params[':search_nombres'] = '%' . $search . '%';
            $params[':search_apellidos'] = '%' . $search . '%';
            $params[':search_nombre_completo'] = '%' . $search . '%';
            $params[':search_documento'] = '%' . $search . '%';
        }

        $categoria = trim((string)($filters['categoria'] ?? ''));
        if ($categoria !== '' && ctype_digit($categoria)) {
            $where[] = 'd.id_categoria = :categoria';
            $params[':categoria'] = (int)$categoria;
        }

        $jornada = trim((string)($filters['jornada'] ?? ''));
        if ($jornada !== '') {
            $where[] = 'd.jornada = :jornada';
            $params[':jornada'] = $jornada;
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM deportistas d' . $whereSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT d.id_deportista,
                    d.tipo_documento,
                    d.foto,
                    d.nombres,
                    d.apellidos,
                    d.fecha_nacimiento,
                    d.jornada,
                    d.fecha_registro,
                    d.genero,
                    d.id_usuario,
                    c.nombre_cat AS categoria,
                    n.nombre AS nivel,
                    CONCAT(COALESCE(u.nombres, ''), ' ', COALESCE(u.apellidos, '')) AS usuario_creador
             FROM deportistas d
             LEFT JOIN categoria c ON d.id_categoria = c.id_categoria
             LEFT JOIN nivel n ON d.id_nivel = n.id_nivel
             LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario
             {$whereSql}
             ORDER BY d.id_deportista ASC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return [
            'rows' => $rows,
            'total' => $total,
        ];
    }

    public static function categories(): array
    {
        return Database::pdo()->query('SELECT id_categoria, nombre_cat FROM categoria ORDER BY nombre_cat ASC')->fetchAll();
    }

    public static function jornadas(): array
    {
        return Database::pdo()->query("SELECT DISTINCT jornada FROM deportistas WHERE jornada <> '' ORDER BY jornada ASC")->fetchAll(PDO::FETCH_COLUMN);
    }
}
