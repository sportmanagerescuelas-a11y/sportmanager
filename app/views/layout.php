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
$assetBase = '/sportmanager/';

$publicAssetPath = static function (string $path) use ($assetBase): string {
    $trimmed = trim($path);
    if ($trimmed === '') {
        return $assetBase . 'assets/img/balonfutbol.png';
    }
    if (preg_match('#^(?:https?:)?//#i', $trimmed) === 1 || str_starts_with($trimmed, '/')) {
        return $trimmed;
    }
    return $assetBase . ltrim($trimmed, '/');
};

$schoolPrimaryColor = '#212529';
$schoolSecondaryColor = '#001285';
$schoolShieldPath = $assetBase . 'assets/img/balonfutbol.png';
$currentRole = (int)($_SESSION['rol'] ?? 0);
$roleLabel = [1 => 'Acudiente', 2 => 'Entrenador', 3 => 'Administrador', 4 => 'Superadmin'][$currentRole] ?? 'Usuario';
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
                    $schoolShieldPath = $publicAssetPath($shield);
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

$primaryRgb = $hexToRgb($schoolPrimaryColor);
$secondaryRgb = $hexToRgb($schoolSecondaryColor);
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
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($assetBase . 'assets/img/balonfutbol.png', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css?v=<?= urlencode($appCssVersion) ?>" rel="stylesheet">
    <link href="assets/css/style.css?v=<?= urlencode($styleCssVersion) ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100" style="--school-primary-color: <?= htmlspecialchars($schoolPrimaryColor, ENT_QUOTES, 'UTF-8') ?>; --school-secondary-color: <?= htmlspecialchars($schoolSecondaryColor, ENT_QUOTES, 'UTF-8') ?>; --school-primary-rgb: <?= (int)$primaryRgb[0] ?>, <?= (int)$primaryRgb[1] ?>, <?= (int)$primaryRgb[2] ?>; --school-secondary-rgb: <?= (int)$secondaryRgb[0] ?>, <?= (int)$secondaryRgb[1] ?>, <?= (int)$secondaryRgb[2] ?>; --school-shield-image: <?= htmlspecialchars($schoolShieldCssImage, ENT_QUOTES, 'UTF-8') ?>;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container-fluid px-3 px-lg-4 gap-2">
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
