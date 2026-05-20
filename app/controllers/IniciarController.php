<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use PDO;

final class IniciarController
{
    public function show(): void
    {
        $transaction = null;
        if (!empty($_SESSION['flash_transaction']) && is_array($_SESSION['flash_transaction'])) {
            $transaction = $_SESSION['flash_transaction'];
            unset($_SESSION['flash_transaction']);
        }

        $idEvento = isset($_GET['id_evento']) ? (int)$_GET['id_evento'] : 0;
        $idDeportista = isset($_GET['id_deportista']) ? (int)$_GET['id_deportista'] : 0;
        $eventoTitulo = (string)($_GET['evento'] ?? 'Pago');
        $monto = isset($_GET['monto']) ? (float)$_GET['monto'] : 0.0;
        $cantidad = isset($_GET['cantidad']) ? max(1, (int)$_GET['cantidad']) : 1;
        $idUsuarioSesion = (int)($_SESSION['id_usuario'] ?? ($_SESSION['usuario']['id_usuario'] ?? 0));
        $returnTo = trim((string)($_GET['return_to'] ?? 'index.php?url=iniciar'));
        if ($returnTo === '' || preg_match('/^(https?:)?\/\//i', $returnTo)) {
            $returnTo = 'index.php?url=iniciar';
        }
        $returnTo = ltrim($returnTo, '/');

        // Si hay id_evento, traer siempre datos reales del evento y recalcular
        // cantidad/monto segun los deportistas inscritos del usuario.
        if ($idEvento > 0) {
            require APP_BASE_PATH . '/config/conexion.php';
            if (isset($conexion) && $conexion instanceof PDO) {
                $stmt = $conexion->prepare('SELECT titulo, costo FROM eventos WHERE id_evento = :id_evento LIMIT 1');
                $stmt->execute([':id_evento' => $idEvento]);
                $evento = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($idUsuarioSesion > 0) {
                    $countStmt = $conexion->prepare("
                        SELECT COUNT(DISTINCT i.id_deportista)
                        FROM inscripciones i
                        INNER JOIN deportistas d ON d.id_deportista = i.id_deportista
                        WHERE i.id_evento = :id_evento
                          AND d.id_usuario = :id_usuario
                    ");
                    $countStmt->execute([
                        ':id_evento' => $idEvento,
                        ':id_usuario' => $idUsuarioSesion,
                    ]);
                    $inscritosUsuario = (int)$countStmt->fetchColumn();
                    if ($inscritosUsuario > 0) {
                        $cantidad = $inscritosUsuario;
                    }
                }
                if ($evento) {
                    if (!empty($evento['titulo'])) {
                        $eventoTitulo = (string)$evento['titulo'];
                    }
                    $costoUnitario = (float)($evento['costo'] ?? 0);
                    $monto = $costoUnitario > 0 ? ($costoUnitario * $cantidad) : $monto;
                }
            }
        }

        $usuarioSesion = $_SESSION['usuario'] ?? [];
        $nombreUsuario = trim((string)($usuarioSesion['nombres'] ?? '') . ' ' . (string)($usuarioSesion['apellidos'] ?? ''));
        $emailUsuario = (string)($usuarioSesion['email'] ?? '');
        $telefonoUsuario = (string)($usuarioSesion['telefono'] ?? '');

        if (!isset($_SESSION['registro_temporal']) || !is_array($_SESSION['registro_temporal'])) {
            $_SESSION['registro_temporal'] = [
                'nombre' => $nombreUsuario,
                'email' => $emailUsuario,
                'telefono' => $telefonoUsuario,
                'password' => '',
            ];
        }

        $payuContext = [
            'id_evento' => $idEvento,
            'id_deportista' => $idDeportista > 0 ? $idDeportista : null,
            'evento_titulo' => $eventoTitulo,
            'monto' => $monto,
            'cantidad' => $cantidad,
            'action' => 'index.php?url=procesar_pago',
            'return_to' => $returnTo,
            'error' => $_SESSION['error'] ?? '',
            'prefill' => [
                'nombre' => $nombreUsuario,
            ],
        ];
        unset($_SESSION['error']);

        View::render('iniciar', [
            'transaction' => $transaction,
            'payuContext' => $payuContext,
        ]);
    }
}
