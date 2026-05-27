<?php
require_once dirname(__DIR__) . '/models/FacturaModel.php';

class FacturaController {
    private FacturaModel $model;

    public function __construct(PDO $pdo) {
        $this->model = new FacturaModel($pdo);
    }

    // NUEVA ACCI??N: Carga la lista de todas las facturas
    public function listar(): void {
        $rol = (int)($_SESSION['rol'] ?? 0);
        $schoolId = (int)($_SESSION['usuario']['id_escuela'] ?? 0);
        if ($rol === 4) {
            $facturas = $this->model->obtenerTodas();
        } elseif ($schoolId > 0) {
            $facturas = $this->model->obtenerTodasPorEscuela($schoolId);
        } else {
            $facturas = [];
        }
        $this->renderWithSiteLayout('listar', ['facturas' => $facturas]);
    }

    public function ver(string|int $id): void {
        $factura = $this->resolveFacturaBySession($id);
        if (!$factura) {
            die("Factura no encontrada.");
        }
        $this->renderWithSiteLayout('ver', ['factura' => $factura]);
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
            $schoolId = (int)($_SESSION['usuario']['id_escuela'] ?? 0);
            if ($schoolId <= 0) {
                return false;
            }
            return $this->model->obtenerFacturaPorIdYEscuela($id, $schoolId);
        }
        if ($rol === 4) {
            return $this->model->obtenerFacturaPorId($id);
        }

        $idUsuario = (int)($_SESSION['id_usuario'] ?? 0);
        if ($idUsuario <= 0) {
            return false;
        }

        return $this->model->obtenerFacturaPorIdYUsuario($id, $idUsuario);
    }

    private function renderWithSiteLayout(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/factura/' . $view . '.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
    }
}



