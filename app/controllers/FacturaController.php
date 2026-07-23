<?php
require_once dirname(__DIR__) . '/models/FacturaModel.php';

class FacturaController {
    private const MAX_RECEIPT_SIZE = 5242880;
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

    public function subirComprobante($id): void
    {
        $factura = $this->resolveFacturaBySession($id);
        if (!$factura) {
            http_response_code(404);
            die("Factura no encontrada.");
        }

        if ((string)($_GET['saved'] ?? '') === '1' || !empty($_SESSION['flash_receipt_saved'])) {
            unset($_SESSION['flash_receipt_saved']);
            $messageMode = 'receipt_saved';
            require dirname(__DIR__) . '/views/layout/mensaje.php';
            return;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $this->validateReceiptUpload();
            if ($error === '') {
                $newPath = '';
                try {
                    $newPath = $this->storeReceiptUpload();
                    $this->model->actualizarComprobantePath((int)$factura['id_factura'], $newPath);

                    $oldRelativePath = trim((string)($factura['comprobante_path'] ?? ''));
                    if ($oldRelativePath !== '') {
                        $oldAbsolutePath = realpath(dirname(__DIR__, 2) . '/' . ltrim(str_replace('\\', '/', $oldRelativePath), '/'));
                        $baseDirectory = realpath(dirname(__DIR__, 2) . '/storage/payment_receipts');
                        if ($oldAbsolutePath && $baseDirectory && str_starts_with(str_replace('\\', '/', $oldAbsolutePath), rtrim(str_replace('\\', '/', $baseDirectory), '/') . '/')) {
                            @unlink($oldAbsolutePath);
                        }
                    }

                    $_SESSION['flash_receipt_saved'] = true;
                    header('Location: index.php?action=subir_comprobante&id=' . urlencode((string)$factura['id_factura']) . '&saved=1');
                    exit;
                } catch (Throwable $e) {
                    if ($newPath !== '') {
                        $absolutePath = realpath(dirname(__DIR__, 2) . '/' . ltrim(str_replace('\\', '/', $newPath), '/'));
                        if ($absolutePath && is_file($absolutePath)) {
                            @unlink($absolutePath);
                        }
                    }
                    $error = 'No se pudo guardar el comprobante. Intenta nuevamente.';
                }
            }
        }

        $this->renderWithSiteLayout('subir_comprobante', [
            'factura' => $factura,
            'error' => $error,
        ]);
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

    private function validateReceiptUpload(): string
    {
        $file = $_FILES['comprobante'] ?? null;
        if (!is_array($file) || empty($file['tmp_name'])) {
            return 'Debes adjuntar el comprobante de pago.';
        }

        if ((int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'No se pudo cargar el comprobante. Intenta nuevamente.';
        }

        if ((int)($file['size'] ?? 0) > self::MAX_RECEIPT_SIZE) {
            return 'El comprobante no puede superar 5 MB.';
        }

        if ((int)($file['size'] ?? 0) <= 0 || !is_uploaded_file((string)$file['tmp_name'])) {
            return 'El archivo del comprobante no es válido.';
        }

        $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowedMimeTypes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
            'pdf' => ['application/pdf'],
        ];
        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);
        if (!isset($allowedMimeTypes[$extension]) || !is_string($mimeType) || !in_array($mimeType, $allowedMimeTypes[$extension], true)) {
            return 'El comprobante debe ser imagen JPG, PNG, WEBP o PDF.';
        }

        return '';
    }

    private function storeReceiptUpload(): string
    {
        $file = $_FILES['comprobante'];
        $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        $directory = dirname(__DIR__, 2) . '/storage/payment_receipts';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('No fue posible crear el directorio de comprobantes.');
        }

        $fileName = 'comprobante_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $absolutePath = $directory . '/' . $fileName;
        if (!move_uploaded_file((string)$file['tmp_name'], $absolutePath)) {
            throw new RuntimeException('No fue posible almacenar el comprobante.');
        }

        return 'storage/payment_receipts/' . $fileName;
    }

    private function renderWithSiteLayout(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/factura/' . $view . '.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
    }
}



