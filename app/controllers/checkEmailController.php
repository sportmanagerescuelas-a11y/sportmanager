<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../config/conexion.php';

$emailRaw = isset($_POST['email']) ? (string)$_POST['email'] : '';
$email = trim(filter_var($emailRaw, FILTER_SANITIZE_EMAIL));

if ($email === '') {
    echo json_encode([
        'ok' => false,
        'valid' => false,
        'exists' => false,
        'message' => 'Ingresa un correo electronico.',
    ]);
    exit;
}

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    echo json_encode([
        'ok' => true,
        'valid' => false,
        'exists' => false,
        'message' => 'El formato del correo no es valido.',
    ]);
    exit;
}

try {
    $stmt = $conexion->prepare('SELECT 1 FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $exists = (bool)$stmt->fetchColumn();

    echo json_encode([
        'ok' => true,
        'valid' => true,
        'exists' => $exists,
        'message' => $exists ? 'Este correo ya esta registrado.' : 'Correo disponible.',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'valid' => true,
        'exists' => false,
        'message' => 'No se pudo validar el correo en este momento.',
    ]);
}

