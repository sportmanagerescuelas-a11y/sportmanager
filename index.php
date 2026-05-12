<?php
require_once __DIR__ . '/config/session.php';

if (isset($_GET['action'])) {
    require_once __DIR__ . '/config/conexion.php';
    require_once __DIR__ . '/app/controllers/FacturaController.php';

    $controller = new FacturaController($conexion);
    $action = $_GET['action'] ?? 'listar';
    $id = $_GET['id'] ?? null;

    switch ($action) {
        case 'ver':
            if (!$id) {
                http_response_code(400);
                die('Falta el ID de la factura.');
            }
            $controller->ver($id);
            break;
        case 'pdf':
            if (!$id) {
                http_response_code(400);
                die('Falta el ID de la factura.');
            }
            $controller->descargarPdf($id);
            break;
        case 'listar':
        default:
            $controller->listar();
            break;
    }
    exit;
}

$url = $_GET['url'] ?? 'home';
$path = trim((string)$url, '/');
$route = preg_replace('/\.php$/', '', $path);

switch ($route) {
    case '':
    case 'home':
        require_once __DIR__ . '/app/views/layout/header.php';
        require_once __DIR__ . '/app/views/layout/content.php';
        require_once __DIR__ . '/app/views/layout/footer.php';
        break;
    case 'recuperar':
    case 'enviar':
    case 'reset':
    case 'guardar':
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $controller = new AuthController();

        if ($route === 'recuperar') {
            $controller->showRecuperar();
        } elseif ($route === 'enviar') {
            $controller->enviarReset();
        } elseif ($route === 'reset') {
            $controller->showReset();
        } else {
            $controller->guardarPassword();
        }
        break;
    case 'iniciar':
        require_once __DIR__ . '/app/bootstrap.php';
        require_once __DIR__ . '/app/controllers/IniciarController.php';
        (new App\Controllers\IniciarController())->show();
        break;
    case 'confirmacion-pago-isn':
        require_once __DIR__ . '/app/bootstrap.php';
        require_once __DIR__ . '/app/controllers/ConfirmacionController.php';
        (new App\Controllers\ConfirmacionController())->pagoIsn();
        break;
    case 'login':
    case 'register':
    case 'crear_escuela':
    case 'logout':
    case 'dashboard':
    case 'admin_usuarios':
    case 'editar_usuario':
    case 'deportistas':
    case 'crear_deportista':
    case 'editar_deportista':
    case 'eliminar_deportista':
    case 'eventos':
    case 'gestion_eventos':
    case 'crear_evento':
    case 'editar_evento':
    case 'toggle_evento':
    case 'ver_inscritos':
    case 'ver_deportistas_usuario':
    case 'inscribirse':
    case 'descargar':
    case 'reportes':
        require_once __DIR__ . '/app/controllers/PagesController.php';
        $controller = new PagesController();
        $actions = [
            'login' => 'login',
            'register' => 'register',
            'crear_escuela' => 'createSchool',
            'logout' => 'logout',
            'dashboard' => 'dashboard',
            'admin_usuarios' => 'adminUsers',
            'editar_usuario' => 'editUser',
            'deportistas' => 'athletes',
            'crear_deportista' => 'createAthlete',
            'editar_deportista' => 'editAthlete',
            'eliminar_deportista' => 'deleteAthlete',
            'eventos' => 'events',
            'gestion_eventos' => 'manageEvents',
            'crear_evento' => 'createEvent',
            'editar_evento' => 'editEvent',
            'toggle_evento' => 'toggleEvent',
            'ver_inscritos' => 'eventRegistrations',
            'ver_deportistas_usuario' => 'userAthletes',
            'inscribirse' => 'enroll',
            'descargar' => 'downloadReport',
            'reportes' => 'reports',
        ];
        $controller->{$actions[$route]}();
        break;
    case 'productos':
        if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] !== 3) {
            header('Location: dashboard.php');
            exit;
        }

        require_once __DIR__ . '/config/conexion.php';
        require_once __DIR__ . '/app/controllers/ProductoController.php';

        $controller = new ProductoController($conexion);
        $action = $_GET['product_action'] ?? 'listar';
        $id = $_GET['id'] ?? null;

        switch ($action) {
            case 'nuevo':
                $producto = [];
                $isEdit = false;
                require __DIR__ . '/app/views/productos/form.php';
                break;
            case 'guardar':
                $controller->guardar();
                break;
            case 'editar':
                $producto = $controller->obtenerPorId($id);
                $isEdit = true;
                require __DIR__ . '/app/views/productos/form.php';
                break;
            case 'actualizar':
                $controller->actualizar($id);
                break;
            case 'eliminar':
                $controller->borrar($id);
                break;
            case 'listar':
            default:
                $productos = $controller->listar();
                require __DIR__ . '/app/views/productos/listar.php';
                break;
        }
        break;
    case 'pagos':
        require_once __DIR__ . '/app/bootstrap.php';
        require_once __DIR__ . '/app/controllers/PagosPageController.php';
        (new App\Controllers\PagosPageController())->show();
        break;
    case 'obtener_bancos':
        require_once __DIR__ . '/app/bootstrap.php';
        require_once __DIR__ . '/app/controllers/BancosController.php';
        (new App\Controllers\BancosController())->pseBanks();
        break;
    case 'procesar_pago':
        require_once __DIR__ . '/app/bootstrap.php';
        require_once __DIR__ . '/app/controllers/PagoController.php';
        (new App\Controllers\PagoController())->inscripcion();
        break;
    case 'registrar-asistencia':
        require_once __DIR__ . '/app/controllers/DeportistasController.php';
        (new DeportistasController())->index();
        break;
    case 'guardar-asistencia':
        require_once __DIR__ . '/app/controllers/DeportistasController.php';
        (new DeportistasController())->guardar();
        break;
    default:
        http_response_code(404);
        require_once __DIR__ . '/app/views/layout/header.php';
        require_once __DIR__ . '/app/views/pages/error404.php';
        require_once __DIR__ . '/app/views/layout/footer.php';
}
