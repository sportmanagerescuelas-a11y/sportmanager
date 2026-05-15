<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use Throwable;

final class PaymentTransactionService
{
    private const SESSION_CONTEXTS_KEY = 'payment_contexts';

    /**
     * @param array<string,mixed> $context
     */
    public function encodeContext(array $context): string
    {
        $json = json_encode($context, JSON_UNESCAPED_UNICODE);
        if (!is_string($json) || $json === '') {
            return '';
        }

        return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    }

    /**
     * @return array<string,mixed>
     */
    public function decodeContext(?string $encoded): array
    {
        if (!is_string($encoded) || trim($encoded) === '') {
            return [];
        }

        $base64 = strtr($encoded, '-_', '+/');
        $padding = strlen($base64) % 4;
        if ($padding > 0) {
            $base64 .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($base64, true);
        if (!is_string($decoded) || $decoded === '') {
            return [];
        }

        $data = json_decode($decoded, true);
        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string,mixed> $context
     */
    public function storeContext(string $referenceCode, array $context): void
    {
        if ($referenceCode === '') {
            return;
        }
        if (!isset($_SESSION[self::SESSION_CONTEXTS_KEY]) || !is_array($_SESSION[self::SESSION_CONTEXTS_KEY])) {
            $_SESSION[self::SESSION_CONTEXTS_KEY] = [];
        }

        $_SESSION[self::SESSION_CONTEXTS_KEY][$referenceCode] = $context;
    }

    /**
     * @return array<string,mixed>
     */
    public function resolveContext(string $referenceCode, ?string $encodedContext = null): array
    {
        $fromUrl = $this->decodeContext($encodedContext);
        if ($fromUrl !== []) {
            return $fromUrl;
        }

        $contexts = $_SESSION[self::SESSION_CONTEXTS_KEY] ?? [];
        if (!is_array($contexts)) {
            return [];
        }

        $ctx = $contexts[$referenceCode] ?? [];
        return is_array($ctx) ? $ctx : [];
    }

    /**
     * @return array{code:int,label:string,tone:string,final:bool,key:string}
     */
    public function normalizeStatus(?int $stateCode, ?string $stateText, ?string $responseCode = null): array
    {
        $normalizedState = strtoupper(trim((string)$stateText));
        $normalizedResponse = strtoupper(trim((string)$responseCode));

        if ($stateCode === null || $stateCode <= 0) {
            if ($normalizedState === 'APPROVED') {
                $stateCode = 4;
            } elseif ($normalizedState === 'PENDING' || str_contains($normalizedState, 'PENDING')) {
                $stateCode = 7;
            } elseif ($normalizedState === 'DECLINED' || $normalizedState === 'FAILED' || $normalizedState === 'REJECTED') {
                $stateCode = 6;
            } elseif ($normalizedState !== '') {
                $stateCode = 104;
            } elseif ($normalizedResponse !== '') {
                $stateCode = 104;
            } else {
                $stateCode = 0;
            }
        }

        $map = [
            4 => ['label' => 'Transaccion aprobada.', 'tone' => 'success', 'final' => true, 'key' => 'approved'],
            6 => ['label' => 'Transaccion rechazada.', 'tone' => 'danger', 'final' => true, 'key' => 'rejected'],
            7 => ['label' => 'Transaccion pendiente.', 'tone' => 'warning', 'final' => false, 'key' => 'pending'],
            104 => ['label' => 'Error de procesamiento.', 'tone' => 'danger', 'final' => true, 'key' => 'error'],
            0 => ['label' => 'Estado de transaccion desconocido.', 'tone' => 'secondary', 'final' => false, 'key' => 'unknown'],
        ];

        $status = $map[$stateCode] ?? $map[0];
        return [
            'code' => $stateCode,
            'label' => $status['label'],
            'tone' => $status['tone'],
            'final' => $status['final'],
            'key' => $status['key'],
        ];
    }

    /**
     * @param array<string,mixed> $raw
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function buildResultFromRaw(array $raw, array $context = []): array
    {
        $stateCode = isset($raw['transactionState']) ? (int)$raw['transactionState'] : null;
        $stateText = isset($raw['state']) ? (string)$raw['state'] : null;
        $responseCode = isset($raw['responseCode']) ? (string)$raw['responseCode'] : null;
        $status = $this->normalizeStatus($stateCode, $stateText, $responseCode);

        $amount = isset($raw['amount']) ? (float)$raw['amount'] : (float)($context['monto'] ?? 0);
        $eventTitle = trim((string)($context['concepto'] ?? $context['evento_titulo'] ?? 'Pago'));
        $paymentMethod = strtoupper(trim((string)($raw['paymentMethod'] ?? $context['metodo_pago'] ?? '')));
        $bank = trim((string)($raw['bank'] ?? $context['bank'] ?? ''));

        return [
            'status' => $status,
            'referenceCode' => (string)($raw['referenceCode'] ?? ''),
            'transactionId' => (string)($raw['transactionId'] ?? ''),
            'responseCode' => $responseCode !== '' ? $responseCode : null,
            'stateText' => $stateText !== '' ? $stateText : null,
            'amount' => $amount,
            'date' => (string)($raw['date'] ?? date('Y-m-d H:i:s')),
            'paymentMethod' => $paymentMethod,
            'bank' => $bank,
            'description' => $eventTitle,
            'supportCode' => (string)($raw['supportCode'] ?? ''),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function fetchLatestByReference(string $referenceCode): array
    {
        $payu = new PayUService();
        $payload = $payu->getOrderDetailByReferenceCode($referenceCode);
        $payloadArray = json_decode(json_encode($payload), true);
        if (!is_array($payloadArray)) {
            return ['referenceCode' => $referenceCode];
        }

        $order = $payloadArray[0] ?? [];
        if (!is_array($order)) {
            $order = [];
        }

        $transactions = $order['transactions'] ?? [];
        if (!is_array($transactions) || $transactions === []) {
            return [
                'referenceCode' => $referenceCode,
                'state' => $order['status'] ?? null,
                'amount' => $this->extractAmount($order),
            ];
        }

        $tx = end($transactions);
        if (!is_array($tx)) {
            $tx = [];
        }

        $txResponse = $tx['transactionResponse'] ?? [];
        if (!is_array($txResponse)) {
            $txResponse = [];
        }
        $extra = $txResponse['extraParameters'] ?? [];
        if (!is_array($extra)) {
            $extra = [];
        }

        $stateValue = $txResponse['state'] ?? ($tx['state'] ?? null);
        $responseCode = $txResponse['responseCode'] ?? ($tx['responseCode'] ?? null);
        $amount = $this->extractAmount($order);
        if ($amount <= 0 && isset($tx['order']) && is_array($tx['order'])) {
            $amount = $this->extractAmount($tx['order']);
        }

        return [
            'referenceCode' => (string)($order['referenceCode'] ?? $referenceCode),
            'transactionId' => (string)($tx['id'] ?? $tx['transactionId'] ?? ''),
            'state' => is_scalar($stateValue) ? (string)$stateValue : null,
            'responseCode' => is_scalar($responseCode) ? (string)$responseCode : null,
            'amount' => $amount,
            'paymentMethod' => (string)($tx['paymentMethod'] ?? ''),
            'bank' => (string)($extra['PSE_BANK'] ?? $extra['BANK_NAME'] ?? ''),
            'date' => (string)($tx['transactionDate'] ?? $tx['operationDate'] ?? date('Y-m-d H:i:s')),
            'supportCode' => (string)($tx['trazabilityCode'] ?? ''),
        ];
    }

    /**
     * @param array<string,mixed> $result
     * @param array<string,mixed> $context
     * @return array{saved:bool,factura_id:?int,message:string}
     */
    public function persistInvoiceIfApproved(array $result, array $context): array
    {
        $status = $result['status']['key'] ?? '';
        if ($status !== 'approved') {
            return ['saved' => false, 'factura_id' => null, 'message' => 'La transaccion no esta aprobada.'];
        }

        $referenceCode = trim((string)($result['referenceCode'] ?? ''));
        if ($referenceCode === '') {
            return ['saved' => false, 'factura_id' => null, 'message' => 'Referencia de pago no disponible.'];
        }

        require APP_BASE_PATH . '/config/conexion.php';
        if (!isset($conexion) || !($conexion instanceof PDO)) {
            return ['saved' => false, 'factura_id' => null, 'message' => 'Conexion de base de datos no disponible.'];
        }

        $existing = $conexion->prepare('SELECT id_factura FROM facturas WHERE numero_factura = :numero LIMIT 1');
        $existing->execute([':numero' => $referenceCode]);
        $existingId = $existing->fetchColumn();
        if ($existingId !== false) {
            return ['saved' => true, 'factura_id' => (int)$existingId, 'message' => 'Factura ya registrada previamente.'];
        }

        $idUsuario = (int)($context['id_usuario'] ?? ($_SESSION['id_usuario'] ?? ($_SESSION['usuario']['id_usuario'] ?? 0)));
        if ($idUsuario <= 0) {
            return ['saved' => false, 'factura_id' => null, 'message' => 'No se pudo identificar el usuario para facturar.'];
        }

        $idEscuela = (int)($context['id_escuela'] ?? ($_SESSION['usuario']['id_escuela'] ?? 1));
        if ($idEscuela <= 0) {
            $idEscuela = 1;
        }

        $idDeportista = isset($context['id_deportista']) ? (int)$context['id_deportista'] : null;
        if ($idDeportista !== null && $idDeportista <= 0) {
            $idDeportista = null;
        }

        $idEvento = isset($context['id_evento']) ? (int)$context['id_evento'] : null;
        if ($idEvento !== null && $idEvento <= 0) {
            $idEvento = null;
        }

        $descripcion = trim((string)($context['concepto'] ?? $result['description'] ?? 'Pago pasarela'));
        if ($descripcion === '') {
            $descripcion = 'Pago pasarela';
        }
        $descripcion = substr($descripcion, 0, 100);

        $amount = (float)($result['amount'] ?? $context['monto'] ?? 0);
        if ($amount < 0) {
            $amount = 0;
        }

        $tipoPagoTexto = strtoupper(trim((string)($context['metodo_pago'] ?? $result['paymentMethod'] ?? 'PAYU')));
        if ($tipoPagoTexto === '') {
            $tipoPagoTexto = 'PAYU';
        }

        try {
            $conexion->beginTransaction();

            $tipoPagoId = $this->resolvePaymentMethodId($conexion, $tipoPagoTexto, $idEscuela);
            $nextFacturaId = (int)$conexion->query('SELECT COALESCE(MAX(id_factura), 0) + 1 FROM facturas')->fetchColumn();
            $now = date('Y-m-d H:i:s');

            $insert = $conexion->prepare(
                'INSERT INTO facturas (id_factura, id, numero_factura, fecha_emision, tipo_pago, id_deportista, monto, descripcion, id_evento)
                 VALUES (:id_factura, :id_usuario, :numero_factura, :fecha_emision, :tipo_pago, :id_deportista, :monto, :descripcion, :id_evento)'
            );
            $insert->bindValue(':id_factura', $nextFacturaId, PDO::PARAM_INT);
            $insert->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $insert->bindValue(':numero_factura', $referenceCode, PDO::PARAM_STR);
            $insert->bindValue(':fecha_emision', $now, PDO::PARAM_STR);
            $insert->bindValue(':tipo_pago', $tipoPagoId, PDO::PARAM_INT);
            $insert->bindValue(':id_deportista', $idDeportista, $idDeportista === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $insert->bindValue(':monto', $amount);
            $insert->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
            $insert->bindValue(':id_evento', $idEvento, $idEvento === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $insert->execute();

            $conexion->commit();
            return ['saved' => true, 'factura_id' => $nextFacturaId, 'message' => 'Factura registrada correctamente.'];
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log('Error guardando factura de pago: ' . $e->getMessage());
            return ['saved' => false, 'factura_id' => null, 'message' => 'No se pudo guardar la factura.'];
        }
    }

    /**
     * @param array<string,mixed> $order
     */
    private function extractAmount(array $order): float
    {
        $additional = $order['additionalValues'] ?? [];
        if (!is_array($additional)) {
            return 0.0;
        }

        $txValue = $additional['TX_VALUE'] ?? [];
        if (is_array($txValue) && isset($txValue['value']) && is_numeric((string)$txValue['value'])) {
            return (float)$txValue['value'];
        }

        if (isset($additional['TX_VALUE.value']) && is_numeric((string)$additional['TX_VALUE.value'])) {
            return (float)$additional['TX_VALUE.value'];
        }

        return 0.0;
    }

    private function resolvePaymentMethodId(PDO $conexion, string $methodName, int $schoolId): int
    {
        $find = $conexion->prepare(
            'SELECT id_metodo FROM metodos_pago
             WHERE UPPER(nombre_entidad) = :name
             ORDER BY CASE WHEN id_escuela = :id_escuela THEN 0 ELSE 1 END, id_metodo ASC
             LIMIT 1'
        );
        $find->execute([
            ':name' => strtoupper($methodName),
            ':id_escuela' => $schoolId,
        ]);
        $id = $find->fetchColumn();
        if ($id !== false) {
            return (int)$id;
        }

        $newId = (int)$conexion->query('SELECT COALESCE(MAX(id_metodo), 0) + 1 FROM metodos_pago')->fetchColumn();
        $insert = $conexion->prepare(
            'INSERT INTO metodos_pago (id_metodo, id_escuela, nombre_entidad, qr_path, tipo)
             VALUES (:id_metodo, :id_escuela, :nombre, NULL, :tipo)'
        );
        $insert->execute([
            ':id_metodo' => $newId,
            ':id_escuela' => $schoolId,
            ':nombre' => strtoupper($methodName),
            ':tipo' => 'online',
        ]);

        return $newId;
    }
}
