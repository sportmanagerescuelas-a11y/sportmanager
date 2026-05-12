<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Deportista.php';
require_once __DIR__ . '/../models/Asistencia.php';
require_once __DIR__ . '/../core/Database.php';

class DeportistasController extends Controller
{
    private function requireTrainerSession(): void
    {
        if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
            header('Location: index.php?url=login');
            exit();
        }

        $rol = (int)($_SESSION['rol'] ?? 0);
        if (!in_array($rol, [2, 3], true)) {
            header('Location: index.php?url=dashboard');
            exit();
        }
    }

    public function index(): void
    {
        $this->requireTrainerSession();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $filters = [
            'search' => trim((string)($_GET['search'] ?? '')),
            'categoria' => trim((string)($_GET['categoria'] ?? '')),
            'jornada' => trim((string)($_GET['jornada'] ?? '')),
        ];

        $data = Deportista::paginate($page, $perPage, $filters);
        $totalPages = (int)ceil($data['total'] / max(1, $perPage));

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
        ]);
    }

    public function asistenciaHijos(): void
    {
        if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
            header('Location: index.php?url=login');
            exit();
        }

        if ((int)($_SESSION['rol'] ?? 0) !== 1) {
            header('Location: index.php?url=dashboard');
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
            http_response_code(405);
            echo 'Metodo no permitido.';
            return;
        }

        $fecha = isset($_POST['fecha']) ? trim((string)$_POST['fecha']) : '';
        $fechaValida = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
            http_response_code(400);
            echo 'Fecha invalida.';
            return;
        }

        $payload = $_POST['payload'] ?? '';
        $rows = json_decode($payload, true);
        if (!is_array($rows) || empty($rows)) {
            http_response_code(400);
            echo 'Datos invalidos.';
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
            http_response_code(400);
            echo 'No hay datos para guardar.';
            return;
        }

        $existentes = Asistencia::findExisting($fecha, $ids);
        if (!empty($existentes)) {
            $count = count($existentes);
            $fechaParam = urlencode($fecha);
            header('Location: index.php?url=registrar-asistencia&error=duplicado&count=' . $count . '&fecha=' . $fechaParam);
            return;
        }

        Asistencia::insertMany($clean, $fecha);

        header('Location: index.php?url=registrar-asistencia&ok=1');
    }
}

