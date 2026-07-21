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

        $this->ensurePaymentStructure($conexion);

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
        $this->renderWithLayout('event_payment', $data + [
            'error' => $error,
            'paymentToken' => $this->paymentToken($idEvento),
        ]);
    }

    private function submit(PDO $conexion): void
    {
        $idEvento = isset($_POST['id_evento']) ? (int)$_POST['id_evento'] : 0;
        $data = $this->buildPaymentData($conexion, $idEvento);

        if (empty($data['event']) || empty($data['user']) || empty($data['school'])) {
            $this->renderPaymentError($data, 'No se pudo validar el evento para tu escuela.');
            return;
        }

        if (!$this->validPaymentToken($idEvento, (string)($_POST['payment_token'] ?? ''))) {
            $this->renderPaymentError($data, 'La sesión del formulario venció. Recarga la página e intenta nuevamente.');
            return;
        }

        $idMetodo = isset($_POST['id_metodo_pago']) ? (int)$_POST['id_metodo_pago'] : 0;
        $method = $this->paymentMethodForSchool($conexion, $idMetodo, (int)$data['school']['id_escuela']);
        if (!$method) {
            $this->renderPaymentError($data, 'Selecciona un método de pago válido para tu escuela.');
            return;
        }

        $receiptError = $this->validateReceiptUpload();
        if ($receiptError !== '') {
            $this->renderPaymentError($data, $receiptError);
            return;
        }

        $total = (float)$data['total'];
        if ($total <= 0) {
            $message = (int)($data['registeredQuantity'] ?? 0) <= 0
                ? 'Primero debes inscribir al menos un deportista en este evento.'
                : 'No tienes valores pendientes por pagar en este evento.';
            $this->renderPaymentError($data, $message);
            return;
        }

        $receiptPath = null;
        try {
            $receiptPath = $this->storeReceiptUpload();

            $facturaId = $this->insertInvoice(
                $conexion,
                (int)$data['user']['id_usuario'],
                (int)$data['event']['id_evento'],
                isset($data['id_deportista']) ? (int)$data['id_deportista'] : null,
                (int)$method['id_metodo'],
                $total,
                (string)$data['event']['titulo'],
                (int)$data['quantity'],
                $receiptPath
            );

            unset($_SESSION['event_payment_tokens'][$idEvento]);
            $notificationSent = true;
            try {
                $this->sendReceiptEmail(
                    $conexion,
                    $data['user'],
                    $data['school'],
                    $data['event'],
                    $method,
                    (int)$data['quantity'],
                    $total,
                    $this->absoluteReceiptPath($receiptPath),
                    (string)($_FILES['comprobante']['name'] ?? 'comprobante')
                );
            } catch (Throwable $notificationError) {
                $notificationSent = false;
                error_log('Factura registrada, pero no se pudo enviar la notificación: ' . $notificationError->getMessage());
            }

            $_SESSION['flash_payment_success'] = 'Comprobante recibido y factura #' . $facturaId . ' registrada correctamente.';
            if (!$notificationSent) {
                $_SESSION['flash_payment_notice'] = 'La factura quedó guardada, pero no fue posible enviar la notificación por correo.';
            }
            header('Location: pagos');
            exit();
        } catch (Throwable $e) {
            if (is_string($receiptPath) && $receiptPath !== '') {
                $absolutePath = $this->absoluteReceiptPath($receiptPath);
                if (is_file($absolutePath)) {
                    @unlink($absolutePath);
                }
            }
            error_log('Error en pago manual de evento: ' . $e->getMessage());
            $this->renderPaymentError($data, 'No se pudo guardar el comprobante ni registrar la factura. Intenta nuevamente.');
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
            'quantity' => 0,
            'id_deportista' => null,
        ];

        $registeredQuantity = max(0, (int)$registration['quantity']);
        $paidQuantity = ($event && $user)
            ? $this->paidQuantity($conexion, (int)$event['id_evento'], (int)$user['id_usuario'])
            : 0;
        $quantity = max(0, $registeredQuantity - $paidQuantity);
        $unitCost = $event ? (float)($event['costo'] ?? 0) : 0.0;

        return [
            'user' => $user,
            'school' => $school,
            'event' => $event,
            'methods' => $methods,
            'quantity' => $quantity,
            'registeredQuantity' => $registeredQuantity,
            'paidQuantity' => $paidQuantity,
            'id_deportista' => $registration['id_deportista'],
            'unitCost' => $unitCost,
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
            'quantity' => $quantity,
            'id_deportista' => $quantity === 1 ? (int)($row['first_athlete'] ?? 0) : null,
        ];
    }

    private function paidQuantity(PDO $conexion, int $eventId, int $userId): int
    {
        $stmt = $conexion->prepare(
            'SELECT COALESCE(SUM(cantidad), 0) FROM facturas WHERE id_evento = :id_evento AND id = :id_usuario'
        );
        $stmt->execute([
            ':id_evento' => $eventId,
            ':id_usuario' => $userId,
        ]);
        return max(0, (int)$stmt->fetchColumn());
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
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);
        if (!isset($allowedMimeTypes[$extension]) || !is_string($mimeType) || !in_array($mimeType, $allowedMimeTypes[$extension], true)) {
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
    private function sendReceiptEmail(PDO $conexion, array $user, array $school, array $event, array $method, int $quantity, float $total, string $receiptPath, string $originalName): void
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
        $mail->Timeout = 8;
        $mail->setFrom($smtp['email'] !== '' ? $smtp['email'] : 'no-reply@sportmanager.local', $smtp['name'] !== '' ? $smtp['name'] : 'Sport Manager');
        $logoPath = APP_BASE_PATH . '/assets/img/balonfutbol.png';
        if (is_file($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'sportmanager-logo', 'balonfutbol.png');
        }
        if ($smtp['email'] !== '' && $smtp['password'] !== '') {
            $mail->isSMTP();
            $mail->Host = $smtp['host'] !== '' ? $smtp['host'] : 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $smtp['email'];
            $mail->Password = $smtp['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)($smtp['port'] ?? 587);
        } else {
            $mail->isMail();
        }

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
        $mail->Body = $this->receiptHtmlBodyV2($userName, $school, $eventTitle, $methodName, $quantity, $total);
        $mail->AltBody = $this->receiptTextBodyV2($userName, $school, $eventTitle, $methodName, $quantity, $total);

        $mail->addAttachment($receiptPath, $this->safeAttachmentName($originalName));
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
        $envHost = trim((string)(getenv('MAIL_HOST') ?: ''));
        $envEmail = trim((string)(getenv('MAIL_USERNAME') ?: (getenv('MAIL_FROM_ADDRESS') ?: '')));
        $envPassword = trim((string)(getenv('MAIL_PASSWORD') ?: ''));
        $envName = trim((string)(getenv('MAIL_FROM_NAME') ?: 'Sport Manager'));
        $envPort = (int)(getenv('MAIL_PORT') ?: 587);
        if (filter_var($schoolEmail, FILTER_VALIDATE_EMAIL) && $schoolPassword !== '') {
            return [
                'host' => $envHost !== '' ? $envHost : 'smtp.gmail.com',
                'email' => $schoolEmail,
                'password' => $schoolPassword,
                'name' => $envName !== '' ? $envName : 'Sport Manager',
                'port' => $envPort,
            ];
        }

        return [
            'host' => $envHost,
            'email' => $envEmail,
            'password' => $envPassword,
            'name' => $envName !== '' ? $envName : 'Sport Manager',
            'port' => $envPort,
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
    private function receiptHtmlBodyV2(string $userName, array $school, string $eventTitle, string $methodName, int $quantity, float $total): string
    {
        $schoolName = htmlspecialchars((string)($school['nombre'] ?? 'Escuela'), ENT_QUOTES, 'UTF-8');
        $safeUser = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
        $safeEvent = htmlspecialchars($eventTitle, ENT_QUOTES, 'UTF-8');
        $safeMethod = htmlspecialchars($methodName, ENT_QUOTES, 'UTF-8');
        $safeTotal = '$' . number_format($total, 0, ',', '.');

        return <<<HTML
<!doctype html>
<html lang="es">
<body style="margin:0;padding:0;background:#eef4fa;font-family:Arial,Helvetica,sans-serif;color:#102a43;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(180deg,#0f2340 0%,#17334e 42%,#eef4fa 42.1%,#eef4fa 100%);padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;background:#ffffff;border-radius:22px;overflow:hidden;box-shadow:0 18px 40px rgba(8,18,31,.16);">
          <tr>
            <td style="background:linear-gradient(135deg,#07111f 0%,#16314c 55%,#2f7fbd 100%);padding:28px 28px 22px;text-align:center;">
              <img src="cid:sportmanager-logo" alt="Sport Manager" width="72" height="72" style="display:block;margin:0 auto 14px;border-radius:18px;background:#fff;padding:10px;object-fit:contain;">
              <div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.78);font-weight:700;">Sport Manager</div>
              <div style="margin-top:6px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.7);">Gestión deportiva</div>
              <h1 style="margin:10px 0 0;font-size:28px;line-height:1.1;color:#fff;">Comprobante de pago recibido</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:30px 30px 14px;text-align:center;">
              <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#334155;">
                Hemos recibido y registrado el comprobante asociado a tu pago.
              </p>
              <div style="border:1px solid #e2e8f0;border-radius:16px;padding:18px 18px 8px;background:#f8fbff;text-align:left;">
                <p style="margin:0 0 10px;"><strong>Escuela:</strong> {$schoolName}</p>
                <p style="margin:0 0 10px;"><strong>Usuario:</strong> {$safeUser}</p>
                <p style="margin:0 0 10px;"><strong>Evento:</strong> {$safeEvent}</p>
                <p style="margin:0 0 10px;"><strong>Método:</strong> {$safeMethod}</p>
                <p style="margin:0 0 10px;"><strong>Cantidad:</strong> {$quantity}</p>
                <p style="margin:0;"><strong>Total:</strong> {$safeTotal}</p>
              </div>
              <p style="margin:18px 0 0;font-size:14px;line-height:1.6;color:#64748b;">
                El comprobante fue adjuntado para revisión administrativa.
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:0 30px 28px;text-align:center;">
              <div style="height:1px;background:#e2e8f0;margin:8px 0 18px;"></div>
              <p style="margin:0;font-size:12px;line-height:1.6;color:#94a3b8;">
                Sport Manager · Gestión deportiva
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    /**
     * @param array<string,mixed> $school
     */
    private function receiptTextBodyV2(string $userName, array $school, string $eventTitle, string $methodName, int $quantity, float $total): string
    {
        return "Sport Manager - Gestión deportiva\n"
            . "Comprobante de pago recibido\n"
            . "Escuela: " . (string)($school['nombre'] ?? 'Escuela') . "\n"
            . "Usuario: " . $userName . "\n"
            . "Evento: " . $eventTitle . "\n"
            . "Método: " . $methodName . "\n"
            . "Cantidad: " . $quantity . "\n"
            . "Total: $" . number_format($total, 0, ',', '.') . "\n"
            . "El comprobante fue adjuntado para revisión administrativa.\n";
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

    private function insertInvoice(PDO $conexion, int $userId, int $eventId, ?int $athleteId, int $methodId, float $total, string $eventTitle, int $quantity, string $receiptPath): int
    {
        $reference = $this->manualReference($conexion, $userId, $eventId);

        try {
            $conexion->beginTransaction();
            $nextFacturaId = (int)$conexion->query('SELECT COALESCE(MAX(id_factura), 0) + 1 FROM facturas')->fetchColumn();
            $description = substr('Pago evento: ' . $eventTitle, 0, 100);
            $insert = $conexion->prepare(
                'INSERT INTO facturas (id_factura, id, numero_factura, fecha_emision, tipo_pago, id_deportista, monto, descripcion, id_evento, cantidad, comprobante_path)
                 VALUES (:id_factura, :id_usuario, :numero_factura, :fecha_emision, :tipo_pago, :id_deportista, :monto, :descripcion, :id_evento, :cantidad, :comprobante_path)'
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
            $insert->bindValue(':cantidad', max(1, $quantity), PDO::PARAM_INT);
            $insert->bindValue(':comprobante_path', $receiptPath, PDO::PARAM_STR);
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

    private function ensurePaymentStructure(PDO $conexion): void
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

            $quantityStmt = $conexion->query("SHOW COLUMNS FROM facturas LIKE 'cantidad'");
            if ($quantityStmt === false || $quantityStmt->fetch(PDO::FETCH_ASSOC) === false) {
                $conexion->exec('ALTER TABLE facturas ADD COLUMN cantidad INT(11) NOT NULL DEFAULT 1 AFTER id_evento');
            }

            $receiptStmt = $conexion->query("SHOW COLUMNS FROM facturas LIKE 'comprobante_path'");
            if ($receiptStmt === false || $receiptStmt->fetch(PDO::FETCH_ASSOC) === false) {
                $conexion->exec('ALTER TABLE facturas ADD COLUMN comprobante_path VARCHAR(255) NULL AFTER cantidad');
            }
        } catch (Throwable $e) {
            // Si la tabla no esta disponible, las consultas posteriores mostraran el error correspondiente.
        }
    }

    /** @param array<string,mixed> $data */
    private function renderPaymentError(array $data, string $message): void
    {
        $eventId = (int)($data['event']['id_evento'] ?? 0);
        $this->renderWithLayout('event_payment', $data + [
            'error' => $message,
            'paymentToken' => $this->paymentToken($eventId),
        ]);
    }

    private function paymentToken(int $eventId): string
    {
        if ($eventId <= 0) {
            return '';
        }
        $tokens = is_array($_SESSION['event_payment_tokens'] ?? null) ? $_SESSION['event_payment_tokens'] : [];
        if (!isset($tokens[$eventId]) || !is_string($tokens[$eventId]) || strlen($tokens[$eventId]) < 32) {
            $tokens[$eventId] = bin2hex(random_bytes(32));
            $_SESSION['event_payment_tokens'] = $tokens;
        }
        return $tokens[$eventId];
    }

    private function validPaymentToken(int $eventId, string $submitted): bool
    {
        $expected = $_SESSION['event_payment_tokens'][$eventId] ?? '';
        return $eventId > 0 && is_string($expected) && $expected !== '' && hash_equals($expected, $submitted);
    }

    private function storeReceiptUpload(): string
    {
        $file = $_FILES['comprobante'];
        $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        $directory = APP_BASE_PATH . '/storage/payment_receipts';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('No fue posible crear el directorio de comprobantes.');
        }

        $fileName = 'comprobante_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $absolutePath = $directory . '/' . $fileName;
        if (!move_uploaded_file((string)$file['tmp_name'], $absolutePath)) {
            throw new \RuntimeException('No fue posible almacenar el comprobante.');
        }

        return 'storage/payment_receipts/' . $fileName;
    }

    private function absoluteReceiptPath(string $relativePath): string
    {
        return APP_BASE_PATH . '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
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
