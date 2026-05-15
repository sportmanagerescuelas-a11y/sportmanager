<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\User;
use App\Services\PaymentTransactionService;
use Throwable;

final class ConfirmacionController
{
    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function buildRawFromGatewayData(array $data): array
    {
        return [
            'transactionState' => isset($data['transactionState']) ? (int)$data['transactionState'] : null,
            'state' => isset($data['state_pol']) ? (string)$data['state_pol'] : (isset($data['state']) ? (string)$data['state'] : null),
            'referenceCode' => (string)($data['referenceCode'] ?? ''),
            'transactionId' => (string)($data['transactionId'] ?? ''),
            'responseCode' => (string)($data['lapResponseCode'] ?? ($data['responseCode'] ?? '')),
            'paymentMethod' => (string)($data['lapPaymentMethod'] ?? ($data['payment_method'] ?? '')),
            'amount' => isset($data['TX_VALUE']) ? (float)$data['TX_VALUE'] : (isset($data['value']) ? (float)$data['value'] : 0),
            'bank' => (string)($data['pseBank'] ?? ($data['bank'] ?? '')),
            'date' => (string)($data['processingDate'] ?? ($data['transactionDate'] ?? date('Y-m-d H:i:s'))),
            'supportCode' => (string)($data['trazabilityCode'] ?? ($data['cus'] ?? '')),
        ];
    }

    /**
     * @param array<string,mixed> $result
     * @return array<int,array{label:string,value:string}>
     */
    private function buildDetailRows(array $result): array
    {
        $rows = [];
        $rows[] = ['label' => 'Referencia', 'value' => (string)($result['referenceCode'] ?? 'N/A')];
        if (!empty($result['transactionId'])) {
            $rows[] = ['label' => 'ID transaccion', 'value' => (string)$result['transactionId']];
        }
        $rows[] = ['label' => 'Fecha', 'value' => (string)($result['date'] ?? date('Y-m-d H:i:s'))];
        $rows[] = ['label' => 'Descripcion del pago', 'value' => (string)($result['description'] ?? 'Pago')];
        $rows[] = ['label' => 'Valor del pago', 'value' => '$' . number_format((float)($result['amount'] ?? 0), 0, ',', '.')];
        if (!empty($result['paymentMethod'])) {
            $rows[] = ['label' => 'Metodo de pago', 'value' => (string)$result['paymentMethod']];
        }
        if (!empty($result['bank'])) {
            $rows[] = ['label' => 'Banco', 'value' => (string)$result['bank']];
        }
        if (!empty($result['responseCode'])) {
            $rows[] = ['label' => 'Codigo de respuesta', 'value' => (string)$result['responseCode']];
        }
        if (!empty($result['supportCode'])) {
            $rows[] = ['label' => 'Codigo de seguimiento', 'value' => (string)$result['supportCode']];
        }

        return $rows;
    }

    /**
     * @param array<string,mixed> $result
     */
    private function maybeCreateUserFromLegacyFlow(array $result): void
    {
        $statusKey = (string)($result['status']['key'] ?? '');
        if ($statusKey !== 'approved') {
            return;
        }

        $hasLoggedUser = isset($_SESSION['id_usuario']) && (int)$_SESSION['id_usuario'] > 0;
        if ($hasLoggedUser) {
            return;
        }

        if (!isset($_SESSION['registro_temporal']) || !is_array($_SESSION['registro_temporal'])) {
            return;
        }

        $usuario = $_SESSION['registro_temporal'];
        if (User::create($usuario)) {
            unset($_SESSION['registro_temporal']);
        }
    }

    public function pagoIsn(): void
    {
        $paymentFlow = new PaymentTransactionService();
        $data = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;
        $referenceCode = trim((string)($data['referenceCode'] ?? ''));
        $encodedContext = (string)($data['ctx'] ?? '');
        $context = $paymentFlow->resolveContext($referenceCode, $encodedContext);
        $checkLatest = isset($data['check']) && (string)$data['check'] === '1';
        $errorMessage = '';
        $invoiceResult = ['saved' => false, 'factura_id' => null, 'message' => ''];

        if ($referenceCode === '' && isset($context['referenceCode'])) {
            $referenceCode = (string)$context['referenceCode'];
        }

        if ($referenceCode === '') {
            $result = $paymentFlow->buildResultFromRaw([
                'transactionState' => 104,
                'referenceCode' => '',
                'responseCode' => 'MISSING_REFERENCE',
                'date' => date('Y-m-d H:i:s'),
                'description' => (string)($context['concepto'] ?? 'Pago'),
                'amount' => (float)($context['monto'] ?? 0),
            ], $context);
            $errorMessage = 'No se recibio una referencia de pago valida.';
            View::render('payment_result', [
                'paymentResult' => $result,
                'paymentDetails' => $this->buildDetailRows($result),
                'paymentError' => $errorMessage,
                'invoiceResult' => $invoiceResult,
                'refreshUrl' => '',
            ]);
            return;
        }

        if ($checkLatest) {
            try {
                $latestRaw = $paymentFlow->fetchLatestByReference($referenceCode);
                $result = $paymentFlow->buildResultFromRaw($latestRaw, $context);
            } catch (Throwable $e) {
                $errorMessage = 'No fue posible consultar el estado actualizado. Intenta nuevamente en unos minutos.';
                $fallbackRaw = $this->buildRawFromGatewayData($data);
                if ($fallbackRaw['referenceCode'] === '') {
                    $fallbackRaw['referenceCode'] = $referenceCode;
                }
                $result = $paymentFlow->buildResultFromRaw($fallbackRaw, $context);
            }
        } else {
            $raw = $this->buildRawFromGatewayData($data);
            if ($raw['referenceCode'] === '') {
                $raw['referenceCode'] = $referenceCode;
            }
            $result = $paymentFlow->buildResultFromRaw($raw, $context);
        }

        if (($result['status']['key'] ?? '') === 'approved') {
            $invoiceResult = $paymentFlow->persistInvoiceIfApproved($result, $context);
        }

        $this->maybeCreateUserFromLegacyFlow($result);

        $refreshUrl = '';
        if (($result['status']['key'] ?? '') === 'pending') {
            $refreshUrl = 'index.php?url=confirmacion-pago-isn&check=1&referenceCode='
                . urlencode($referenceCode)
                . ($encodedContext !== '' ? ('&ctx=' . urlencode($encodedContext)) : '');
        }

        $retryParams = ['url' => 'iniciar'];
        if (!empty($context['id_evento'])) {
            $retryParams['id_evento'] = (string)$context['id_evento'];
        }
        if (!empty($context['id_deportista'])) {
            $retryParams['id_deportista'] = (string)$context['id_deportista'];
        }
        if (!empty($context['concepto'])) {
            $retryParams['evento'] = (string)$context['concepto'];
        }
        if (!empty($context['monto'])) {
            $retryParams['monto'] = (string)$context['monto'];
        }
        if (!empty($context['cantidad'])) {
            $retryParams['cantidad'] = (string)$context['cantidad'];
        }
        $retryUrl = 'index.php?' . http_build_query($retryParams, '', '&', PHP_QUERY_RFC3986);

        View::render('payment_result', [
            'paymentResult' => $result,
            'paymentDetails' => $this->buildDetailRows($result),
            'paymentError' => $errorMessage,
            'invoiceResult' => $invoiceResult,
            'refreshUrl' => $refreshUrl,
            'retryUrl' => $retryUrl,
        ]);
    }
}
