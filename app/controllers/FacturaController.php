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

    public function ver($id): void {
        $factura = $this->resolveFacturaBySession($id);
        if (!$factura) {
            http_response_code(404);
            die("Factura no encontrada.");
        }
        $this->renderWithSiteLayout('ver', ['factura' => $factura]);
    }

    public function descargarPdf($id): void {
        $factura = $this->resolveFacturaBySession($id);
        if (!$factura) {
            http_response_code(404);
            die("Factura no encontrada.");
        }
        require_once dirname(__DIR__) . '/views/factura/pdf.php';
    }

    public function verComprobante($id): void
    {
        $factura = $this->resolveFacturaBySession($id);
        $relativePath = is_array($factura) ? trim((string)($factura['comprobante_path'] ?? '')) : '';
        if ($relativePath === '') {
            http_response_code(404);
            exit('Esta factura no tiene un comprobante adjunto.');
        }

        $baseDirectory = realpath(dirname(__DIR__, 2) . '/storage/payment_receipts');
        $absolutePath = realpath(dirname(__DIR__, 2) . '/' . ltrim(str_replace('\\', '/', $relativePath), '/'));
        if ($baseDirectory === false || $absolutePath === false || !is_file($absolutePath)) {
            http_response_code(404);
            exit('No se encontró el comprobante solicitado.');
        }

        $basePrefix = rtrim(str_replace('\\', '/', $baseDirectory), '/') . '/';
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        if (!str_starts_with($normalizedPath, $basePrefix)) {
            http_response_code(403);
            exit('Acceso no autorizado al comprobante.');
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($absolutePath);
        header('Content-Type: ' . (is_string($mimeType) ? $mimeType : 'application/octet-stream'));
        header('Content-Length: ' . (string)filesize($absolutePath));
        header('Content-Disposition: inline; filename="comprobante-' . (int)$factura['id_factura'] . '.' . pathinfo($absolutePath, PATHINFO_EXTENSION) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($absolutePath);
        exit;
    }

    private function resolveFacturaBySession($id)
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



