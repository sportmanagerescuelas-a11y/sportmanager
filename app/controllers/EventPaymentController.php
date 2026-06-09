<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

final class EventPaymentController
{
    private const MAX_RECEIPT_SIZE = 5242880;

    public function handle(): void
    {
        if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
            header('Location: login');
            exit();
        }

        require APP_BASE_PATH . '/config/conexion.php';
        if (!isset($conexion) || !($conexion instanceof PDO)) {
            $this->renderStatusError('500', 'Error interno', 'No fue posible conectar con la base de datos.');
            return;
        }

        $this->ensurePaymentMethodActiveColumn($conexion);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->submit($conexion);
            return;
        }

        $this->show($conexion);
    }

    private function show(PDO $conexion, string $error = ''): void
    {
        $idEvento = isset($_GET['id_evento']) ? (int)$_GET['id_evento'] : 0;
        $data = $this->buildPaymentData($conexion, $idEvento);
        $this->renderWithLayout('event_payment', $data + ['error' => $error]);
    }

    private function submit(PDO $conexion): void
    {
        $idEvento = isset($_POST['id_evento']) ? (int)$_POST['id_evento'] : 0;
        $data = $this->buildPaymentData($conexion, $idEvento);

        if (empty($data['event']) || empty($data['user']) || empty($data['school'])) {
            $this->renderWithLayout('event_payment', $data + ['error' => 'No se pudo validar el evento para tu escuela.']);
            return;
        }

        $idMetodo = isset($_POST['id_metodo_pago']) ? (int)$_POST['id_metodo_pago'] : 0;
        $method = $this->paymentMethodForSchool($conexion, $idMetodo, (int)$data['school']['id_escuela']);
        if (!$method) {
            $this->renderWithLayout('event_payment', $data + ['error' => 'Selecciona un metodo de pago valido para tu escuela.']);
            return;
        }

        $receiptError = $this->validateReceiptUpload();
        if ($receiptError !== '') {
            $this->renderWithLayout('event_payment', $data + ['error' => $receiptError]);
            return;
        }

        $total = (float)$data['total'];
        if ($total <= 0) {
            $this->renderWithLayout('event_payment', $data + ['error' => 'Este evento no tiene un valor pendiente por pagar.']);
            return;
        }

        try {
            $this->sendReceiptEmail(
                $conexion,
                $data['user'],
                $data['school'],
                $data['event'],
                $method,
                (int)$data['quantity'],
                $total
            );

            $facturaId = $this->insertInvoice(
                $conexion,
                (int)$data['user']['id_usuario'],
                (int)$data['event']['id_evento'],
                isset($data['id_deportista']) ? (int)$data['id_deportista'] : null,
                (int)$method['id_metodo'],
                $total,
                (string)$data['event']['titulo']
            );

            $_SESSION['flash_payment_success'] = 'Comprobante enviado y factura #' . $facturaId . ' registrada correctamente.';
            header('Location: pagos');
            exit();
        } catch (Throwable $e) {
            error_log('Error en pago manual de evento: ' . $e->getMessage());
            $this->renderWithLayout('event_payment', $data + ['error' => 'No se pudo enviar el comprobante. La factura no fue registrada.']);
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function buildPaymentData(PDO $conexion, int $idEvento): array
    {
        $user = $this->currentUser($conexion);
        $school = $user ? $this->schoolById($conexion, (int)($user['id_escuela'] ?? 0)) : null;
        $event = ($user && $school && $idEvento > 0)
            ? $this->eventForSchool($conexion, $idEvento, (int)$school['id_escuela'])
            : null;
        $methods = $school ? $this->paymentMethodsForSchool($conexion, (int)$school['id_escuela']) : [];
        $registration = $event && $user ? $this->registrationSummary($conexion, (int)$event['id_evento'], (int)$user['id_usuario']) : [
            'quantity' => 1,
            'id_deportista' => null,
        ];

        $quantity = max(1, (int)$registration['quantity']);
        $unitCost = $event ? (float)($event['costo'] ?? 0) : 0.0;

        return [
            'user' => $user,
            'school' => $school,
            'event' => $event,
            'methods' => $methods,
            'quantity' => $quantity,
            'id_deportista' => $registration['id_deportista'],
            'total' => $unitCost * $quantity,
        ];
    }

    private function currentUser(PDO $conexion): ?array
    {
        $idUsuario = (int)($_SESSION['id_usuario'] ?? ($_SESSION['usuario']['id_usuario'] ?? 0));
        if ($idUsuario <= 0) {
            return null;
        }

        $stmt = $conexion->prepare('SELECT * FROM usuarios WHERE id_usuario = :id_usuario LIMIT 1');
        $stmt->execute([':id_usuario' => $idUsuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($user) ? $user : null;
    }

    private function schoolById(PDO $conexion, int $schoolId): ?array
    {
        if ($schoolId <= 0) {
            return null;
        }

        $stmt = $conexion->prepare('SELECT * FROM escuelas WHERE id_escuela = :id_escuela LIMIT 1');
        $stmt->execute([':id_escuela' => $schoolId]);
        $school = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($school) ? $school : null;
    }

    private function eventForSchool(PDO $conexion, int $eventId, int $schoolId): ?array
    {
        $stmt = $conexion->prepare("
            SELECT *
            FROM eventos
            WHERE id_evento = :id_evento
              AND id_escuela = :id_escuela
              AND estado = 1
            LIMIT 1
        ");
        $stmt->execute([
            ':id_evento' => $eventId,
            ':id_escuela' => $schoolId,
        ]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($event) ? $event : null;
    }

    private function paymentMethodsForSchool(PDO $conexion, int $schoolId): array
    {
        $stmt = $conexion->prepare("
            SELECT id_metodo, id_escuela, nombre_entidad, qr_path, tipo
            FROM metodos_pago
            WHERE id_escuela = :id_escuela
              AND activo = 1
            ORDER BY id_metodo ASC
        ");
        $stmt->execute([':id_escuela' => $schoolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function paymentMethodForSchool(PDO $conexion, int $methodId, int $schoolId): ?array
    {
        if ($methodId <= 0 || $schoolId <= 0) {
            return null;
        }

        $stmt = $conexion->prepare("
            SELECT id_metodo, id_escuela, nombre_entidad, qr_path, tipo
            FROM metodos_pago
            WHERE id_metodo = :id_metodo
              AND id_escuela = :id_escuela
              AND activo = 1
            LIMIT 1
        ");
        $stmt->execute([
            ':id_metodo' => $methodId,
            ':id_escuela' => $schoolId,
        ]);
        $method = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($method) ? $method : null;
    }

    /**
     * @return array{quantity:int,id_deportista:?int}
     */
    private function registrationSummary(PDO $conexion, int $eventId, int $userId): array
    {
        $stmt = $conexion->prepare("
            SELECT COUNT(DISTINCT i.id_deportista) AS quantity,
                   MIN(i.id_deportista) AS first_athlete
            FROM inscripciones i
            INNER JOIN deportistas d ON d.id_deportista = i.id_deportista
            WHERE i.id_evento = :id_evento
              AND i.id_usuario = :id_usuario
              AND d.id_usuario = :id_usuario
        ");
        $stmt->execute([
            ':id_evento' => $eventId,
            ':id_usuario' => $userId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $quantity = (int)($row['quantity'] ?? 0);

        return [
            'quantity' => max(1, $quantity),
            'id_deportista' => $quantity === 1 ? (int)($row['first_athlete'] ?? 0) : null,
        ];
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

        $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (!in_array($extension, $allowedExtensions, true)) {
            return 'El comprobante debe ser imagen JPG, PNG, WEBP o PDF.';
        }

        return '';
    }

    /**
     * @param array<string,mixed> $user
     * @param array<string,mixed> $school
     * @param array<string,mixed> $event
     * @param array<string,mixed> $method
     */
    private function sendReceiptEmail(PDO $conexion, array $user, array $school, array $event, array $method, int $quantity, float $total): void
    {
        $recipients = $this->schoolAdminRecipients($conexion, (int)$school['id_escuela'], (string)($school['correo'] ?? ''));
        if ($recipients === []) {
            throw new \RuntimeException('No hay destinatario para enviar el comprobante.');
        }

        $smtp = $this->smtpCredentials($school);
        $userName = trim((string)($user['nombres'] ?? '') . ' ' . (string)($user['apellidos'] ?? ''));
        if ($userName === '') {
            $userName = 'Usuario';
        }

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $smtp['email'];
        $mail->Password = $smtp['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom($smtp['email'], $smtp['name']);

        $replyTo = (string)($user['email'] ?? '');
        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo, $userName);
        }

        foreach ($recipients as $recipient) {
            $mail->addAddress($recipient['email'], $recipient['name']);
        }

        $eventTitle = (string)($event['titulo'] ?? 'Evento');
        $methodName = (string)($method['nombre_entidad'] ?? 'Metodo de pago');
        $mail->isHTML(true);
        $mail->Subject = 'Comprobante de pago - ' . $eventTitle;
        $mail->Body = $this->receiptHtmlBody($userName, $school, $eventTitle, $methodName, $quantity, $total);
        $mail->AltBody = $this->receiptTextBody($userName, $school, $eventTitle, $methodName, $quantity, $total);

        $file = $_FILES['comprobante'];
        $mail->addAttachment((string)$file['tmp_name'], $this->safeAttachmentName((string)($file['name'] ?? 'comprobante')));
        $mail->send();
    }

    /**
     * @return array<int,array{email:string,name:string}>
     */
    private function schoolAdminRecipients(PDO $conexion, int $schoolId, string $schoolEmail): array
    {
        $stmt = $conexion->prepare("
            SELECT email, nombres, apellidos
            FROM usuarios
            WHERE id_escuela = :id_escuela
              AND id_rol = 3
              AND email <> ''
        ");
        $stmt->execute([':id_escuela' => $schoolId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $recipients = [];
        $seen = [];

        foreach ($rows as $row) {
            $email = strtolower(trim((string)($row['email'] ?? '')));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || isset($seen[$email])) {
                continue;
            }
            $seen[$email] = true;
            $name = trim((string)($row['nombres'] ?? '') . ' ' . (string)($row['apellidos'] ?? ''));
            $recipients[] = [
                'email' => $email,
                'name' => $name !== '' ? $name : 'Administrador',
            ];
        }

        $fallbackEmail = strtolower(trim($schoolEmail));
        if ($recipients === [] && filter_var($fallbackEmail, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = [
                'email' => $fallbackEmail,
                'name' => 'Administrador',
            ];
        }

        return $recipients;
    }

    /**
     * @param array<string,mixed> $school
     * @return array{email:string,password:string,name:string}
     */
    private function smtpCredentials(array $school): array
    {
        $schoolEmail = trim((string)($school['correo'] ?? ''));
        $schoolPassword = trim((string)($school['pass_app'] ?? ''));
        if (filter_var($schoolEmail, FILTER_VALIDATE_EMAIL) && $schoolPassword !== '') {
            return [
                'email' => $schoolEmail,
                'password' => $schoolPassword,
                'name' => 'Sport Manager',
            ];
        }

        return [
            'email' => 'termostatosolar2022@gmail.com',
            'password' => 'ctgrayjsgmradzeg',
            'name' => 'Sport Manager',
        ];
    }

    /**
     * @param array<string,mixed> $school
     */
    private function receiptHtmlBody(string $userName, array $school, string $eventTitle, string $methodName, int $quantity, float $total): string
    {
        $schoolName = htmlspecialchars((string)($school['nombre'] ?? 'Escuela'), ENT_QUOTES, 'UTF-8');
        $safeUser = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
        $safeEvent = htmlspecialchars($eventTitle, ENT_QUOTES, 'UTF-8');
        $safeMethod = htmlspecialchars($methodName, ENT_QUOTES, 'UTF-8');
        $safeTotal = '$' . number_format($total, 0, ',', '.');

        return "
            <h2>Comprobante de pago recibido</h2>
            <p><strong>Escuela:</strong> {$schoolName}</p>
            <p><strong>Usuario:</strong> {$safeUser}</p>
            <p><strong>Evento:</strong> {$safeEvent}</p>
            <p><strong>Metodo:</strong> {$safeMethod}</p>
            <p><strong>Cantidad:</strong> {$quantity}</p>
            <p><strong>Total:</strong> {$safeTotal}</p>
            <p>El comprobante esta adjunto para revision administrativa.</p>
        ";
    }

    /**
     * @param array<string,mixed> $school
     */
    private function receiptTextBody(string $userName, array $school, string $eventTitle, string $methodName, int $quantity, float $total): string
    {
        return "Comprobante de pago recibido\n"
            . 'Escuela: ' . (string)($school['nombre'] ?? 'Escuela') . "\n"
            . 'Usuario: ' . $userName . "\n"
            . 'Evento: ' . $eventTitle . "\n"
            . 'Metodo: ' . $methodName . "\n"
            . 'Cantidad: ' . $quantity . "\n"
            . 'Total: $' . number_format($total, 0, ',', '.') . "\n";
    }

    private function safeAttachmentName(string $name): string
    {
        $clean = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?? '';
        return $clean !== '' ? $clean : 'comprobante';
    }

    private function insertInvoice(PDO $conexion, int $userId, int $eventId, ?int $athleteId, int $methodId, float $total, string $eventTitle): int
    {
        $reference = $this->manualReference($conexion, $userId, $eventId);

        try {
            $conexion->beginTransaction();
            $nextFacturaId = (int)$conexion->query('SELECT COALESCE(MAX(id_factura), 0) + 1 FROM facturas')->fetchColumn();
            $description = substr('Pago evento: ' . $eventTitle, 0, 100);
            $insert = $conexion->prepare(
                'INSERT INTO facturas (id_factura, id, numero_factura, fecha_emision, tipo_pago, id_deportista, monto, descripcion, id_evento)
                 VALUES (:id_factura, :id_usuario, :numero_factura, :fecha_emision, :tipo_pago, :id_deportista, :monto, :descripcion, :id_evento)'
            );
            $insert->bindValue(':id_factura', $nextFacturaId, PDO::PARAM_INT);
            $insert->bindValue(':id_usuario', $userId, PDO::PARAM_INT);
            $insert->bindValue(':numero_factura', $reference, PDO::PARAM_STR);
            $insert->bindValue(':fecha_emision', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $insert->bindValue(':tipo_pago', $methodId, PDO::PARAM_INT);
            $athleteValue = $athleteId !== null && $athleteId > 0 ? $athleteId : null;
            $insert->bindValue(':id_deportista', $athleteValue, $athleteValue === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $insert->bindValue(':monto', $total);
            $insert->bindValue(':descripcion', $description, PDO::PARAM_STR);
            $insert->bindValue(':id_evento', $eventId, PDO::PARAM_INT);
            $insert->execute();
            $conexion->commit();
            return $nextFacturaId;
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $e;
        }
    }

    private function manualReference(PDO $conexion, int $userId, int $eventId): string
    {
        do {
            $reference = 'EVT-' . date('YmdHis') . '-' . $userId . '-' . $eventId . '-' . random_int(1000, 9999);
            $stmt = $conexion->prepare('SELECT 1 FROM facturas WHERE numero_factura = :reference LIMIT 1');
            $stmt->execute([':reference' => $reference]);
        } while ($stmt->fetchColumn());

        return $reference;
    }

    private function ensurePaymentMethodActiveColumn(PDO $conexion): void
    {
        try {
            $conexion->exec("
                CREATE TABLE IF NOT EXISTS metodos_pago (
                    id_metodo INT(11) NOT NULL,
                    id_escuela INT(11) NOT NULL,
                    nombre_entidad VARCHAR(50) NOT NULL,
                    qr_path VARCHAR(255) DEFAULT NULL,
                    tipo VARCHAR(50) DEFAULT 'offline',
                    activo TINYINT(1) NOT NULL DEFAULT 1,
                    PRIMARY KEY (id_metodo),
                    KEY id_escuela (id_escuela)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");

            $existsStmt = $conexion->query("SHOW COLUMNS FROM metodos_pago LIKE 'activo'");
            $exists = $existsStmt !== false && $existsStmt->fetch(PDO::FETCH_ASSOC) !== false;
            if (!$exists) {
                $conexion->exec("ALTER TABLE metodos_pago ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER tipo");
            }
        } catch (Throwable) {
            // Si la tabla no esta disponible, las consultas posteriores mostraran el error correspondiente.
        }
    }

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

    private function renderStatusError(string $code, string $title, string $message): void
    {
        $backUrl = 'eventos';
        $backLabel = 'Volver a eventos';
        require APP_PATH . '/views/layout/header.php';
        require APP_PATH . '/views/pages/error_status.php';
        require APP_PATH . '/views/layout/footer.php';
    }
}
