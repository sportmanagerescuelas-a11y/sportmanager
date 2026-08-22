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
                         COALESCE(CONCAT(d.nombres, ' ', d.apellidos), 'No aplica') AS nombre_deportista,
                         COALESCE(e.titulo, f.descripcion) AS nombre_evento,
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

    public function obtenerTodasPorEscuela(int $schoolId): array {
        $query = "SELECT f.*, 
                         u.nombres AS nombre_usuario, 
                         COALESCE(CONCAT(d.nombres, ' ', d.apellidos), 'No aplica') AS nombre_deportista,
                         COALESCE(e.titulo, f.descripcion) AS nombre_evento,
                         m.nombre_entidad AS metodo_pago_texto
                  FROM facturas f
                  INNER JOIN usuarios u ON f.id = u.id_usuario
                  LEFT JOIN deportistas d ON f.id_deportista = d.id_deportista
                  LEFT JOIN eventos e ON f.id_evento = e.id_evento
                  LEFT JOIN metodos_pago m ON f.tipo_pago = m.id_metodo
                  WHERE u.id_escuela = :id_escuela
                  ORDER BY f.id_factura DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['id_escuela' => $schoolId]);
        return $stmt->fetchAll();
    }

    public function obtenerFacturaPorId($id_factura) {
        $query = "SELECT f.*, 
                         u.nombres AS nombre_usuario, 
                         COALESCE(CONCAT(d.nombres, ' ', d.apellidos), 'No aplica') AS nombre_deportista,
                         COALESCE(e.titulo, f.descripcion) AS nombre_evento,
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

    public function obtenerFacturaPorIdYEscuela($id_factura, int $schoolId) {
        $query = "SELECT f.*, 
                         u.nombres AS nombre_usuario, 
                         COALESCE(CONCAT(d.nombres, ' ', d.apellidos), 'No aplica') AS nombre_deportista,
                         COALESCE(e.titulo, f.descripcion) AS nombre_evento,
                         m.nombre_entidad AS metodo_pago_texto
                  FROM facturas f
                  INNER JOIN usuarios u ON f.id = u.id_usuario
                  LEFT JOIN deportistas d ON f.id_deportista = d.id_deportista
                  LEFT JOIN eventos e ON f.id_evento = e.id_evento
                  LEFT JOIN metodos_pago m ON f.tipo_pago = m.id_metodo
                  WHERE f.id_factura = :id_factura
                    AND u.id_escuela = :id_escuela";
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'id_factura' => $id_factura,
            'id_escuela' => $schoolId,
        ]);
        return $stmt->fetch();
    }

    public function obtenerFacturaPorIdYUsuario($id_factura, int $idUsuario) {
        $query = "SELECT f.*,
                         u.nombres AS nombre_usuario,
                         COALESCE(CONCAT(d.nombres, ' ', d.apellidos), 'No aplica') AS nombre_deportista,
                         COALESCE(e.titulo, f.descripcion) AS nombre_evento,
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

    public function actualizarComprobantePath(int $id_factura, string $comprobantePath): bool {
        $query = "UPDATE facturas
                  SET comprobante_path = :comprobante_path
                  WHERE id_factura = :id_factura";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'id_factura' => $id_factura,
            'comprobante_path' => $comprobantePath,
        ]);
    }
}
