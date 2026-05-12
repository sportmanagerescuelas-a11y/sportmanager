<?php
class Deportista
{
    public static function paginate(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $offset = ($page - 1) * $perPage;

        $pdo = Database::pdo();

        $total = (int)$pdo->query('SELECT COUNT(*) FROM deportistas')->fetchColumn();

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
             ORDER BY d.id_deportista ASC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return [
            'rows' => $rows,
            'total' => $total,
        ];
    }
}
