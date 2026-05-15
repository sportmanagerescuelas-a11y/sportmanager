<?php
class FacturaModel {
    private PDO $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    public function obtenerTodas(): array {
        // Concatenamos nombres y apellidos del deportista
        $query = "SELECT f.*, 
                         u.nombres AS nombre_usuario, 
                         CONCAT(d.nombres, ' ', d.apellidos) AS nombre_deportista, 
                         e.titulo AS nombre_evento,
                         m.nombre_entidad AS metodo_pago_texto
                  FROM facturas f
                  INNER JOIN usuarios u ON f.id = u.id_usuario
                  LEFT JOIN deportistas d ON f.id_deportista = d.id_deportista
                  LEFT JOIN eventos e ON f.id_evento = e.id_evento
                  LEFT JOIN metodos_pago m ON f.tipo_pago = m.id_metodo
                  ORDER BY f.id_factura DESC";
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerFacturaPorId(string|int $id_factura): array|false {
        $query = "SELECT f.*, 
                         u.nombres AS nombre_usuario, 
                         CONCAT(d.nombres, ' ', d.apellidos) AS nombre_deportista, 
                         e.titulo AS nombre_evento,
                         m.nombre_entidad AS metodo_pago_texto
                  FROM facturas f
                  INNER JOIN usuarios u ON f.id = u.id_usuario
                  LEFT JOIN deportistas d ON f.id_deportista = d.id_deportista
                  LEFT JOIN eventos e ON f.id_evento = e.id_evento
                  LEFT JOIN metodos_pago m ON f.tipo_pago = m.id_metodo
                  WHERE f.id_factura = :id_factura";
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id_factura' => $id_factura]);
        return $stmt->fetch();
    }

    public function obtenerFacturaPorIdYUsuario(string|int $id_factura, int $idUsuario): array|false {
        $query = "SELECT f.*,
                         u.nombres AS nombre_usuario,
                         CONCAT(d.nombres, ' ', d.apellidos) AS nombre_deportista,
                         e.titulo AS nombre_evento,
                         m.nombre_entidad AS metodo_pago_texto
                  FROM facturas f
                  INNER JOIN usuarios u ON f.id = u.id_usuario
                  LEFT JOIN deportistas d ON f.id_deportista = d.id_deportista
                  LEFT JOIN eventos e ON f.id_evento = e.id_evento
                  LEFT JOIN metodos_pago m ON f.tipo_pago = m.id_metodo
                  WHERE f.id_factura = :id_factura
                    AND f.id = :id_usuario";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'id_factura' => $id_factura,
            'id_usuario' => $idUsuario,
        ]);
        return $stmt->fetch();
    }
}
