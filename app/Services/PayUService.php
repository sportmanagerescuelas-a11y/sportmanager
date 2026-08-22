<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use RuntimeException;

final class PayUService
{
    /**
     * @return array{apiKey:string,apiLogin:string,merchantId:string,accountId:string,returnUrlBase:string}
     */
    private function config(): array
    {
        $configFile = APP_BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'payu.php';
        if (!is_file($configFile)) {
            throw new RuntimeException('No se encontro config/payu.php.');
        }
        $cfg = require $configFile;
        if (!is_array($cfg)) {
            throw new RuntimeException('config/payu.php debe retornar un array.');
        }

        return $cfg;
    }

    private function ensureSdkLoaded(): void
    {
        $vendorAutoload = APP_BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (is_file($vendorAutoload)) {
            require_once $vendorAutoload;
        }

        if (class_exists('PayU', false)) {
            return;
        }

        $sdkCandidates = [
            APP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'payu-php-sdk' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'PayU.php',
            APP_BASE_PATH . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'payu-php-sdk' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'PayU.php',
        ];

        $sdk = null;
        foreach ($sdkCandidates as $candidate) {
            if (is_file($candidate)) {
                $sdk = $candidate;
                break;
            }
        }

        if ($sdk === null) {
            throw new RuntimeException('No se encontro el SDK de PayU en lib/ o libs/.');
        }

        require_once $sdk;
    }

    /**
     * @return array{apiKey:string,apiLogin:string,merchantId:string,accountId:string,returnUrlBase:string}
     */
    private function init(): array
    {
        $this->ensureSdkLoaded();
        $config = $this->config();

        \Environment::setPaymentsCustomUrl('https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi');
        \Environment::setReportsCustomUrl('https://sandbox.api.payulatam.com/reports-api/4.0/service.cgi');

        \PayU::$apiKey = $config['apiKey'];
        \PayU::$apiLogin = $config['apiLogin'];
        \PayU::$merchantId = $config['merchantId'];
        \PayU::$language = \SupportedLanguages::ES;
        \PayU::$isTest = (bool)($config['isTest'] ?? true);

        return $config;
    }

    /**
     * Carga el SDK y configura credenciales/entorno.
     *
     * @return array{apiKey:string,apiLogin:string,merchantId:string,accountId:string,returnUrlBase:string}
     */
    public function boot(): array
    {
        return $this->init();
    }

    /**
     * @return array<int,array{pseCode:mixed,description:mixed}>
     */
    public function getPseBanks(): array
    {
        $this->init();

        $parameters = [
            \PayUParameters::PAYMENT_METHOD => 'PSE',
            \PayUParameters::COUNTRY => \PayUCountries::CO,
        ];

        $response = \PayUPayments::getPSEBanks($parameters);
        if (!isset($response->banks) || !is_array($response->banks)) {
            return [];
        }

        return array_map(
            static function ($bank): array {
                return [
                    'pseCode' => $bank->pseCode ?? null,
                    'description' => $bank->description ?? null,
                ];
            },
            $response->banks
        );
    }

    /**
     * @param array<string,mixed> $parameters
     * @return mixed
     */
    public function authorizeAndCapture(array $parameters)
    {
        $this->init();

        try {
            return \PayUPayments::doAuthorizationAndCapture($parameters);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Consulta el estado de una orden por referencia.
     *
     * @return mixed
     */
    public function getOrderDetailByReferenceCode(string $referenceCode)
    {
        $this->init();

        $parameters = [
            \PayUParameters::REFERENCE_CODE => $referenceCode,
        ];

        try {
            return \PayUReports::getOrderDetailByReferenceCode($parameters);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
