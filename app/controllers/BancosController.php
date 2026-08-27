<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PayUService;
use Exception;

<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
if (!defined('APP_BASE_PATH')) {
    require_once dirname(__DIR__) . '/bootstrap.php';
}

<<<<<<< HEAD
=======
=======
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
final class BancosController
{
    private function config(): array
    {
        $file = APP_BASE_PATH . '/config/payu.php';
        if (!is_file($file)) {
            return [];
        }
        $cfg = require $file;
        return is_array($cfg) ? $cfg : [];
    }

    public function pseBanks(): void
    {
        header('Content-Type: application/json');

        $cfg = $this->config();
        $isTest = !empty($cfg['isTest']);
        $testCode = trim((string)($cfg['pseTestBankCode'] ?? ''));
        $testName = trim((string)($cfg['pseTestBankName'] ?? 'Banco de pruebas (Sandbox)'));

        // En sandbox devolver banco de pruebas incluso si el SDK o credenciales fallan.
        if ($isTest) {
            $bankCode = $testCode !== '' ? $testCode : '1022';
            $bankName = $testName !== '' ? $testName : 'Banco de pruebas (Sandbox)';
            echo json_encode([
                'status' => 'success',
                'banks' => [[
                    'pseCode' => $bankCode,
                    'description' => $bankName,
                ]],
            ]);
            return;
        }

        try {
            $payu = new PayUService();
            $payu->boot();
            $banks = $payu->getPseBanks();
            if ($banks === []) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No hay bancos disponibles en este momento.',
                ]);
                return;
            }

            echo json_encode([
                'status' => 'success',
                'banks' => $banks,
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al obtener la lista de bancos.',
                'details' => $e->getMessage(),
            ]);
        }
    }
}
