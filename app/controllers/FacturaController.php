<?php
require_once dirname(__DIR__) . '/models/FacturaModel.php';

class FacturaController {
    private FacturaModel $model;

    public function __construct(PDO $pdo) {
        $this->model = new FacturaModel($pdo);
    }

    // NUEVA ACCI??N: Carga la lista de todas las facturas
    public function listar(): void {
        $facturas = $this->model->obtenerTodas();
        require_once dirname(__DIR__) . '/views/factura/listar.php';
    }

    public function ver(string|int $id): void {
        $factura = $this->resolveFacturaBySession($id);
        if (!$factura) {
            die("Factura no encontrada.");
        }
        require_once dirname(__DIR__) . '/views/factura/ver.php';
    }

    public function descargarPdf(string|int $id): void {
        $factura = $this->resolveFacturaBySession($id);
        if (!$factura) {
            die("Factura no encontrada.");
        }
        require_once dirname(__DIR__) . '/views/factura/pdf.php';
    }

    private function resolveFacturaBySession(string|int $id): array|false
    {
        $rol = (int)($_SESSION['rol'] ?? 0);
        if ($rol === 3) {
            return $this->model->obtenerFacturaPorId($id);
        }

        $idUsuario = (int)($_SESSION['id_usuario'] ?? 0);
        if ($idUsuario <= 0) {
            return false;
        }

        return $this->model->obtenerFacturaPorIdYUsuario($id, $idUsuario);
    }
}



