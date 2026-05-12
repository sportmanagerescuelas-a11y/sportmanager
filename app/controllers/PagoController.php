<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PayUService;
use Exception;
use PDO;

final class PagoController
{
    private function resolveReturnTo(): string
    {
        $default = 'pagos.php';
        $candidate = trim((string)($_POST['return_to'] ?? $default));
        if ($candidate === '') {
            return $default;
        }
        if (preg_match('/^(https?:)?\/\//i', $candidate)) {
            return $default;
        }
        return ltrim($candidate, '/');
    }

    private function fail(string $message): void
    {
        $_SESSION['error'] = $message;
        header('Location: ' . $this->resolveReturnTo());
        exit();
    }

    public function inscripcion(): void
    {
        if (empty($_POST['cantidad']) || empty($_SESSION['registro_temporal']) || empty($_POST['metodo_pago'])) {
            $this->fail('Datos insuficientes para realizar el pago.');
        }

        $payu = new PayUService();
        $config = $payu->boot();

        $cantidad = (int)$_POST['cantidad'];
        if ($cantidad <= 0) {
            $this->fail('La cantidad debe ser mayor a cero.');
        }

        $direccion = (string)filter_var($_POST['direccion'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $ciudad = (string)filter_var($_POST['ciudad'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $departamento = (string)filter_var($_POST['departamento'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $codigoPostal = (string)filter_var($_POST['codigo_postal'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $pais = strtoupper((string)filter_var($_POST['pais'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $dni = preg_replace('/\D+/', '', (string)($_POST['dni'] ?? '')) ?? '';
        $metodoPago = (string)filter_var($_POST['metodo_pago'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payerPersonType = (string)filter_var($_POST['tipo_persona'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $payerDocumentType = (string)filter_var($_POST['tipo_documento'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $pseBank = isset($_POST['pseBank']) ? (string)filter_var($_POST['pseBank'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        if ($direccion === '' || $ciudad === '' || $departamento === '' || $codigoPostal === '') {
            $this->fail('Debes completar direccion, ciudad, departamento y codigo postal.');
        }
        if (!preg_match('/^[A-Z]{2}$/', $pais)) {
            $this->fail('El pais debe ser una abreviatura ISO2 (ejemplo CO).');
        }
        if (!preg_match('/^\d{10}$/', $dni)) {
            $this->fail('El documento debe tener exactamente 10 digitos numericos.');
        }
        if (!preg_match('/^\d+$/', $codigoPostal)) {
            $this->fail('El codigo postal debe ser numerico.');
        }

        $numeroTarjeta = isset($_POST['numero_tarjeta']) ? (string)filter_var($_POST['numero_tarjeta'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        $cardName = (string)filter_var($_POST['cardName'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $cvvTarjeta = isset($_POST['cardCVV']) ? (string)filter_var($_POST['cardCVV'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        $expiracionMes = isset($_POST['expiracion_mes']) ? (string)filter_var($_POST['expiracion_mes'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        $expiracionAno = isset($_POST['expiracion_ano']) ? (string)filter_var($_POST['expiracion_ano'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        $usuario = $_SESSION['registro_temporal'];
        $email = isset($usuario['email']) ? (string)$usuario['email'] : '';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->fail('El correo es invalido.');
        }

        $usuario['dni'] = $dni;
        $usuario['tipo_documento'] = $payerDocumentType;
        $usuario['cantidad'] = $cantidad;
        $_SESSION['registro_temporal'] = $usuario;

        $idEvento = isset($_POST['id_evento']) ? (int)$_POST['id_evento'] : 0;
        $montoPost = isset($_POST['monto']) ? (float)$_POST['monto'] : 0.0;
        $concepto = trim((string)($_POST['concepto'] ?? 'Pago de inscripcion'));

        // Prioriza siempre el costo real del evento para evitar inconsistencias.
        $total = 0.0;
        if ($idEvento > 0) {
            require APP_BASE_PATH . '/config/conexion.php';
            if (isset($conexion) && $conexion instanceof PDO) {
                $stmtEvento = $conexion->prepare('SELECT titulo, costo FROM eventos WHERE id_evento = :id_evento LIMIT 1');
                $stmtEvento->execute([':id_evento' => $idEvento]);
                $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($evento) {
                    $total = (float)($evento['costo'] ?? 0);
                    if ($concepto === 'Pago de inscripcion' && !empty($evento['titulo'])) {
                        $concepto = (string)$evento['titulo'];
                    }
                }
            }
        }

        if ($total <= 0) {
            $total = $montoPost > 0 ? $montoPost : ($cantidad * 35000);
        }
        if ($total <= 0) {
            $this->fail('El monto a pagar no es valido.');
        }

        $referenceCode = 'PAGO_' . uniqid();
        $signature = md5($config['apiKey'] . '~' . $config['merchantId'] . '~' . $referenceCode . '~' . $total . '~COP');
        $returnUrlBase = (string)$config['returnUrlBase'];

        $parameters = [
            \PayUParameters::ACCOUNT_ID => $config['accountId'],
            \PayUParameters::REFERENCE_CODE => $referenceCode,
            \PayUParameters::DESCRIPTION => $concepto,
            \PayUParameters::VALUE => $total,
            \PayUParameters::CURRENCY => 'COP',
            \PayUParameters::BUYER_EMAIL => $usuario['email'],
            \PayUParameters::BUYER_NAME => $usuario['nombre'],
            \PayUParameters::BUYER_CONTACT_PHONE => $usuario['telefono'],
            \PayUParameters::BUYER_DNI => $dni,
            \PayUParameters::BUYER_STREET => $direccion,
            \PayUParameters::BUYER_CITY => $ciudad,
            \PayUParameters::BUYER_STATE => $departamento,
            \PayUParameters::BUYER_COUNTRY => $pais,
            \PayUParameters::BUYER_POSTAL_CODE => $codigoPostal,
            \PayUParameters::RESPONSE_URL => $returnUrlBase . "/confirmacion-pago-isn?cantidad={$cantidad}",
            \PayUParameters::PAYMENT_METHOD => $metodoPago,
            \PayUParameters::COUNTRY => \PayUCountries::CO,
            \PayUParameters::IP_ADDRESS => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            \PayUParameters::USER_AGENT => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            \PayUParameters::PAYER_COOKIE => session_id(),
            \PayUParameters::PAYER_PERSON_TYPE => $payerPersonType,
            \PayUParameters::PAYER_CONTACT_PHONE => $usuario['telefono'],
            \PayUParameters::PAYER_EMAIL => $usuario['email'],
            \PayUParameters::PAYER_DOCUMENT_TYPE => $payerDocumentType,
            \PayUParameters::PAYER_NAME => $usuario['nombre'],
            \PayUParameters::SIGNATURE => $signature,
        ];

        if ($metodoPago === 'PSE') {
            if (!empty($config['isTest']) && !empty($config['pseTestBankCode'])) {
                $parameters[\PayUParameters::PSE_FINANCIAL_INSTITUTION_CODE] = (string)$config['pseTestBankCode'];
            } else {
                if (empty($pseBank)) {
                    $this->fail('Debe seleccionar un banco para continuar con el pago.');
                }
                $parameters[\PayUParameters::PSE_FINANCIAL_INSTITUTION_CODE] = $pseBank;
            }
        }

        if ($metodoPago === 'MASTERCARD' || $metodoPago === 'VISA') {
            if (empty($numeroTarjeta) || empty($cvvTarjeta) || empty($expiracionMes) || empty($expiracionAno)) {
                $this->fail('Debe proporcionar todos los datos de la tarjeta para continuar con el pago.');
            }
            $parameters[\PayUParameters::CREDIT_CARD_NUMBER] = $numeroTarjeta;
            $parameters[\PayUParameters::CREDIT_CARD_SECURITY_CODE] = $cvvTarjeta;
            $parameters[\PayUParameters::CREDIT_CARD_EXPIRATION_DATE] = "{$expiracionAno}/{$expiracionMes}";
            $parameters[\PayUParameters::INSTALLMENTS_NUMBER] = '1';
            $parameters[\PayUParameters::PAYER_NAME] = $cardName !== '' ? $cardName : $usuario['nombre'];
        }

        try {
            $response = $payu->authorizeAndCapture($parameters);
            if ($response && isset($response->transactionResponse->state)) {
                $transactionState = $response->transactionResponse->state;
                $transactionId = $response->transactionResponse->transactionId ?? null;
                $responseCode = $response->transactionResponse->responseCode ?? null;

                if ($transactionState === 'APPROVED') {
                    $usuarioEncoded = base64_encode(json_encode($usuario));
                    header("Location: {$returnUrlBase}/confirmacion-pago-isn?transactionState=4&referenceCode={$referenceCode}&transactionId={$transactionId}&cantidad={$cantidad}&userData={$usuarioEncoded}");
                    exit();
                }

                if ($transactionState === 'PENDING') {
                    $redirectUrl = $response->transactionResponse->extraParameters->BANK_URL ?? null;
                    if ($redirectUrl) {
                        header("Location: {$redirectUrl}");
                        exit();
                    }
                }

                header("Location: {$returnUrlBase}/confirmacion-pago-isn?transactionState=6&referenceCode={$referenceCode}&responseCode={$responseCode}&cantidad={$cantidad}");
                exit();
            }

            error_log('Respuesta inesperada: ' . print_r($response, true));
            $this->fail('Respuesta inesperada del servidor de pagos.');
        } catch (Exception $e) {
            $msg = $e->getMessage();
            error_log('Excepcion capturada: ' . $msg);
            error_log('Trace: ' . $e->getTraceAsString());
            $this->fail('PayU devolvio un error: ' . $msg);
        }
    }
}
