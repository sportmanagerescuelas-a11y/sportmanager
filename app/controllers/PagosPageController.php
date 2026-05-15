<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use PDO;

final class PagosPageController
{
    /**
     * @param array<string,mixed> $data
     */
    private function renderWithLayout(string $viewName, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require APP_PATH . '/views/layout/header.php';
        View::render($viewName, $data);
        require APP_PATH . '/views/layout/footer.php';
    }

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
                "SELECT f.id_factura, f.numero_factura, f.fecha_emision, f.monto, f.descripcion,
                        COALESCE(e.titulo, f.descripcion) AS nombre_evento,
                        COALESCE(m.nombre_entidad, 'N/A') AS metodo_pago_texto,
                        COALESCE(CONCAT(d.nombres, ' ', d.apellidos), 'No aplica') AS nombre_deportista
                 FROM facturas f
                 LEFT JOIN eventos e ON f.id_evento = e.id_evento
                 LEFT JOIN metodos_pago m ON f.tipo_pago = m.id_metodo
                 LEFT JOIN deportistas d ON f.id_deportista = d.id_deportista
                 WHERE f.id = :id_usuario
                 ORDER BY f.id_factura DESC"
            );
            $stmtFacturas->execute([':id_usuario' => $idUsuarioSesion]);
            $facturasUsuario = $stmtFacturas->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $this->renderWithLayout('pagos', [
            'facturasUsuario' => $facturasUsuario,
            'idEvento' => $idEvento,
        ]);
    }
}
