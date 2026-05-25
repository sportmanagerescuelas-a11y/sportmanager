<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../config/conexion.php';

$documentRaw = isset($_POST['id_usuario']) ? (string)$_POST['id_usuario'] : '';
$document = trim($documentRaw);

if ($document === '') {
    echo json_encode([
        'ok' => false,
        'valid' => false,
        'exists' => false,
        'message' => 'Ingresa un numero de documento.',
    ]);
    exit;
}

if (!preg_match('/^\d+$/', $document)) {
    echo json_encode([
        'ok' => true,
        'valid' => false,
        'exists' => false,
        'message' => 'El documento solo debe contener numeros.',
    ]);
    exit;
}

try {
    $stmt = $conexion->prepare('SELECT 1 FROM usuarios WHERE id_usuario = ? LIMIT 1');
    $stmt->execute([$document]);
    $exists = (bool)$stmt->fetchColumn();

    echo json_encode([
        'ok' => true,
        'valid' => true,
        'exists' => $exists,
        'message' => $exists ? 'Este numero de documento ya esta registrado.' : 'Documento disponible.',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'valid' => true,
        'exists' => false,
        'message' => 'No se pudo validar el documento en este momento.',
    ]);
}

