<?php

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/password.php';

const SM_REGISTER_MAX_RECEIPT_SIZE = 5242880;

function sm_register_redirect_error(string $code, string $debug = ''): void
{
    $url = 'register?error=' . urlencode($code);
    if ($debug !== '') {
        $url .= '&debug=' . urlencode(substr($debug, 0, 220));
    }
    header('Location: ' . $url);
    exit();
}

function sm_register_ensure_payment_structure(PDO $conexion): void
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

        $activeStmt = $conexion->query("SHOW COLUMNS FROM metodos_pago LIKE 'activo'");
        if ($activeStmt === false || $activeStmt->fetch(PDO::FETCH_ASSOC) === false) {
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
        error_log('No se pudo asegurar estructura de pagos de registro: ' . $e->getMessage());
    }
}

function sm_register_payment_method_for_school(PDO $conexion, int $methodId, int $schoolId): ?array
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

function sm_register_receipt_error_code(): string
{
    $file = $_FILES['comprobante'] ?? null;
    if (!is_array($file) || empty($file['tmp_name'])) {
        return 'receipt';
    }

    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || (int)($file['size'] ?? 0) > SM_REGISTER_MAX_RECEIPT_SIZE) {
        return 'receiptsize';
    }
    if ($error !== UPLOAD_ERR_OK) {
        return 'receipt';
    }
    if ((int)($file['size'] ?? 0) <= 0 || !is_uploaded_file((string)$file['tmp_name'])) {
        return 'receipt';
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
        return 'receipttype';
    }

    return '';
}

function sm_register_store_receipt_upload(): string
{
    $file = $_FILES['comprobante'];
    $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    $directory = dirname(__DIR__, 2) . '/storage/payment_receipts';
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('No fue posible crear el directorio de comprobantes.');
    }

    $fileName = 'registro_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $absolutePath = $directory . '/' . $fileName;
    if (!move_uploaded_file((string)$file['tmp_name'], $absolutePath)) {
        throw new RuntimeException('No fue posible almacenar el comprobante.');
    }

    return 'storage/payment_receipts/' . $fileName;
}

