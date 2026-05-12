<?php

// Bootstrap minimo para un MVC sin Composer.
// Mantiene el proyecto funcional bajo XAMPP y endpoints en la raiz.

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('APP_BASE_PATH', dirname(__DIR__));
require_once APP_BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'session.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'ui.php';

define('APP_PATH', __DIR__);
define('CONFIG_PATH', APP_BASE_PATH . DIRECTORY_SEPARATOR . 'config');

$scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}
define('BASE_PATH', $basePath);

$vendorAutoload = APP_BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($vendorAutoload)) {
    require_once $vendorAutoload;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';


