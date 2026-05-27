<?php
$viewData = get_defined_vars();
$title = (string)($viewData['title'] ?? 'Modulo');
$content = (string)($viewData['content'] ?? '');
$appCssPath = __DIR__ . '/../../assets/css/app.css';
$styleCssPath = __DIR__ . '/../../assets/css/style.css';
$appJsPath = __DIR__ . '/../../assets/js/app.js';
$passwordTogglePath = __DIR__ . '/../../assets/js/password-toggle.js';
$appCssVersion = is_file($appCssPath) ? (string)filemtime($appCssPath) : (string)time();
$styleCssVersion = is_file($styleCssPath) ? (string)filemtime($styleCssPath) : (string)time();
$appJsVersion = is_file($appJsPath) ? (string)filemtime($appJsPath) : (string)time();
$passwordToggleVersion = is_file($passwordTogglePath) ? (string)filemtime($passwordTogglePath) : (string)time();

$schoolPrimaryColor = '#212529';
$schoolSecondaryColor = '#001285';
$schoolShieldPath = 'assets/img/balonfutbol.png';
$currentRole = (int)($_SESSION['rol'] ?? 0);
if ($currentRole !== 4 && isset($_SESSION['usuario']['id_escuela']) && (int)$_SESSION['usuario']['id_escuela'] > 0) {
    try {
        require_once __DIR__ . '/../../config/conexion.php';
        $schoolDb = isset($conexion) && $conexion instanceof PDO ? $conexion : null;
        if ($schoolDb instanceof PDO) {
            $schoolStmt = $schoolDb->prepare('SELECT color_primario, color_secundario, escudo_path FROM escuelas WHERE id_escuela = ? LIMIT 1');
            $schoolStmt->execute([(int)$_SESSION['usuario']['id_escuela']]);
            $schoolTheme = $schoolStmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($schoolTheme)) {
                $primary = (string)($schoolTheme['color_primario'] ?? '');
                $secondary = (string)($schoolTheme['color_secundario'] ?? '');
                $shield = trim((string)($schoolTheme['escudo_path'] ?? ''));
                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $primary) === 1) {
                    $schoolPrimaryColor = strtolower($primary);
                }
                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $secondary) === 1) {
                    $schoolSecondaryColor = strtolower($secondary);
                }
                if ($shield !== '') {
                    $schoolShieldPath = $shield;
                }
            }
        }
    } catch (Throwable) {
    }
}

$hexToRgb = static function (string $hex): array {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
        return [33, 37, 41];
    }
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
};

$mixWith = static function (string $hex, int $target, float $amount) use ($hexToRgb): string {
    $amount = max(0, min(1, $amount));
    [$r, $g, $b] = $hexToRgb($hex);
    $r = (int)round($r + (($target - $r) * $amount));
    $g = (int)round($g + (($target - $g) * $amount));
    $b = (int)round($b + (($target - $b) * $amount));
    return sprintf('#%02x%02x%02x', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
};

$primaryRgb = $hexToRgb($schoolPrimaryColor);
$secondaryRgb = $hexToRgb($schoolSecondaryColor);
$smBlue900 = $mixWith($schoolPrimaryColor, 0, 0.38);
$smBlue800 = $mixWith($schoolPrimaryColor, 0, 0.24);
$smBlue700 = $schoolPrimaryColor;
$smBlue600 = $mixWith($schoolPrimaryColor, 255, 0.12);
$smGray800 = $mixWith($schoolSecondaryColor, 0, 0.28);
$smGray700 = $schoolSecondaryColor;
$smGray500 = $mixWith($schoolSecondaryColor, 255, 0.22);
$shieldCssPath = str_replace('\\', '/', trim((string)$schoolShieldPath));
$shieldCssPath = str_replace(['"', "'", ' '], ['%22', '%27', '%20'], $shieldCssPath);
$schoolShieldCssImage = $shieldCssPath !== '' ? "url({$shieldCssPath})" : 'none';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" type="image/png" href="assets/img/balonfutbol.png">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css?v=<?= urlencode($appCssVersion) ?>" rel="stylesheet">
    <link href="assets/css/style.css?v=<?= urlencode($styleCssVersion) ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100" style="--school-primary-color: <?= htmlspecialchars($schoolPrimaryColor, ENT_QUOTES, 'UTF-8') ?>; --school-secondary-color: <?= htmlspecialchars($schoolSecondaryColor, ENT_QUOTES, 'UTF-8') ?>; --school-primary-rgb: <?= (int)$primaryRgb[0] ?>, <?= (int)$primaryRgb[1] ?>, <?= (int)$primaryRgb[2] ?>; --school-secondary-rgb: <?= (int)$secondaryRgb[0] ?>, <?= (int)$secondaryRgb[1] ?>, <?= (int)$secondaryRgb[2] ?>; --school-shield-image: <?= htmlspecialchars($schoolShieldCssImage, ENT_QUOTES, 'UTF-8') ?>; --sm-blue-900: <?= htmlspecialchars($smBlue900, ENT_QUOTES, 'UTF-8') ?>; --sm-blue-800: <?= htmlspecialchars($smBlue800, ENT_QUOTES, 'UTF-8') ?>; --sm-blue-700: <?= htmlspecialchars($smBlue700, ENT_QUOTES, 'UTF-8') ?>; --sm-blue-600: <?= htmlspecialchars($smBlue600, ENT_QUOTES, 'UTF-8') ?>; --sm-gray-800: <?= htmlspecialchars($smGray800, ENT_QUOTES, 'UTF-8') ?>; --sm-gray-700: <?= htmlspecialchars($smGray700, ENT_QUOTES, 'UTF-8') ?>; --sm-gray-500: <?= htmlspecialchars($smGray500, ENT_QUOTES, 'UTF-8') ?>;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container-fluid px-3 px-lg-4">
            <span class="navbar-brand fw-semibold">Asistencia - Deportistas</span>
        </div>
    </nav>

    <main class="container-fluid px-3 px-lg-4 py-3 py-lg-4 flex-grow-1">
        <?= $content ?>
    </main>

    <?php if (isset($_SESSION['usuario'])): ?>
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/password-toggle.js?v=<?= urlencode($passwordToggleVersion) ?>" defer></script>
    <script src="assets/js/app.js?v=<?= urlencode($appJsVersion) ?>" defer></script>
</body>
</html>
