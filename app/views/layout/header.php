<!DOCTYPE html>
<html lang="es">

<head>
    <?php
    $stylePath = __DIR__ . '/../../../assets/css/style.css';
    $styleVersion = is_file($stylePath) ? (string)filemtime($stylePath) : (string)time();
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
    $brandName = 'Sport Manager';
    $currentRole = (int)($_SESSION['rol'] ?? 0);
    $archivoActual = basename($_SERVER['PHP_SELF']);
    $urlParam = $_GET['url'] ?? 'home';
    $esPaginaPrincipal = ($archivoActual === 'index.php' && ($urlParam === 'home' || $urlParam === '') && !isset($_GET['action']));

    if ($currentRole !== 4 && isset($_SESSION['usuario']['id_escuela']) && (int)$_SESSION['usuario']['id_escuela'] > 0) {
        try {
            require_once __DIR__ . '/../../../config/conexion.php';
            $schoolDb = null;
            if (isset($conexion) && $conexion instanceof PDO) {
                $schoolDb = $conexion;
            } elseif (class_exists('Database') && method_exists('Database', 'getConnection')) {
                $schoolDb = Database::getConnection();
            }

            if ($schoolDb instanceof PDO) {
                $schoolStmt = $schoolDb->prepare('SELECT nombre, color_primario, color_secundario, escudo_path FROM escuelas WHERE id_escuela = ? LIMIT 1');
                $schoolStmt->execute([(int)$_SESSION['usuario']['id_escuela']]);
                $schoolTheme = $schoolStmt->fetch(PDO::FETCH_ASSOC);
                if (is_array($schoolTheme)) {
                    $schoolName = trim((string)($schoolTheme['nombre'] ?? ''));
                    $primary = (string)($schoolTheme['color_primario'] ?? '');
                    $secondary = (string)($schoolTheme['color_secundario'] ?? '');
                    $shield = trim((string)($schoolTheme['escudo_path'] ?? ''));
                    $role = (int)($_SESSION['rol'] ?? 0);
                    if ($role === 3 && $schoolName !== '') {
                        $brandName = $schoolName;
                    }
                    if ($shield !== '') {
                        $schoolShieldPath = $publicAssetPath($shield);
                    }
                    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $primary) === 1) {
                        $schoolPrimaryColor = strtolower($primary);
                    }
                    if (preg_match('/^#[0-9A-Fa-f]{6}$/', $secondary) === 1) {
                        $schoolSecondaryColor = strtolower($secondary);
                    }
                }
            }
        } catch (Throwable $e) {
            // Mantener tema por defecto si falla la consulta.
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
    $mixColor = static function (string $hex, string $targetHex, float $amount): string {
        $hex = ltrim($hex, '#');
        $targetHex = ltrim($targetHex, '#');
        if (strlen($hex) !== 6 || strlen($targetHex) !== 6 || !ctype_xdigit($hex) || !ctype_xdigit($targetHex)) {
            return '#212529';
        }

        $amount = max(0.0, min(1.0, $amount));
        $from = [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
        $to = [
            hexdec(substr($targetHex, 0, 2)),
            hexdec(substr($targetHex, 2, 2)),
            hexdec(substr($targetHex, 4, 2)),
        ];

        $rgb = [];
        for ($i = 0; $i < 3; $i++) {
            $rgb[$i] = (int)round($from[$i] + (($to[$i] - $from[$i]) * $amount));
        }

        return sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
    };
    $buttonPrimaryBg = $schoolPrimaryColor;
    $buttonPrimaryHover = $mixColor($schoolPrimaryColor, '#000000', 0.12);
    $buttonSecondaryBg = $schoolSecondaryColor;
    $buttonSecondaryHover = $mixColor($schoolSecondaryColor, '#000000', 0.12);
    $buttonSuccessBg = $mixColor($schoolSecondaryColor, '#ffffff', 0.16);
    $buttonSuccessHover = $mixColor($schoolSecondaryColor, '#000000', 0.08);
    $buttonInfoBg = $mixColor($schoolPrimaryColor, '#000000', 0.1);
    $buttonInfoHover = $mixColor($schoolPrimaryColor, '#000000', 0.18);
    $buttonWarningBg = $mixColor($schoolPrimaryColor, '#000000', 0.22);
    $buttonWarningHover = $mixColor($schoolPrimaryColor, '#000000', 0.32);
    $buttonDangerBg = $mixColor($schoolSecondaryColor, '#000000', 0.18);
    $buttonDangerHover = $mixColor($schoolSecondaryColor, '#000000', 0.28);
    $shieldCssPath = str_replace('\\', '/', trim((string)$schoolShieldPath));
    $shieldCssPath = str_replace(['"', "'", ' '], ['%22', '%27', '%20'], $shieldCssPath);
    $schoolShieldCssImage = $shieldCssPath !== '' ? "url({$shieldCssPath})" : 'none';
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sport Manager | Gestión deportiva</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($assetBase . 'assets/img/balonfutbol.png', ENT_QUOTES, 'UTF-8') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= urlencode($styleVersion) ?>">
</head>

    <body style="--school-primary-color: <?= htmlspecialchars($schoolPrimaryColor, ENT_QUOTES, 'UTF-8') ?>; --school-secondary-color: <?= htmlspecialchars($schoolSecondaryColor, ENT_QUOTES, 'UTF-8') ?>; --school-primary-rgb: <?= (int)$primaryRgb[0] ?>, <?= (int)$primaryRgb[1] ?>, <?= (int)$primaryRgb[2] ?>; --school-secondary-rgb: <?= (int)$secondaryRgb[0] ?>, <?= (int)$secondaryRgb[1] ?>, <?= (int)$secondaryRgb[2] ?>; --school-btn-primary-bg: <?= htmlspecialchars($buttonPrimaryBg, ENT_QUOTES, 'UTF-8') ?>; --school-btn-primary-hover: <?= htmlspecialchars($buttonPrimaryHover, ENT_QUOTES, 'UTF-8') ?>; --school-btn-secondary-bg: <?= htmlspecialchars($buttonSecondaryBg, ENT_QUOTES, 'UTF-8') ?>; --school-btn-secondary-hover: <?= htmlspecialchars($buttonSecondaryHover, ENT_QUOTES, 'UTF-8') ?>; --school-btn-success-bg: <?= htmlspecialchars($buttonSuccessBg, ENT_QUOTES, 'UTF-8') ?>; --school-btn-success-hover: <?= htmlspecialchars($buttonSuccessHover, ENT_QUOTES, 'UTF-8') ?>; --school-btn-info-bg: <?= htmlspecialchars($buttonInfoBg, ENT_QUOTES, 'UTF-8') ?>; --school-btn-info-hover: <?= htmlspecialchars($buttonInfoHover, ENT_QUOTES, 'UTF-8') ?>; --school-btn-warning-bg: <?= htmlspecialchars($buttonWarningBg, ENT_QUOTES, 'UTF-8') ?>; --school-btn-warning-hover: <?= htmlspecialchars($buttonWarningHover, ENT_QUOTES, 'UTF-8') ?>; --school-btn-danger-bg: <?= htmlspecialchars($buttonDangerBg, ENT_QUOTES, 'UTF-8') ?>; --school-btn-danger-hover: <?= htmlspecialchars($buttonDangerHover, ENT_QUOTES, 'UTF-8') ?>; --bs-primary: <?= htmlspecialchars($schoolPrimaryColor, ENT_QUOTES, 'UTF-8') ?>; --bs-secondary: <?= htmlspecialchars($schoolSecondaryColor, ENT_QUOTES, 'UTF-8') ?>; --bs-success: <?= htmlspecialchars($buttonSuccessBg, ENT_QUOTES, 'UTF-8') ?>; --bs-info: <?= htmlspecialchars($buttonInfoBg, ENT_QUOTES, 'UTF-8') ?>; --bs-warning: <?= htmlspecialchars($buttonWarningBg, ENT_QUOTES, 'UTF-8') ?>; --bs-danger: <?= htmlspecialchars($buttonDangerBg, ENT_QUOTES, 'UTF-8') ?>; --bs-primary-rgb: <?= (int)$primaryRgb[0] ?>, <?= (int)$primaryRgb[1] ?>, <?= (int)$primaryRgb[2] ?>; --bs-secondary-rgb: <?= (int)$secondaryRgb[0] ?>, <?= (int)$secondaryRgb[1] ?>, <?= (int)$secondaryRgb[2] ?>; --school-shield-image: <?= htmlspecialchars($schoolShieldCssImage, ENT_QUOTES, 'UTF-8') ?>;">
    <header>
        <div class="top-bar top-bar--brand text-white py-1">
            <div class="container top-bar__inner">
                <div class="top-bar__contact">
                    <span>Email: sportmanager.escuelas@gmail.com</span>
                    <span>Tel: 601 577 1818</span>
                </div>
                <div class="top-bar__social">
                    <span class="top-bar__social-label">Síguenos</span>
                    <a href="https://www.facebook.com/profile.php?id=100083328903404" rel="noopener noreferrer" class="top-bar__social-link top-bar__social-link--facebook">Facebook</a>
                    <a href="https://x.com/spmanager20" rel="noopener noreferrer" class="top-bar__social-link top-bar__social-link--x">X</a>
                    <a href="https://www.instagram.com/sport_manager_escuelas/" rel="noopener noreferrer" class="top-bar__social-link top-bar__social-link--instagram">Instagram</a>
                </div>
            </div>
        </div>

        <nav class="navbar navbar-expand-lg navbar-light custom-navbar" style="border-bottom: 4px solid <?= htmlspecialchars($schoolSecondaryColor, ENT_QUOTES, 'UTF-8') ?>;">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center brand-mark" href="home">
                    <img src="<?= htmlspecialchars($schoolShieldPath, ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="logo-nav me-2">
                    <span class="brand-text">
                        <span class="brand-name"><?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></span>
                        <small>Gestión deportiva</small>
                    </span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <?php
                    $usuarioLogueado = isset($_SESSION['usuario']);
                    $rolUsuario = (int)($_SESSION['rol'] ?? 0);
                    $rolEtiqueta = [1 => 'Acudiente', 2 => 'Entrenador', 3 => 'Administrador', 4 => 'Superadmin'][$rolUsuario] ?? 'Usuario';
                    $nombreUsuario = htmlspecialchars(
                        (string)(($_SESSION['usuario']['nombres'] ?? '') . ' ' . ($_SESSION['usuario']['apellidos'] ?? ''))
                    );

                    $menuPorRol = [
                        1 => [
                            ['label' => 'Mi panel', 'href' => 'dashboard'],
                            ['label' => 'Registrar deportista', 'href' => 'crear_deportista'],
                            ['label' => 'Mis deportistas', 'href' => 'deportistas'],
                            ['label' => 'Eventos', 'href' => 'eventos'],
                            ['label' => 'Mis pagos', 'href' => 'pagos'],
                            ['label' => 'Uniformes', 'href' => 'uniformes'],
                        ],
                        2 => [
                            ['label' => 'Mi panel', 'href' => 'dashboard'],
                            ['label' => 'Deportistas', 'href' => 'deportistas'],
                            ['label' => 'Registrar asistencia', 'href' => 'registrar-asistencia'],
                            ['label' => 'Reportes', 'href' => 'reportes'],
                            ['label' => 'Eventos', 'href' => 'eventos'],
                            ['label' => 'Uniformes', 'href' => 'uniformes'],
                        ],
                        3 => [
                            ['label' => 'Mi panel', 'href' => 'dashboard'],
                            ['label' => 'Gestionar escuelas', 'href' => 'gestion_escuelas'],
                            ['label' => 'Gestionar usuarios', 'href' => 'admin_usuarios'],
                            ['label' => 'Gestionar deportistas', 'href' => 'deportistas'],
                            ['label' => 'Gestionar eventos', 'href' => 'gestion_eventos'],
                            ['label' => 'Crear evento', 'href' => 'crear_evento'],
                            ['label' => 'Productos', 'href' => 'productos'],
                            ['label' => 'Reportes generales', 'href' => 'reportes'],
                            ['label' => 'Facturas', 'href' => 'index.php?action=listar'],
                            ['label' => 'Uniformes', 'href' => 'uniformes'],
                        ],
                    ];
                    $opcionesMenu = $menuPorRol[$rolUsuario] ?? [['label' => 'Mi panel', 'href' => 'dashboard']];
                    ?>

                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <?php if ($esPaginaPrincipal): ?>
                            <li class="nav-item"><a class="nav-link" href="home#sobre-nosotros">Sobre nosotros</a></li>
                            <li class="nav-item"><a class="nav-link" href="home#planes">Planes</a></li>
                            <li class="nav-item"><a class="nav-link" href="home#beneficios">Beneficios</a></li>
                            <li class="nav-item"><a class="nav-link" href="home#contacto">Contacto</a></li>
                        <?php endif; ?>

                        <?php if ($usuarioLogueado): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-semibold" href="#" id="menuRolDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Menu del rol
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuRolDropdown">
                                    <li><span class="dropdown-item-text text-uppercase small text-muted">Rol: <?= htmlspecialchars($rolEtiqueta, ENT_QUOTES, 'UTF-8') ?></span></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php foreach ($opcionesMenu as $item): ?>
                                        <li><a class="dropdown-item" href="<?= htmlspecialchars((string)$item['href']) ?>"><?= htmlspecialchars((string)$item['label']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Hola, <?= $nombreUsuario ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="dashboard">Mi panel</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout">Cerrar sesion</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="startDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Iniciar
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="startDropdown">
                                    <li><a class="dropdown-item" href="login">Iniciar sesion</a></li>
                                    <li><a class="dropdown-item" href="register">Registrarse</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

    </header>

    <main>
