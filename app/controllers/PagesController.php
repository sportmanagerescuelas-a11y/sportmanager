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
        $schoolPaymentData = [];
        try {
            $model = $this->model();
            $schools = $model->schools();
            foreach ($schools as $school) {
                $schoolId = (int)($school->id_escuela ?? 0);
                if ($schoolId <= 0) {
                    continue;
                }
                $schoolPaymentData[(string)$schoolId] = [
                    'id_escuela' => $schoolId,
                    'nombre' => (string)($school->nombre ?? 'Escuela'),
                    'disciplina' => (string)($school->disciplina ?? ''),
                    'valor_inscripcion' => (float)($school->valor_inscripcion ?? 0),
                    'methods' => $model->paymentMethodsBySchool($schoolId),
                ];
            }
        } catch (Throwable $e) {
            $schools = [];
            $schoolPaymentData = [];
        }

        if (count($schools) === 0) {
            $this->render('register', ['schools' => $schools, 'schoolPaymentData' => $schoolPaymentData]);
            return;
        }

        $this->render('register', ['schools' => $schools, 'schoolPaymentData' => $schoolPaymentData]);
    }

    public function createSchool(): void
    {
        $this->requireSchoolCreationAccess();
        $isAdminOnboarding = $this->isAdminPendingSchoolCreation();
        $error = null;
        $errorDetails = [];
        $formData = $this->emptySchoolFormData();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = $this->schoolPayload();
            $formData = $this->schoolFormData($payload);
            $errorDetails = $this->validateSchoolPayload($payload);

            if (!empty($errorDetails)) {
                $error = 'Corrige los siguientes errores para crear la escuela.';
            } else {
                try {
                    $payload['escudo_path'] = $this->storeSchoolShield((string)($payload['escudo_path'] ?? ''));
                    $payload['metodos_pago'] = $this->storePaymentMethodQrs(is_array($payload['metodos_pago'] ?? null) ? $payload['metodos_pago'] : []);
                    $adminCreatorId = $isAdminOnboarding ? (string)$_SESSION['id_usuario'] : null;
                    $schoolId = $this->model()->createSchool($payload, $adminCreatorId);
                    if ($schoolId !== false) {
                        if ($isAdminOnboarding) {
                            $_SESSION['usuario']['id_escuela'] = (int)$schoolId;
                            $_SESSION['usuario']['estado'] = 'aprobado';
                            $_SESSION['usuario']['habilitado'] = 1;
                            $this->redirect('dashboard');
                        }
                        $this->redirect('gestion_escuelas&created=1');
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

    public function manageSchools(): void
    {
        $this->requireSuperAdmin();
        $this->render('gestion_escuelas', [
            'escuelas' => $this->model()->allSchools(),
            'error' => isset($_GET['error']) ? $this->schoolActionError((string)$_GET['error']) : '',
        ]);
    }

    public function editSchool(): void
    {
        $this->requireSuperAdmin();
        $id = (string)($_GET['id'] ?? '');
        $school = $this->model()->schoolById($id);
        if (!$school) {
            $this->redirect('gestion_escuelas&error=notfound');
        }

        $error = null;
        $errorDetails = [];
        $schoolData = (array)$school;
        $schoolData['metodos_pago'] = $this->model()->paymentMethodsBySchool((int)$id);
        $formData = $this->schoolFormData($schoolData);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = $this->schoolPayload();
            $formData = $this->schoolFormData($payload);
            $errorDetails = $this->validateSchoolPayload($payload);

            if (!empty($errorDetails)) {
                $error = 'Corrige los siguientes errores para actualizar la escuela.';
            } else {
                $payload['escudo_path'] = $this->storeSchoolShield((string)($payload['escudo_path'] ?? ''));
                $payload['metodos_pago'] = $this->storePaymentMethodQrs(is_array($payload['metodos_pago'] ?? null) ? $payload['metodos_pago'] : []);
                if ($this->model()->updateSchool($id, $payload)) {
                    $this->redirect('gestion_escuelas&updated=1');
                }
                $dbError = $this->model()->lastError();
                $error = $dbError !== '' ? ('No se pudo actualizar la escuela: ' . $dbError) : 'No se pudo actualizar la escuela.';
            }
        }

        $this->render('crear_escuela', [
            'error' => $error,
            'errorDetails' => $errorDetails,
            'formData' => $formData,
            'isEdit' => true,
            'schoolId' => $id,
        ]);
    }

    public function deleteSchool(): void
    {
        $this->requireSuperAdmin();
        $this->requirePostRequest('gestion_escuelas');
        $this->requireCsrfToken();
        $id = $this->requestId();
        if ($id === '' || !$this->model()->schoolById($id)) {
            $this->redirect('gestion_escuelas&error=notfound');
        }

        if (!$this->model()->deleteSchool($id)) {
            $this->redirect('gestion_escuelas&error=delete');
        }

        $this->redirect('gestion_escuelas&deleted=1');
    }

    public function logout(): void
    {
        $inactiveReason = ((string)($_GET['reason'] ?? '') === 'inactive');
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

        if ($inactiveReason) {
            session_start();
            $_SESSION['flash_session_expired'] = true;
        }

        $this->redirect('login');
    }

    public function dashboard(): void
    {
        $this->requireLogin();
        $data = $this->model()->dashboardData((int)$_SESSION['id_usuario'], (int)$_SESSION['rol']);
        $this->render('dashboard', $data + ['rol' => (int)$_SESSION['rol']]);
    }

    public function adminUsers(): void
    {
        $this->requireAdminOrSuperAdmin();
        $role = (int)($_SESSION['rol'] ?? 0);
        if ($role === 3) {
            $schoolId = (int)($_SESSION['usuario']['id_escuela'] ?? 0);
            $this->render('admin_usuarios', [
                'usuariosPendientes' => [],
                'usuariosAprobados' => $schoolId > 0 ? $this->model()->usersBySchool($schoolId) : [],
                'isSchoolAdminView' => true,
            ]);
            return;
        }

        $this->render('admin_usuarios', [
            'usuariosPendientes' => $this->model()->pendingUsers(),
            'usuariosAprobados' => $this->model()->approvedUsers(),
            'isSchoolAdminView' => false,
        ]);
    }

    public function editUser(): void
    {
        $this->requireAdminOrSuperAdmin();
        $user = $this->model()->userById((string)($_GET['id'] ?? ''));
        if (!$user) {
            $this->redirect('admin_usuarios');
        }
        $this->render('editar_usuario', [
            'user' => $user,
            'schools' => $this->model()->schools(),
        ]);
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
        $errorDetails = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = $this->athletePayload('default.png');
            $payload['id_usuario'] = (int)$_SESSION['id_usuario'];
            $payload['foto'] = $this->storeUploadedPhoto('default.png');

            if (!preg_match('/^\d{1,11}$/', $payload['id_deportista'])) {
                $errorDetails[] = 'El numero de documento del deportista debe tener maximo 11 digitos numericos.';
            }
            if ($payload['nombres'] === '') {
                $errorDetails[] = 'Los nombres del deportista son obligatorios.';
            }
            if ($payload['apellidos'] === '') {
                $errorDetails[] = 'Los apellidos del deportista son obligatorios.';
            }
            if ($payload['fecha_nacimiento'] === '' || DateTime::createFromFormat('Y-m-d', $payload['fecha_nacimiento']) === false) {
                $errorDetails[] = 'La fecha de nacimiento no es valida.';
            }
            if (!$this->model()->categoryExists((string)$payload['id_categoria'])) {
                $errorDetails[] = 'Debes seleccionar una categoria valida.';
            }
            if (!$this->model()->levelExists((string)$payload['id_nivel'])) {
                $errorDetails[] = 'Debes seleccionar un nivel valido.';
            }
            if ($this->model()->athleteExists((string)$payload['id_deportista'])) {
                $errorDetails[] = 'Ya existe un deportista con ese numero de documento.';
            }

            if (!empty($errorDetails)) {
                $error = 'Corrige los siguientes datos para registrar el deportista.';
            } elseif ($this->model()->createAthlete($payload)) {
                $this->redirect('deportistas');
            } else {
                $dbError = $this->model()->lastError();
                $error = $dbError !== '' ? ('No se pudo registrar el deportista: ' . $dbError) : 'No se pudo registrar el deportista.';
            }
        }

        $this->render('crear_deportista', [
            'categorias' => $this->model()->categories(),
            'niveles' => $this->model()->levels(),
            'error' => $error,
            'errorDetails' => $errorDetails,
        ]);
    }

    public function editAthlete(): void
    {
        $this->requireLogin();
        $id = (string)($_GET['id'] ?? '');
        $athlete = $this->model()->athleteById($id);
        if (!$athlete || !$this->canAccessAthlete($athlete)) {
            $this->redirect('deportistas');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = $this->athletePayload($athlete->foto);
            $payload['foto'] = $this->storeUploadedPhoto($athlete->foto);
            if ($payload['foto'] !== $athlete->foto) {
                $this->deleteOldPhoto($athlete->foto);
            }
            $this->model()->updateAthlete($id, $payload);
            $this->redirect('deportistas');
        }

        $this->render('editar_deportista', [
            'athlete' => $athlete,
            'categorias' => $this->model()->categories(),
            'niveles' => $this->model()->levels(),
        ]);
    }

    public function deleteAthlete(): void
    {
        $this->requireLogin();
        $this->requirePostRequest('deportistas');
        $this->requireCsrfToken();
        $id = $this->requestId();
        $athlete = $this->model()->athleteById($id);
        $role = (int)($_SESSION['rol'] ?? 0);
        $isOwner = $athlete && (int)$athlete->id_usuario === (int)$_SESSION['id_usuario'];
        if ($athlete && $this->canAccessAthlete($athlete) && ($role === 3 || $role === 4 || $isOwner)) {
            $this->model()->deleteAthlete($id);
        }
        $this->redirect('deportistas');
    }

    public function userAthletes(): void
    {
        $this->requireSuperAdmin();
        $this->render('ver_deportistas_usuario', [
            'deportistas' => $this->model()->athletesByUser((string)($_GET['id'] ?? '')),
        ]);
    }

    public function events(): void
    {
        $this->requireLogin();
        $this->render('eventos', [
            'eventos' => $this->model()->events($this->scopeSchoolId(), (int)($_SESSION['id_usuario'] ?? 0)),
        ]);
    }

    public function manageEvents(): void
    {
        $this->requireAdmin();
        $this->render('gestion_eventos', ['eventos' => $this->model()->managedEvents($this->scopeSchoolId())]);
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
            $this->model()->createEvent($this->eventPayload(), $this->scopeSchoolId());
            $this->redirect('gestion_eventos');
        }
        $this->render('crear_evento');
    }

    public function editEvent(): void
    {
        $this->requireAdmin();
        $id = (string)($_GET['id'] ?? '');
        $evento = $this->model()->eventById($id, $this->scopeSchoolId());
        if (!$evento) {
            $this->redirect('gestion-eventos');
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
            $this->model()->updateEvent($id, $this->eventPayload(), $this->scopeSchoolId());
            $this->redirect('gestion-eventos');
        }
        $this->render('editar_evento', ['evento' => $evento]);
    }

    public function toggleEvent(): void
    {
        $this->requireAdmin();
        $this->requirePostRequest('gestion_eventos');
        $this->requireCsrfToken();
        $this->model()->toggleEvent($this->requestId(), $this->scopeSchoolId());
        $this->redirect('gestion-eventos');
    }

    public function eventRegistrations(): void
    {
        $this->requireAdmin();
        $id = (string)($_GET['id'] ?? '');
        $evento = $this->model()->eventById($id, $this->scopeSchoolId());
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
            $this->redirect('reportes&error=forbidden_table');
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
            $this->redirect('login');
        }
    }

    private function requireAdmin(): void
    {
        if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] !== 3) {
            $this->redirect('panel');
        }
    }

    private function requireSuperAdmin(): void
    {
        if (!isset($_SESSION['rol']) || (int)$_SESSION['rol'] !== 4) {
            $this->redirect('panel');
        }
    }

    private function requireAdminOrSuperAdmin(): void
    {
        $rol = (int)($_SESSION['rol'] ?? 0);
        if (!in_array($rol, [3, 4], true)) {
            $this->redirect('panel');
        }
    }

    private function requireSchoolCreationAccess(): void
    {
        if (!isset($_SESSION['rol'], $_SESSION['id_usuario'])) {
            $this->redirect('login');
        }

        $rol = (int)$_SESSION['rol'];
        if ($rol === 4) {
            return;
        }

        if ($rol === 3 && $this->model()->userNeedsSchoolCreation((int)$_SESSION['id_usuario'])) {
            return;
        }

        $this->redirect('panel');
    }

    private function isAdminPendingSchoolCreation(): bool
    {
        if (!isset($_SESSION['rol'], $_SESSION['id_usuario'])) {
            return false;
        }

        return (int)$_SESSION['rol'] === 3 && $this->model()->userNeedsSchoolCreation((int)$_SESSION['id_usuario']);
    }

    private function requireReportAccess(): void
    {
        if (!isset($_SESSION['rol']) || !in_array((int)$_SESSION['rol'], [2, 3], true)) {
            $this->redirect('panel');
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
            $this->redirect('panel');
        }
    }

    private function requirePostRequest(string $fallbackRoute): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($fallbackRoute);
        }
    }

    private function requireCsrfToken(): void
    {
        if (!sm_csrf_verify((string)($_POST['_csrf'] ?? ''))) {
            http_response_code(419);
            exit('Token CSRF invalido.');
        }
    }

    private function requestId(): string
    {
        return trim((string)($_POST['id'] ?? $_GET['id'] ?? ''));
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

    private function emptySchoolFormData(): array
    {
        return [
            'nombre' => '',
            'disciplina' => '',
            'dia_pago' => '',
            'valor_inscripcion' => '',
            'valor_mensualidad' => '',
            'correo' => '',
            'telefono' => '',
            'direccion' => '',
            'escudo_path' => '',
            'firma_path' => '',
            'color_primario' => '#0d6efd',
            'color_secundario' => '#198754',
            'metodos_pago' => [
                [
                    'id_metodo' => '',
                    'nombre_entidad' => '',
                    'tipo' => 'offline',
                    'qr_path' => '',
                ],
            ],
        ];
    }

    private function canAccessAthlete(object $athlete): bool
    {
        $role = (int)($_SESSION['rol'] ?? 0);
        if ($role === 4) {
            return true;
        }
        if ($role === 1) {
            return (int)($athlete->id_usuario ?? 0) === (int)($_SESSION['id_usuario'] ?? 0);
        }
        if (in_array($role, [2, 3], true)) {
            $schoolId = (int)($_SESSION['usuario']['id_escuela'] ?? 0);
            return $schoolId > 0 && (int)($athlete->owner_school_id ?? 0) === $schoolId;
        }
        return false;
    }

    private function schoolPayload(): array
    {
        return [
            'nombre' => trim((string)($_POST['nombre'] ?? '')),
            'disciplina' => trim((string)($_POST['disciplina'] ?? '')),
            'dia_pago' => (int)($_POST['dia_pago'] ?? 0),
            'valor_inscripcion' => trim((string)($_POST['valor_inscripcion'] ?? '0')),
            'valor_mensualidad' => trim((string)($_POST['valor_mensualidad'] ?? '0')),
            'correo' => trim((string)($_POST['correo'] ?? '')),
            'telefono' => trim((string)($_POST['telefono'] ?? '')),
            'direccion' => trim((string)($_POST['direccion'] ?? '')),
            'escudo_path' => trim((string)($_POST['current_escudo_path'] ?? '')) ?: null,
            'firma_path' => trim((string)($_POST['firma_path'] ?? '')) ?: null,
            'color_primario' => $this->normalizeHexColor((string)($_POST['color_primario'] ?? ''), '#0d6efd'),
            'color_secundario' => $this->normalizeHexColor((string)($_POST['color_secundario'] ?? ''), '#198754'),
            'metodos_pago' => $this->paymentMethodsPayload(),
        ];
    }

    private function schoolFormData(array $payload): array
    {
        $metodosPago = $payload['metodos_pago'] ?? [];
        if (!is_array($metodosPago) || $metodosPago === []) {
            $metodosPago = $this->emptySchoolFormData()['metodos_pago'];
        }

        return array_merge($this->emptySchoolFormData(), [
            'nombre' => (string)($payload['nombre'] ?? ''),
            'disciplina' => (string)($payload['disciplina'] ?? ''),
            'dia_pago' => (string)($payload['dia_pago'] ?? ''),
            'valor_inscripcion' => (string)($payload['valor_inscripcion'] ?? ''),
            'valor_mensualidad' => (string)($payload['valor_mensualidad'] ?? ''),
            'correo' => (string)($payload['correo'] ?? ''),
            'telefono' => (string)($payload['telefono'] ?? ''),
            'direccion' => (string)($payload['direccion'] ?? ''),
            'escudo_path' => (string)($payload['escudo_path'] ?? ''),
            'firma_path' => (string)($payload['firma_path'] ?? ''),
            'color_primario' => (string)($payload['color_primario'] ?? '#0d6efd'),
            'color_secundario' => (string)($payload['color_secundario'] ?? '#198754'),
            'metodos_pago' => $metodosPago,
        ]);
    }

    private function validateSchoolPayload(array $payload): array
    {
        $errors = [];
        $emailValido = filter_var($payload['correo'], FILTER_VALIDATE_EMAIL) !== false;
        $diaPagoValido = (int)$payload['dia_pago'] >= 1 && (int)$payload['dia_pago'] <= 31;
        $inscripcionValida = is_numeric($payload['valor_inscripcion']) && (float)$payload['valor_inscripcion'] >= 0;
        $mensualidadValida = is_numeric($payload['valor_mensualidad']) && (float)$payload['valor_mensualidad'] >= 0;
        $telefonoValido = preg_match('/^\d{10}$/', (string)$payload['telefono']) === 1;

        if ($payload['nombre'] === '') {
            $errors[] = 'El nombre de la escuela es obligatorio.';
        }
        if ($payload['disciplina'] === '') {
            $errors[] = 'La disciplina es obligatoria.';
        }
        if (!$emailValido) {
            $errors[] = 'El correo oficial no tiene un formato valido.';
        }
        if (!$diaPagoValido) {
            $errors[] = 'El dia de pago debe estar entre 1 y 31.';
        }
        if (!$inscripcionValida) {
            $errors[] = 'El valor de inscripcion debe ser un numero mayor o igual a 0.';
        }
        if (!$mensualidadValida) {
            $errors[] = 'El valor de mensualidad debe ser un numero mayor o igual a 0.';
        }
        if (!$telefonoValido) {
            $errors[] = 'El telefono debe tener exactamente 10 digitos numericos.';
        }
        if ($payload['direccion'] === '') {
            $errors[] = 'La direccion es obligatoria.';
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', (string)$payload['color_primario'])) {
            $errors[] = 'El color primario no es valido.';
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', (string)$payload['color_secundario'])) {
            $errors[] = 'El color secundario no es valido.';
        }

        $paymentMethods = is_array($payload['metodos_pago'] ?? null) ? $payload['metodos_pago'] : [];
        if (count($paymentMethods) === 0) {
            $errors[] = 'Debes registrar al menos un metodo de pago para la escuela.';
        }
        $normalizedMethodNames = [];
        foreach ($paymentMethods as $method) {
            $name = trim((string)($method['nombre_entidad'] ?? ''));
            $type = trim((string)($method['tipo'] ?? ''));
            if ($name === '') {
                continue;
            }
            if (strlen($name) > 50) {
                $errors[] = 'El nombre de cada metodo de pago debe tener maximo 50 caracteres.';
                break;
            }
            if (strlen($type) > 50) {
                $errors[] = 'El tipo de cada metodo de pago debe tener maximo 50 caracteres.';
                break;
            }
            $normalizedName = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);
            if (isset($normalizedMethodNames[$normalizedName])) {
                $errors[] = 'No puedes registrar dos metodos de pago con el mismo nombre.';
                break;
            }
            $normalizedMethodNames[$normalizedName] = true;
        }

        return $errors;
    }

    private function paymentMethodsPayload(): array
    {
        $raw = is_array($_POST['metodos_pago'] ?? null) ? $_POST['metodos_pago'] : [];
        $ids = is_array($raw['id_metodo'] ?? null) ? $raw['id_metodo'] : [];
        $names = is_array($raw['nombre_entidad'] ?? null) ? $raw['nombre_entidad'] : [];
        $types = is_array($raw['tipo'] ?? null) ? $raw['tipo'] : [];
        $qrPaths = is_array($raw['qr_path'] ?? null) ? $raw['qr_path'] : [];
        $count = max(count($ids), count($names), count($types), count($qrPaths));
        $methods = [];

        for ($i = 0; $i < $count; $i++) {
            $name = trim((string)($names[$i] ?? ''));
            if ($name === '') {
                continue;
            }

            $id = trim((string)($ids[$i] ?? ''));
            $type = trim((string)($types[$i] ?? 'offline'));
            $qrPath = trim((string)($qrPaths[$i] ?? ''));

            $methods[] = [
                'id_metodo' => ctype_digit($id) ? (int)$id : '',
                'nombre_entidad' => $name,
                'tipo' => $type !== '' ? $type : 'offline',
                'qr_path' => $qrPath,
                '_file_index' => $i,
            ];
        }

        return $methods;
    }

    private function normalizeHexColor(string $value, string $fallback): string
    {
        $trimmed = trim($value);
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $trimmed) === 1) {
            return strtolower($trimmed);
        }
        return $fallback;
    }

    private function storeSchoolShield(string $currentPath = ''): ?string
    {
        if (empty($_FILES['escudo_file']['tmp_name']) || empty($_FILES['escudo_file']['name'])) {
            return $currentPath !== '' ? $currentPath : null;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $originalName = (string)$_FILES['escudo_file']['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return $currentPath !== '' ? $currentPath : null;
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
        $randomPart = bin2hex(random_bytes(4));
        $fileName = 'escudo_' . time() . '_' . $randomPart . '_' . $safeName;
        $targetDir = dirname(__DIR__, 2) . '/fotos/escudos';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $targetPath = $targetDir . '/' . $fileName;
        if (!move_uploaded_file($_FILES['escudo_file']['tmp_name'], $targetPath)) {
            return $currentPath !== '' ? $currentPath : null;
        }

        return 'fotos/escudos/' . $fileName;
    }

    /**
     * @param array<int,array<string,mixed>> $methods
     * @return array<int,array<string,mixed>>
     */
    private function storePaymentMethodQrs(array $methods): array
    {
        foreach ($methods as $position => $method) {
            $fileIndex = (int)($method['_file_index'] ?? $position);
            $tmpName = $_FILES['metodo_pago_qr']['tmp_name'][$fileIndex] ?? '';
            $originalName = $_FILES['metodo_pago_qr']['name'][$fileIndex] ?? '';
            if (!is_string($tmpName) || $tmpName === '' || !is_uploaded_file($tmpName)) {
                continue;
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $extension = strtolower(pathinfo((string)$originalName, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions, true)) {
                continue;
            }

            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$originalName);
            $randomPart = bin2hex(random_bytes(4));
            $fileName = 'metodo_' . time() . '_' . $randomPart . '_' . $safeName;
            $targetDir = dirname(__DIR__, 2) . '/fotos/metodos_pago';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }

            $targetPath = $targetDir . '/' . $fileName;
            if (move_uploaded_file($tmpName, $targetPath)) {
                $methods[$position]['qr_path'] = 'fotos/metodos_pago/' . $fileName;
            }
        }

        return $methods;
    }

    private function schoolActionError(string $code): string
    {
        return [
            'notfound' => 'La escuela solicitada no existe.',
            'delete' => 'No se pudo eliminar la escuela. Verifica que no tenga usuarios inscritos.',
        ][$code] ?? '';
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

    private function scopeSchoolId(): ?int
    {
        $role = (int)($_SESSION['rol'] ?? 0);
        if ($role === 4) {
            return null;
        }

        $schoolId = (int)($_SESSION['usuario']['id_escuela'] ?? 0);
        return $schoolId > 0 ? $schoolId : null;
    }
}
