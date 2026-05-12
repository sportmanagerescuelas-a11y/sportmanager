<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use PDO;

final class PagosPageController
{
    public function show(): void
    {
        if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
            header('Location: index.php?url=login');
            exit();
        }

        require APP_BASE_PATH . '/config/conexion.php';
        if (!isset($conexion) || !($conexion instanceof PDO)) {
            http_response_code(500);
            echo 'Conexion no disponible.';
            return;
        }

        $idEvento = isset($_GET['id_evento']) ? (int) $_GET['id_evento'] : 0;

        $facturasUsuario = [];
        $idUsuarioSesion = (int)($_SESSION['id_usuario'] ?? ($_SESSION['usuario']['id_usuario'] ?? 0));
        if ($idUsuarioSesion > 0) {
            $stmtFacturas = $conexion->prepare(
                "SELECT f.id_factura, f.fecha_emision, f.monto,
                        e.titulo AS nombre_evento,
                        m.nombre_entidad AS metodo_pago_texto,
                        CONCAT(d.nombres, ' ', d.apellidos) AS nombre_deportista
                 FROM facturas f
                 INNER JOIN eventos e ON f.id_evento = e.id_evento
                 INNER JOIN metodos_pago m ON f.tipo_pago = m.id_metodo
                 INNER JOIN deportistas d ON f.id_deportista = d.id_deportista
                 WHERE f.id = :id_usuario
                 ORDER BY f.id_factura DESC"
            );
            $stmtFacturas->execute([':id_usuario' => $idUsuarioSesion]);
            $facturasUsuario = $stmtFacturas->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        View::render('pagos', [
            'facturasUsuario' => $facturasUsuario,
            'idEvento' => $idEvento,
        ]);
    }
}
