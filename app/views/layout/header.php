<!DOCTYPE html>
<html lang="es">

<head>
    <?php
    $stylePath = __DIR__ . '/../../../assets/css/style.css';
    $styleVersion = is_file($stylePath) ? (string)filemtime($stylePath) : (string)time();
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto SM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= urlencode($styleVersion) ?>">
</head>

<body>
    <header>
        <div class="top-bar bg-dark text-white py-1">
            <div class="container d-flex justify-content-between">
                <span>Email: sportmanager.escuelas@gmail.com | Tel: 601 577 1818</span>
                <div>
                    <a href="https://www.facebook.com/profile.php?id=100083328903404" target="blank_" class="text-white me-2">Facebook</a>
                    <a href="https://x.com/spmanager20" target="blank_" class="text-white me-2">X</a>
                    <a href="https://www.instagram.com/sport_manager_escuelas/" target="blank_" class="text-white">Instagram</a>
                </div>
            </div>
        </div>

        <nav class="navbar navbar-expand-lg navbar-light custom-navbar">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    <img src="assets/img/logo_icoaner.jpg" alt="Logo" class="logo-nav me-2">
                    <span>Proyecto SM</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <?php
                    $archivoActual = basename($_SERVER['PHP_SELF']);
                    $urlParam = $_GET['url'] ?? 'home';
                    $esPaginaPrincipal = ($archivoActual === 'index.php' && ($urlParam === 'home' || $urlParam === '') && !isset($_GET['action']));
                    $usuarioLogueado = isset($_SESSION['usuario']);
                    $rolUsuario = (int)($_SESSION['rol'] ?? 0);
                    $nombreUsuario = htmlspecialchars(
                        (string)(($_SESSION['usuario']['nombres'] ?? '') . ' ' . ($_SESSION['usuario']['apellidos'] ?? ''))
                    );

                    $menuPorRol = [
                        1 => [
                            ['label' => 'Mi panel', 'href' => 'index.php?url=dashboard'],
                            ['label' => 'Registrar deportista', 'href' => 'index.php?url=crear_deportista'],
                            ['label' => 'Mis deportistas', 'href' => 'index.php?url=deportistas'],
                            ['label' => 'Eventos', 'href' => 'index.php?url=eventos'],
                            ['label' => 'Mis pagos', 'href' => 'index.php?url=pagos'],
                        ],
                        2 => [
                            ['label' => 'Mi panel', 'href' => 'index.php?url=dashboard'],
                            ['label' => 'Deportistas', 'href' => 'index.php?url=deportistas'],
                            ['label' => 'Registrar asistencia', 'href' => 'index.php?url=registrar-asistencia'],
                            ['label' => 'Reportes', 'href' => 'index.php?url=reportes'],
                            ['label' => 'Eventos', 'href' => 'index.php?url=eventos'],
                        ],
                        3 => [
                            ['label' => 'Mi panel', 'href' => 'index.php?url=dashboard'],
                            ['label' => 'Gestionar usuarios', 'href' => 'index.php?url=admin_usuarios'],
                            ['label' => 'Gestionar deportistas', 'href' => 'index.php?url=deportistas'],
                            ['label' => 'Gestionar eventos', 'href' => 'index.php?url=gestion_eventos'],
                            ['label' => 'Crear evento', 'href' => 'index.php?url=crear_evento'],
                            ['label' => 'Productos', 'href' => 'index.php?url=productos'],
                            ['label' => 'Reportes generales', 'href' => 'index.php?url=reportes'],
                            ['label' => 'Facturas', 'href' => 'index.php?action=listar'],
                        ],
                    ];
                    $opcionesMenu = $menuPorRol[$rolUsuario] ?? [['label' => 'Mi panel', 'href' => 'index.php?url=dashboard']];
                    ?>

                    <ul class="navbar-nav ms-auto">
                        <?php if ($esPaginaPrincipal): ?>
                            <li class="nav-item"><a class="nav-link" href="index.php#sobre-nosotros">Sobre nosotros</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php#planes">Planes</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php#beneficios">Beneficios</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php#contacto">Contacto</a></li>
                        <?php endif; ?>

                        <?php if ($usuarioLogueado): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle fw-semibold" href="#" id="menuRolDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Menu del rol
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuRolDropdown">
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
                                    <li><a class="dropdown-item" href="index.php?url=dashboard">Mi panel</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="index.php?url=logout">Cerrar sesion</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="startDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Iniciar
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="startDropdown">
                                    <li><a class="dropdown-item" href="index.php?url=crear_escuela">Crear escuela</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="index.php?url=login">Iniciar sesion</a></li>
                                    <li><a class="dropdown-item" href="index.php?url=register">Registrarse</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

    </header>

    <main>
