<?php

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../models/PagesModel.php';
require_once __DIR__ . '/../helpers/ui.php';

class PagesController
{
    private ?PagesModel $model = null;

    public function __construct()
    {
    }

    public function login(): void
    {
        $this->guestOnly();
        $this->render('login');
    }

    public function register(): void
    {
        $this->guestOnly();
        $schools = [];
        try {
            $schools = $this->model()->schools();
        } catch (Throwable) {
            $schools = [];
        }

        if (count($schools) === 0) {
            $this->redirect('index.php?url=crear_escuela&required=1');
        }

        $this->render('register', ['schools' => $schools]);
    }

    public function createSchool(): void
    {
        $this->guestOnly();
        $error = null;
        $errorDetails = [];
        $formData = [
            'nombre' => '',
            'disciplina' => '',
            'dia_pago' => '',
            'valor_inscripcion' => '',
            'valor_mensualidad' => '',
            'correo' => '',
            'pass_app' => '',
            'telefono' => '',
            'direccion' => '',
            'escudo_path' => '',
            'firma_path' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = [
                'nombre' => trim((string)($_POST['nombre'] ?? '')),
                'disciplina' => trim((string)($_POST['disciplina'] ?? '')),
                'dia_pago' => (int)($_POST['dia_pago'] ?? 0),
                'valor_inscripcion' => trim((string)($_POST['valor_inscripcion'] ?? '0')),
                'valor_mensualidad' => trim((string)($_POST['valor_mensualidad'] ?? '0')),
                'correo' => trim((string)($_POST['correo'] ?? '')),
                'pass_app' => trim((string)($_POST['pass_app'] ?? '')),
                'telefono' => trim((string)($_POST['telefono'] ?? '')),
                'direccion' => trim((string)($_POST['direccion'] ?? '')),
                'escudo_path' => trim((string)($_POST['escudo_path'] ?? '')) ?: null,
                'firma_path' => trim((string)($_POST['firma_path'] ?? '')) ?: null,
            ];
            $formData = [
                'nombre' => $payload['nombre'],
                'disciplina' => $payload['disciplina'],
                'dia_pago' => (string)$payload['dia_pago'],
                'valor_inscripcion' => (string)$payload['valor_inscripcion'],
                'valor_mensualidad' => (string)$payload['valor_mensualidad'],
                'correo' => $payload['correo'],
                'pass_app' => $payload['pass_app'],
                'telefono' => $payload['telefono'],
                'direccion' => $payload['direccion'],
                'escudo_path' => (string)($payload['escudo_path'] ?? ''),
                'firma_path' => (string)($payload['firma_path'] ?? ''),
            ];

            $emailValido = filter_var($payload['correo'], FILTER_VALIDATE_EMAIL) !== false;
            $diaPagoValido = $payload['dia_pago'] >= 1 && $payload['dia_pago'] <= 31;
            $inscripcionValida = is_numeric($payload['valor_inscripcion']) && (float)$payload['valor_inscripcion'] >= 0;
            $mensualidadValida = is_numeric($payload['valor_mensualidad']) && (float)$payload['valor_mensualidad'] >= 0;
            $telefonoValido = preg_match('/^\d{7,11}$/', $payload['telefono']) === 1;

            if ($payload['nombre'] === '') {
                $errorDetails[] = 'El nombre de la escuela es obligatorio.';
            }
            if ($payload['disciplina'] === '') {
                $errorDetails[] = 'La disciplina es obligatoria.';
            }
            if (!$emailValido) {
                $errorDetails[] = 'El correo oficial no tiene un formato valido.';
            }
            if (!$diaPagoValido) {
                $errorDetails[] = 'El dia de pago debe estar entre 1 y 31.';
            }
            if (!$inscripcionValida) {
                $errorDetails[] = 'El valor de inscripcion debe ser un numero mayor o igual a 0.';
            }
            if (!$mensualidadValida) {
                $errorDetails[] = 'El valor de mensualidad debe ser un numero mayor o igual a 0.';
            }
            if ($payload['pass_app'] === '') {
                $errorDetails[] = 'La clave de app/correo es obligatoria.';
            }
            if (!$telefonoValido) {
                $errorDetails[] = 'El telefono debe tener entre 7 y 11 digitos numericos.';
            }
            if ($payload['direccion'] === '') {
                $errorDetails[] = 'La direccion es obligatoria.';
            }

            if (!empty($errorDetails)) {
                $error = 'Corrige los siguientes errores para crear la escuela.';
            } else {
                try {
                    $schoolId = $this->model()->createSchool($payload);
                    if ($schoolId !== false) {
                        $this->redirect('index.php?url=register&school_created=1&id_escuela=' . urlencode((string)$schoolId));
                    }
                    $dbError = $this->model()->lastError();
                    $error = $dbError !== '' ? ('No se pudo crear la escuela: ' . $dbError) : 'No se pudo crear la escuela.';
                } catch (Throwable $e) {
                    $error = 'No se pudo crear la escuela: error interno de conexion o servidor.';
                    $errorDetails[] = 'Detalle tecnico: ' . $e->getMessage();
                }
            }
        }

        $this->render('crear_escuela', ['error' => $error, 'errorDetails' => $errorDetails, 'formData' => $formData]);
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool)$params['secure'],
                (bool)$params['httponly']
            );
        }

        session_unset();
        session_destroy();
        $this->redirect('index.php?url=login');
    }

    public function dashboard(): void
    {
        $this->requireLogin();
        $data = $this->model()->dashboardData((int)$_SESSION['id_usuario'], (int)$_SESSION['rol']);
        $this->render('dashboard', $data + ['rol' => (int)$_SESSION['rol']]);
    }

    public function adminUsers(): void
    {
        $this->requireAdmin();
        $this->render('admin_usuarios', [
            'usuariosPendientes' => $this->model()->pendingUsers(),
            'usuariosAprobados' => $this->model()->approvedUsers(),
        ]);
    }

    public function editUser(): void
    {
        $this->requireAdmin();
        $user = $this->model()->userById((string)($_GET['id'] ?? ''));
        if (!$user) {
            $this->redirect('admin_usuarios.php');
        }
        $this->render('editar_usuario', ['user' => $user]);
    }

    public function athletes(): void
    {
        $this->requireLogin();
        $this->render('deportistas', [
            'rows' => $this->model()->athletesForRole((int)$_SESSION['id_usuario'], (int)$_SESSION['rol']),
            'rol' => (int)$_SESSION['rol'],
        ]);
    }

    public function createAthlete(): void
    {
        $this->requireLogin();
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = $this->athletePayload('default.png');
            $payload['id_usuario'] = (int)$_SESSION['id_usuario'];
            $payload['foto'] = $this->storeUploadedPhoto('default.png');

            if ($this->model()->createAthlete($payload)) {
                $this->redirect('deportistas.php');
            }
            $dbError = $this->model()->lastError();
            $error = $dbError !== '' ? ('No se pudo registrar el deportista: ' . $dbError) : 'No se pudo registrar el deportista.';
        }

        $this->render('crear_deportista', [
            'categorias' => $this->model()->categories(),
            'niveles' => $this->model()->levels(),
            'error' => $error,
        ]);
    }

    public function editAthlete(): void
    {
        $this->requireLogin();
        $id = (string)($_GET['id'] ?? '');
        $athlete = $this->model()->athleteById($id);
        if (!$athlete) {
            $this->redirect('deportistas.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = $this->athletePayload($athlete->foto);
            $payload['foto'] = $this->storeUploadedPhoto($athlete->foto);
            if ($payload['foto'] !== $athlete->foto) {
                $this->deleteOldPhoto($athlete->foto);
            }
            $this->model()->updateAthlete($id, $payload);
            $this->redirect('deportistas.php');
        }

        $this->render('editar_deportista', [
            'data' => $athlete,
            'categorias' => $this->model()->categories(),
            'niveles' => $this->model()->levels(),
        ]);
    }

    public function deleteAthlete(): void
    {
        $this->requireLogin();
        $id = (string)($_GET['id'] ?? '');
        $athlete = $this->model()->athleteById($id);
        $isOwner = $athlete && (int)$athlete->id_usuario === (int)$_SESSION['id_usuario'];
        if ($athlete && ((int)$_SESSION['rol'] === 3 || $isOwner)) {
            $this->model()->deleteAthlete($id);
        }
        $this->redirect('deportistas.php');
    }

    public function userAthletes(): void
    {
        $this->requireAdmin();
        $this->render('ver_deportistas_usuario', [
            'deportistas' => $this->model()->athletesByUser((string)($_GET['id'] ?? '')),
        ]);
    }

    public function events(): void
    {
        $this->requireLogin();
        $this->render('eventos', ['eventos' => $this->model()->events()]);
    }

    public function manageEvents(): void
    {
        $this->requireAdmin();
        $this->render('gestion_eventos', ['eventos' => $this->model()->managedEvents()]);
    }

    public function createEvent(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fecha = (string)($_POST['fecha'] ?? '');
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            $hoy = new DateTime('today');
            if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha || $fechaObj < $hoy) {
                $this->render('crear_evento', ['error' => 'No puedes crear eventos en fechas pasadas.']);
                return;
            }
            $this->model()->createEvent($this->eventPayload());
            $this->redirect('dashboard.php');
        }
        $this->render('crear_evento');
    }

    public function editEvent(): void
    {
        $this->requireAdmin();
        $id = (string)($_GET['id'] ?? '');
        $evento = $this->model()->eventById($id);
        if (!$evento) {
            $this->redirect('gestion_eventos.php');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fecha = (string)($_POST['fecha'] ?? '');
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            $hoy = new DateTime('today');
            if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha || $fechaObj < $hoy) {
                $this->render('editar_evento', [
                    'evento' => $evento,
                    'error' => 'No puedes asignar fechas pasadas al evento.',
                ]);
                return;
            }
            $this->model()->updateEvent($id, $this->eventPayload());
            $this->redirect('gestion_eventos.php');
        }
        $this->render('editar_evento', ['evento' => $evento]);
    }

    public function toggleEvent(): void
    {
        $this->requireAdmin();
        $this->model()->toggleEvent((string)($_GET['id'] ?? ''));
        $this->redirect('gestion_eventos.php');
    }

    public function eventRegistrations(): void
    {
        $this->requireAdmin();
        $id = (string)($_GET['id'] ?? '');
        $evento = $this->model()->eventById($id);
        if (!$evento) {
            $this->render('ver_inscritos', ['evento' => null, 'inscritos' => []]);
            return;
        }
        $this->render('ver_inscritos', [
            'evento' => $evento,
            'inscritos' => $this->model()->eventRegistrations($id),
        ]);
    }

    public function enroll(): void
    {
        if (!isset($_SESSION['id_usuario'])) {
            exit('No autorizado');
        }

        $rawInput = [];
        $rawBody = file_get_contents('php://input');
        if (is_string($rawBody) && $rawBody !== '') {
            parse_str($rawBody, $rawInput);
        }

        $athleteId = trim((string)(
            $_POST['id_deportista']
            ?? $_POST['deportista_id']
            ?? $_POST['athlete_id']
            ?? $rawInput['id_deportista']
            ?? $rawInput['deportista_id']
            ?? $rawInput['athlete_id']
            ?? $rawInput['deportista']
            ?? ''
        ));

        $eventId = (int)(
            $_POST['id_evento']
            ?? $rawInput['id_evento']
            ?? 0
        );

        echo $this->model()->registerInEvent($eventId, (int)$_SESSION['id_usuario'], $athleteId);
    }

    public function downloadReport(): void
    {
        $this->requireReportAccess();
        $tabla = isset($_GET['tabla']) ? (string)$_GET['tabla'] : '';
        if (!$this->canAccessReportTable($tabla)) {
            $this->redirect('index.php?url=reportes&error=forbidden_table');
        }

        require_once __DIR__ . '/ReporteController.php';
        if ($tabla !== '') {
            (new ReporteController())->descargar($tabla, (string)($_GET['formato'] ?? 'xlsx'));
        }
    }

    public function reports(): void
    {
        $this->requireReportAccess();
        require_once __DIR__ . '/../models/ReporteModel.php';
        $tablas = (new ReporteModel())->obtenerTablas();
        $tablas = array_values(array_filter($tablas, fn ($tabla): bool => $this->canAccessReportTable((string)$tabla)));
        $this->render('reportes', ['tablas' => $tablas]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/pages/' . $view . '.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    private function model(): PagesModel
    {
        if (!$this->model instanceof PagesModel) {
            $this->model = new PagesModel();
        }

        return $this->model;
    }

    private function requireLogin(): void
    {
        if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
            $this->redirect('index.php?url=login');
        }
    }

    private function requireAdmin(): void
    {
        if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] !== 3) {
            $this->redirect('dashboard.php');
        }
    }

    private function requireReportAccess(): void
    {
        if (!isset($_SESSION['rol']) || !in_array((int)$_SESSION['rol'], [2, 3], true)) {
            $this->redirect('dashboard.php');
        }
    }

    private function canAccessReportTable(string $tabla): bool
    {
        $rol = (int)($_SESSION['rol'] ?? 0);
        if ($rol === 3) {
            return true;
        }

        if ($rol === 2) {
            $normalizada = strtolower(trim($tabla));
            $permitidas = [
                'asistencia',
                'asistencias',
                'uniforme',
                'uniformes',
                'deportista',
                'deportistas',
            ];
            return in_array($normalizada, $permitidas, true);
        }

        return false;
    }

    private function guestOnly(): void
    {
        if (isset($_SESSION['usuario'])) {
            $this->redirect('dashboard.php');
        }
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    private function athletePayload(string $currentPhoto): array
    {
        return [
            'tipo_documento' => (string)($_POST['tipo_documento'] ?? ''),
            'id_deportista' => (string)($_POST['id_deportista'] ?? $_POST['num_documento'] ?? ''),
            'foto' => $currentPhoto,
            'nombres' => (string)($_POST['nombres'] ?? ''),
            'apellidos' => (string)($_POST['apellidos'] ?? ''),
            'fecha_nacimiento' => (string)($_POST['fecha_nacimiento'] ?? ''),
            'jornada' => (string)($_POST['jornada'] ?? ''),
            'id_categoria' => (string)($_POST['id_categoria'] ?? ''),
            'id_nivel' => (string)($_POST['id_nivel'] ?? ''),
            'genero' => (string)($_POST['genero'] ?? ''),
        ];
    }

    private function eventPayload(): array
    {
        return [
            'titulo' => (string)($_POST['titulo'] ?? ''),
            'fecha' => (string)($_POST['fecha'] ?? ''),
            'id_rol' => (int)($_POST['id_rol'] ?? 1),
            'tipo_evento' => (string)($_POST['tipo_evento'] ?? ''),
            'costo' => (string)($_POST['costo'] ?? '0'),
            'cuotas' => (string)($_POST['cuotas'] ?? '0'),
        ];
    }

    private function storeUploadedPhoto(string $fallback): string
    {
        if (empty($_FILES['foto']['tmp_name']) || empty($_FILES['foto']['name'])) {
            return $fallback;
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$_FILES['foto']['name']);
        $fileName = time() . '_' . $safeName;
        $target = dirname(__DIR__, 2) . '/fotos/' . $fileName;

        return move_uploaded_file($_FILES['foto']['tmp_name'], $target) ? $fileName : $fallback;
    }

    private function deleteOldPhoto(string $photo): void
    {
        if ($photo === 'default.png') {
            return;
        }

        $path = dirname(__DIR__, 2) . '/fotos/' . $photo;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