function sm_register_delete_receipt(?string $relativePath): void
{
    if (!is_string($relativePath) || $relativePath === '') {
        return;
    }
    $absolutePath = dirname(__DIR__, 2) . '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

function sm_register_school_amount(PDO $conexion, int $schoolId): float
{
    $stmt = $conexion->prepare('SELECT valor_inscripcion FROM escuelas WHERE id_escuela = :id_escuela LIMIT 1');
    $stmt->execute([':id_escuela' => $schoolId]);
    return max(0.0, (float)$stmt->fetchColumn());
}

function sm_register_reference(PDO $conexion, string|int $userId): string
{
    do {
        $reference = 'REG-' . date('YmdHis') . '-' . $userId . '-' . random_int(1000, 9999);
        $stmt = $conexion->prepare('SELECT 1 FROM facturas WHERE numero_factura = :reference LIMIT 1');
        $stmt->execute([':reference' => $reference]);
    } while ($stmt->fetchColumn());

    return $reference;
}

function sm_register_insert_invoice(PDO $conexion, string|int $userId, int $methodId, float $amount, string $receiptPath): int
{
    $nextFacturaId = (int)$conexion->query('SELECT COALESCE(MAX(id_factura), 0) + 1 FROM facturas')->fetchColumn();
    $stmt = $conexion->prepare(
        'INSERT INTO facturas (id_factura, id, numero_factura, fecha_emision, tipo_pago, id_deportista, monto, descripcion, id_evento, cantidad, comprobante_path)
         VALUES (:id_factura, :id_usuario, :numero_factura, :fecha_emision, :tipo_pago, :id_deportista, :monto, :descripcion, :id_evento, :cantidad, :comprobante_path)'
    );
    $stmt->bindValue(':id_factura', $nextFacturaId, PDO::PARAM_INT);
    $stmt->bindValue(':id_usuario', (int)$userId, PDO::PARAM_INT);
    $stmt->bindValue(':numero_factura', sm_register_reference($conexion, $userId), PDO::PARAM_STR);
    $stmt->bindValue(':fecha_emision', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->bindValue(':tipo_pago', $methodId, PDO::PARAM_INT);
    $stmt->bindValue(':id_deportista', null, PDO::PARAM_NULL);
    $stmt->bindValue(':monto', $amount);
    $stmt->bindValue(':descripcion', 'Pago registro de usuario', PDO::PARAM_STR);
    $stmt->bindValue(':id_evento', null, PDO::PARAM_NULL);
    $stmt->bindValue(':cantidad', 1, PDO::PARAM_INT);
    $stmt->bindValue(':comprobante_path', $receiptPath, PDO::PARAM_STR);
    $stmt->execute();

    return $nextFacturaId;
}

function sm_register_approve_user(PDO $conexion, string|int $userId): bool
{
    $stmt = $conexion->prepare("
        UPDATE usuarios
        SET estado = 'aprobado',
            habilitado = 1
        WHERE id_usuario = :id_usuario
          AND id_rol IN (1, 2)
    ");
    return $stmt->execute([':id_usuario' => $userId]);
}

if (isset($_POST["register"])) {

    $id_usuario = filter_var(trim($_POST["id_usuario"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tipo_documento = filter_var(trim($_POST["tipo_documento"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id_escuela = filter_var(trim((string)($_POST["id_escuela"] ?? '')), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $nombres = filter_var(trim($_POST["nombres"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $apellidos = filter_var(trim($_POST["apellidos"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $telefono = filter_var(trim($_POST["telefono"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id_rol = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 1;

    $roles_validos = [1, 2, 3];
    if (!in_array($id_rol, $roles_validos, true)) {
        $id_rol = 1;
    }

    if (empty($id_usuario) || empty($tipo_documento) || empty($nombres) || empty($apellidos) || empty($email) || empty($password) || empty($telefono) || empty($id_rol)) {
        header("Location: register?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register?error=invalidemail");
        exit();
    }

    if (!preg_match('/^\d{1,11}$/', $id_usuario)) {
        header("Location: register?error=empty");
        exit();
    }

    // La columna id_usuario en BD es INT firmado (maximo 2147483647).
    if ((int)$id_usuario > 2147483647) {
        header("Location: register?error=duplicateid");
        exit();
    }

    $usuarioModel = new Usuario($conexion);

    if (!preg_match('/^\d{10}$/', $telefono)) {
        header("Location: register?error=phone");
        exit();
    }

    if (!sm_password_is_valid($password)) {
        header("Location: register?error=password");
        exit();
    }

    $stmtUserId = $conexion->prepare("SELECT 1 FROM usuarios WHERE id_usuario = ? LIMIT 1");
    $stmtUserId->execute([$id_usuario]);
    if ($stmtUserId->fetchColumn()) {
        header("Location: register?error=duplicateid");
        exit();
    }

    $stmtEmail = $conexion->prepare("SELECT 1 FROM usuarios WHERE email = ? LIMIT 1");
    $stmtEmail->execute([$email]);
    if ($stmtEmail->fetchColumn()) {
        header("Location: register?error=duplicateemail");
        exit();
    }

    if ($id_rol !== 3 && !$usuarioModel->escuelaExiste($id_escuela)) {
        header("Location: register?error=school");
        exit();
    }

    if ($id_rol === 3) {
        // El administrador debe existir desde este momento para que el superadmin
        // pueda verlo y validar posteriormente la factura generada por la pasarela.
        $id_escuela = null;
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            header("Location: register?error=db");
            exit();
        }

        if (!$usuarioModel->registrar($id_usuario, $tipo_documento, $id_escuela, $nombres, $apellidos, $email, $password, $telefono, $id_rol)) {
            $debug = $usuarioModel->lastError();
            if ($debug !== '') {
                header("Location: register?error=db&debug=" . urlencode(substr($debug, 0, 220)));
            } else {
                header("Location: register?error=db");
            }
            exit();
        }

        $_SESSION['registro_temporal'] = [
            'id_usuario' => $id_usuario,
            'tipo_documento' => $tipo_documento,
            'id_escuela' => null,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'nombre' => trim($nombres . ' ' . $apellidos),
            'email' => $email,
            'password' => $passwordHash,
            'telefono' => $telefono,
            'id_rol' => 3,
            'cantidad' => 1,
            'tipo_persona' => 'N',
            'flujo' => 'registro_admin',
        ];

        $returnTo = urlencode('iniciar?evento=Pago registro administrador&monto=35000&cantidad=1');
        header("Location: iniciar?evento=Pago%20registro%20administrador&monto=35000&cantidad=1&return_to={$returnTo}");
        exit();
    }

    sm_register_ensure_payment_structure($conexion);

    $id_metodo_pago = isset($_POST['id_metodo_pago']) ? (int)$_POST['id_metodo_pago'] : 0;
    $paymentMethod = sm_register_payment_method_for_school($conexion, $id_metodo_pago, (int)$id_escuela);
    if (!$paymentMethod) {
        sm_register_redirect_error('paymentmethod');
    }

    $receiptError = sm_register_receipt_error_code();
    if ($receiptError !== '') {
        sm_register_redirect_error($receiptError);
    }

    $receiptPath = null;
    try {
        $receiptPath = sm_register_store_receipt_upload();
        $amount = sm_register_school_amount($conexion, (int)$id_escuela);

        $conexion->beginTransaction();
        if (!$usuarioModel->registrar($id_usuario, $tipo_documento, $id_escuela, $nombres, $apellidos, $email, $password, $telefono, $id_rol)) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            sm_register_delete_receipt($receiptPath);
            $debug = $usuarioModel->lastError();
            sm_register_redirect_error('db', $debug);
        }

        if (!sm_register_approve_user($conexion, $id_usuario)) {
            throw new RuntimeException('No se pudo activar el usuario registrado.');
        }

        sm_register_insert_invoice($conexion, $id_usuario, (int)$paymentMethod['id_metodo'], $amount, $receiptPath);
        $conexion->commit();

        header("Location: register?success=payment_registered");
        exit();
    } catch (Throwable $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        sm_register_delete_receipt($receiptPath);
        error_log('Error registrando usuario con comprobante: ' . $e->getMessage());
        sm_register_redirect_error('db', $e->getMessage());
    }
}

header("Location: register");
exit();
