<!DOCTYPE html>
<html lang="es">

<head>
    <?php
    $stylePath = __DIR__ . '/../../../assets/css/style.css';
    $styleVersion = is_file($stylePath) ? (string)filemtime($stylePath) : (string)time();
    $goldCardCssPath = __DIR__ . '/../../../Card/gold-card.css';
    $goldCardCssVersion = is_file($goldCardCssPath) ? (string)filemtime($goldCardCssPath) : (string)time();
<<<<<<< HEAD
    $assetBase = rtrim(sm_base_path(), '/') . '/';
=======
    $assetBase = '/sportmanager/';
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
    $publicAssetPath = static function (string $path) use ($assetBase): string {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return $assetBase . 'assets/img/escudo_sportmanager.png';
        }
        if (preg_match('#^(?:https?:)?//#i', $trimmed) === 1 || str_starts_with($trimmed, '/')) {
            return $trimmed;
        }
        return $assetBase . ltrim($trimmed, '/');
    };
    $schoolPrimaryColor = '#212529';
    $schoolSecondaryColor = '#001285';
    $schoolShieldPath = $assetBase . 'assets/img/escudo_sportmanager.png';
    $brandName = 'Sport Manager';
    $brandTitle = 'Sport Manager | Gestión deportiva';
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
                    if ($schoolName !== '') {
                        $brandName = $schoolName;
                        $brandTitle = $schoolName . ' | Gestión deportiva';
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
    <title><?= htmlspecialchars($brandTitle, ENT_QUOTES, 'UTF-8') ?></title>
<<<<<<< HEAD
    <base href="<?= htmlspecialchars(sm_url('/'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(sm_asset_url('assets/img/escudo_sportmanager.png'), ENT_QUOTES, 'UTF-8') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(sm_asset_url('assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>?v=<?= urlencode($styleVersion) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(sm_asset_url('Card/gold-card.css'), ENT_QUOTES, 'UTF-8') ?>?v=<?= urlencode($goldCardCssVersion) ?>">
=======
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($assetBase . 'assets/img/escudo_sportmanager.png', ENT_QUOTES, 'UTF-8') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= urlencode($styleVersion) ?>">
    <link rel="stylesheet" href="Card/gold-card.css?v=<?= urlencode($goldCardCssVersion) ?>">
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
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

    <!-- Hidden SVG defs: responsive clip-path for FIFA card shape.
         clipPathUnits="objectBoundingBox" makes coordinates scale
         with any card size (0-1 = left/top to right/bottom). -->
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
      <defs>
        <clipPath id="cardClip" clipPathUnits="objectBoundingBox">
          <!-- Card silhouette: hex notch at top, pointed bottom, curved sides.
               Coordinates normalized from 320×480 reference design. -->
          <path d="
            M 0.5 0
            C 0.534375 0, 0.553125 0.0125, 0.56875 0.022917
            C 0.584375 0.033333, 0.60625 0.045833, 0.65 0.052083
            C 0.7 0.058333, 0.7375 0.04375, 0.771875 0.027083
            C 0.815625 0.00625, 0.871875 0.004167, 0.915625 0.022917
            C 0.953125 0.039583, 0.978125 0.064583, 0.99375 0.091667
            L 0.99375 0.729167
            C 0.99375 0.847917, 0.86875 0.93125, 0.5 1
            C 0.13125 0.93125, 0.00625 0.847917, 0.00625 0.729167
            L 0.00625 0.091667
            C 0.021875 0.064583, 0.046875 0.039583, 0.084375 0.022917
            C 0.128125 0.004167, 0.184375 0.00625, 0.228125 0.027083
            C 0.2625 0.04375, 0.3 0.058333, 0.35 0.052083
            C 0.39375 0.045833, 0.415625 0.033333, 0.43125 0.022917
            C 0.446875 0.0125, 0.465625 0, 0.5 0
            Z
          "/>
        </clipPath>
      </defs>
    </svg>

    <main>
