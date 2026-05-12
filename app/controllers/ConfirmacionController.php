<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

final class ConfirmacionController
{
    /**
     * @return array{code:int,label:string}
     */
    private function estadoLabel(int $estadoPago): array
    {
        $map = [
            4 => 'Transacción aprobada.',
            6 => 'Transacción rechazada.',
            7 => 'Transacción pendiente.',
            104 => 'Error de procesamiento.',
        ];
        return [
            'code' => $estadoPago,
            'label' => $map[$estadoPago] ?? 'Estado de transacción desconocido.',
        ];
    }

    public function pagoIsn(): void
    {
        $data = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;

        if (!isset($data['transactionState'], $data['referenceCode'], $data['cantidad'])) {
            $_SESSION['error'] = 'Datos incompletos recibidos de PayU.';
            header('Location: ' . BASE_PATH . '/registro');
            exit();
        }

        $estadoPago = (int)$data['transactionState'];
        $cantidad = (int)$data['cantidad'];
        $referenceCode = (string)$data['referenceCode'];
        $transactionId = isset($data['transactionId']) ? (string)$data['transactionId'] : null;
        $responseCode = isset($data['responseCode']) ? (string)$data['responseCode'] : null;

        if ($cantidad <= 0) {
            die('La cantidad de registros no es válida.');
        }

        $usuario = null;
        if (isset($data['userData'])) {
            $decoded = json_decode((string)base64_decode((string)$data['userData']), true);
            if (is_array($decoded)) {
                $usuario = $decoded;
            }
        }
        if (!$usuario && isset($_SESSION['registro_temporal']) && is_array($_SESSION['registro_temporal'])) {
            $usuario = $_SESSION['registro_temporal'];
        }

        $estadoInfo = $this->estadoLabel($estadoPago);
        $_SESSION['flash_transaction'] = [
            'estado' => $estadoInfo,
            'referenceCode' => $referenceCode,
            'transactionId' => $transactionId,
            'responseCode' => $responseCode,
            'cantidad' => $cantidad,
        ];

        if ($estadoPago === 4) {
            if ($usuario) {
                $usuario['cantidad'] = $cantidad;
                if (User::create($usuario)) {
                    unset($_SESSION['registro_temporal']);
                    $_SESSION['flash_transaction']['userCreated'] = true;
                } else {
                    $_SESSION['flash_transaction']['userCreated'] = false;
                    $_SESSION['flash_transaction']['error'] = 'No se pudo crear el usuario en la base de datos.';
                }
            } else {
                $_SESSION['flash_transaction']['userCreated'] = false;
                $_SESSION['flash_transaction']['error'] = 'No se encontró información del usuario para completar el registro.';
            }
        }

        // Siempre mostrar el resultado de la transacción en /iniciar.
        header('Location: ' . BASE_PATH . '/iniciar');
        exit();
    }
}
