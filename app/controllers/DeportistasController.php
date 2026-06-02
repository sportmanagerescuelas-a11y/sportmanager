<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Deportista.php';
require_once __DIR__ . '/../models/Asistencia.php';
require_once __DIR__ . '/../core/Database.php';

class DeportistasController extends Controller
{
    private function renderStatusCard(string $code, string $message, string $title = 'Fuera de juego'): void
    {
        http_response_code((int)$code);
        $backUrl = 'index.php';
        $backLabel = 'Volver al inicio';
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/pages/error_status.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    private function requireTrainerSession(): void
    {
        if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
            header('Location: login');
            exit();
        }

        $rol = (int)($_SESSION['rol'] ?? 0);
        if (!in_array($rol, [2, 3], true)) {
            header('Location: dashboard');
            exit();
        }
    }

    public function index(): void
    {
        $this->requireTrainerSession();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $fechaSeleccionada = trim((string)($_GET['fecha'] ?? ''));
        $fechaValida = DateTime::createFromFormat('Y-m-d', $fechaSeleccionada);
        if ($fechaSeleccionada === '' || !$fechaValida || $fechaValida->format('Y-m-d') !== $fechaSeleccionada) {
            $fechaSeleccionada = (new DateTimeImmutable('now', new DateTimeZone('America/Bogota')))->format('Y-m-d');
        }
        $filters = [
            'search' => trim((string)($_GET['search'] ?? '')),
            'categoria' => trim((string)($_GET['categoria'] ?? '')),
            'jornada' => trim((string)($_GET['jornada'] ?? '')),
        ];

        $data = Deportista::paginate($page, $perPage, $filters);
        $totalPages = (int)ceil($data['total'] / max(1, $perPage));
        $existingAttendance = Asistencia::forDateAndIds($fechaSeleccionada, array_map(
            static fn($row): int => (int)($row['id_deportista'] ?? 0),
            is_array($data['rows']) ? $data['rows'] : []
        ));

        $this->render('deportistas/registrar_asistencia', [
            'title' => 'Deportistas',
            'rows' => $data['rows'],
            'total' => $data['total'],
            'page' => max(1, $page),
            'perPage' => max(1, $perPage),
            'totalPages' => max(1, $totalPages),
            'filters' => $filters,
            'categorias' => Deportista::categories(),
            'jornadas' => Deportista::jornadas(),
            'ok' => isset($_GET['ok']),
            'error' => $_GET['error'] ?? null,
            'errorCount' => isset($_GET['count']) ? (int)$_GET['count'] : 0,
            'errorFecha' => $_GET['fecha'] ?? null,
            'fechaSeleccionada' => $fechaSeleccionada,
            'existingAttendance' => $existingAttendance,
        ]);
    }

    public function asistenciaHijos(): void
    {
        if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
            header('Location: login');
            exit();
        }

        if ((int)($_SESSION['rol'] ?? 0) !== 1) {
            header('Location: dashboard');
            exit();
        }

        $userId = (int)$_SESSION['id_usuario'];
        $fechas = Asistencia::datesForGuardian($userId);
        $fecha = trim((string)($_GET['fecha'] ?? ''));
        if ($fecha === '' || !in_array($fecha, $fechas, true)) {
            $fecha = $fechas[0] ?? '';
        }

        $rows = $fecha !== '' ? Asistencia::forGuardianByDate($userId, $fecha) : [];

        $this->render('deportistas/asistencia_hijos', [
            'title' => 'Asistencias de mis hijos',
            'fechas' => $fechas,
            'fecha' => $fecha,
            'rows' => $rows,
        ]);
    }

    public function guardar(): void
    {
        $this->requireTrainerSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->renderStatusCard('405', 'Metodo no permitido para esta accion.', 'Accion no permitida');
            return;
        }

        $fecha = isset($_POST['fecha']) ? trim((string)$_POST['fecha']) : '';
        $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
            $this->renderStatusCard('400', 'La fecha enviada no es valida.', 'Solicitud invalida');
            return;
        }

        $payload = $_POST['payload'] ?? '';
        $rows = json_decode($payload, true);
        if (!is_array($rows) || empty($rows)) {
            $this->renderStatusCard('400', 'Los datos enviados no son validos.', 'Solicitud invalida');
            return;
        }

        $clean = [];
        $ids = [];
        foreach ($rows as $row) {
            $id = isset($row['id_deportista']) ? (int)$row['id_deportista'] : 0;
            $estado = isset($row['estado']) ? trim((string)$row['estado']) : '';
            $comentario = isset($row['comentario']) ? trim((string)$row['comentario']) : '';
            if ($id <= 0 || $estado === '') {
                continue;
            }
            $ids[] = $id;
            $clean[] = [
                'id_deportista' => $id,
                'estado' => $estado,
                'comentario' => $comentario === '' ? null : $comentario,
            ];
        }

        if (empty($clean)) {
            $this->renderStatusCard('400', 'No hay datos para guardar.', 'Solicitud invalida');
            return;
        }

        Asistencia::replaceMany($clean, $fecha);

        header('Location: registrar-asistencia&ok=1&fecha=' . urlencode($fecha));
    }
}

