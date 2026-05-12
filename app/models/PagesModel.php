<?php

class PagesModel
{
    private PDO $db;
    private string $lastError = '';

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/conexion.php';
        if (isset($conexion) && $conexion instanceof PDO) {
            $this->db = $conexion;
            return;
        }

        if (class_exists('Database') && method_exists('Database', 'getConnection')) {
            /** @var PDO $pdo */
            $pdo = Database::getConnection();
            $this->db = $pdo;
            return;
        }

        throw new RuntimeException('No se pudo inicializar la conexion de base de datos en PagesModel.');
    }

    public function pendingUsers(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, e.nombre AS nombre_escuela
            FROM usuarios u
            LEFT JOIN escuelas e ON e.id_escuela = u.id_escuela
            WHERE u.estado = 'pendiente'
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approvedUsers(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, e.nombre AS nombre_escuela, COUNT(d.id_deportista) AS total_deportistas
            FROM usuarios u
            LEFT JOIN escuelas e ON e.id_escuela = u.id_escuela
            LEFT JOIN deportistas d ON d.id_usuario = u.id_usuario
            WHERE u.estado IN ('aprobado', 'deshabilitado')
            GROUP BY u.id_usuario
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function userById(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id_usuario = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function categories(): array
    {
        return $this->db->query('SELECT * FROM categoria')->fetchAll(PDO::FETCH_OBJ);
    }

    public function levels(): array
    {
        return $this->db->query('SELECT * FROM nivel')->fetchAll(PDO::FETCH_OBJ);
    }

    public function schools(): array
    {
        $stmt = $this->db->query('SELECT id_escuela, nombre, disciplina FROM escuelas ORDER BY nombre ASC');
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function createSchool(array $data): int|false
    {
        try {
            $nextId = (int)$this->db->query('SELECT COALESCE(MAX(id_escuela), 0) + 1 FROM escuelas')->fetchColumn();
            $stmt = $this->db->prepare("
                INSERT INTO escuelas
                (id_escuela, nombre, disciplina, dia_pago, valor_inscripcion, valor_mensualidad, correo, pass_app, telefono, direccion, escudo_path, firma_path)
                VALUES
                (:id_escuela, :nombre, :disciplina, :dia_pago, :valor_inscripcion, :valor_mensualidad, :correo, :pass_app, :telefono, :direccion, :escudo_path, :firma_path)
            ");

            $ok = $stmt->execute([
                ':id_escuela' => $nextId,
                ':nombre' => $data['nombre'],
                ':disciplina' => $data['disciplina'],
                ':dia_pago' => $data['dia_pago'],
                ':valor_inscripcion' => $data['valor_inscripcion'],
                ':valor_mensualidad' => $data['valor_mensualidad'],
                ':correo' => $data['correo'],
                ':pass_app' => $data['pass_app'],
                ':telefono' => $data['telefono'],
                ':direccion' => $data['direccion'],
                ':escudo_path' => $data['escudo_path'],
                ':firma_path' => $data['firma_path'],
            ]);

            if (!$ok) {
                $info = $stmt->errorInfo();
                $this->lastError = $this->mapSchoolDbError(
                    isset($info[0]) ? (string)$info[0] : '',
                    isset($info[1]) ? (string)$info[1] : '',
                    isset($info[2]) ? (string)$info[2] : ''
                );
                return false;
            }

            $this->lastError = '';
            return $nextId;
        } catch (Throwable $e) {
            $sqlState = $e instanceof PDOException ? (string)$e->getCode() : '';
            $this->lastError = $this->mapSchoolDbError($sqlState, '', $e->getMessage());
            return false;
        }
    }

    private function mapSchoolDbError(string $sqlState, string $driverCode, string $rawMessage): string
    {
        $message = strtolower($rawMessage);
        if ($sqlState === '23000' || str_contains($message, 'duplicate') || str_contains($message, 'duplicada')) {
            if (str_contains($message, 'correo')) {
                return 'El correo de la escuela ya existe. Usa otro correo.';
            }
            return 'Ya existe un registro de escuela con esos datos unicos.';
        }

        if (str_contains($message, 'cannot be null') || str_contains($message, 'null')) {
            return 'Faltan campos obligatorios para crear la escuela.';
        }

        if (str_contains($message, 'foreign key')) {
            return 'Error de relacion en base de datos al crear la escuela.';
        }

        $suffix = $driverCode !== '' ? (' (codigo ' . $driverCode . ')') : '';
        return 'Error de base de datos al crear la escuela' . $suffix . '.';
    }

    public function dashboardData(int $userId, int $role): array
    {
        if ($role === 1) {
            $stmt = $this->db->prepare('SELECT * FROM eventos WHERE estado = 1 AND fecha >= CURDATE() ORDER BY fecha ASC, id_evento ASC');
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare('SELECT * FROM eventos WHERE id_rol = ? OR id_rol IS NULL ORDER BY fecha ASC, id_evento ASC');
            $stmt->execute([$role]);
        }
        $events = $stmt->fetchAll(PDO::FETCH_OBJ);

        $athletesStmt = $this->db->prepare('SELECT * FROM deportistas WHERE id_usuario = ?');
        $athletesStmt->execute([$userId]);
        $athletes = $athletesStmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($events as $event) {
            $event->total_inscritos = $this->countEventRegistrations((int)$event->id_evento);
            $event->inscrito = $this->isUserRegisteredInEvent((int)$event->id_evento, $userId);
        }

        return [
            'events' => $events,
            'athletes' => $athletes,
        ];
    }

    public function athletesForRole(int $userId, int $role): array
    {
        $sql = "
            SELECT d.*, c.nombre_cat, n.nombre, u.nombres AS nombre_usuario, e.nombre_estado
            FROM deportistas d
            INNER JOIN categoria c ON d.id_categoria = c.id_categoria
            INNER JOIN nivel n ON d.id_nivel = n.id_nivel
            INNER JOIN usuarios u ON d.id_usuario = u.id_usuario
            INNER JOIN estados e ON d.id_estado = e.id_estado
        ";

        if (in_array($role, [2, 3], true)) {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);
        }

        $stmt = $this->db->prepare($sql . ' WHERE d.id_usuario = :id_usuario');
        $stmt->execute([':id_usuario' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function athleteById(string $id): ?object
    {
        $stmt = $this->db->prepare('SELECT * FROM deportistas WHERE id_deportista = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    public function athletesByUser(string $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, c.nombre_cat, n.nombre
            FROM deportistas d
            INNER JOIN categoria c ON d.id_categoria = c.id_categoria
            INNER JOIN nivel n ON d.id_nivel = n.id_nivel
            WHERE d.id_usuario = :id_usuario
        ");
        $stmt->execute([':id_usuario' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function createAthlete(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO deportistas
            (tipo_documento, id_deportista, foto, nombres, apellidos, fecha_nacimiento, jornada, id_categoria, id_usuario, id_nivel, genero)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        try {
            $ok = $stmt->execute([
                $data['tipo_documento'],
                $data['id_deportista'],
                $data['foto'],
                $data['nombres'],
                $data['apellidos'],
                $data['fecha_nacimiento'],
                $data['jornada'],
                $data['id_categoria'],
                $data['id_usuario'],
                $data['id_nivel'],
                $data['genero'],
            ]);
            if (!$ok) {
                $info = $stmt->errorInfo();
                $this->lastError = isset($info[2]) ? (string)$info[2] : 'Error desconocido al crear deportista.';
            } else {
                $this->lastError = '';
            }
            return $ok;
        } catch (Throwable $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function lastError(): string
    {
        return $this->lastError;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function updateAthlete(string $currentId, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE deportistas
            SET tipo_documento = ?, foto = ?, id_deportista = ?, nombres = ?, apellidos = ?, fecha_nacimiento = ?,
                jornada = ?, id_categoria = ?, id_nivel = ?, genero = ?
            WHERE id_deportista = ?
        ");

        $stmt->execute([
            $data['tipo_documento'],
            $data['foto'],
            $data['id_deportista'],
            $data['nombres'],
            $data['apellidos'],
            $data['fecha_nacimiento'],
            $data['jornada'],
            $data['id_categoria'],
            $data['id_nivel'],
            $data['genero'],
            $currentId,
        ]);
    }

    public function deleteAthlete(string $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM deportistas WHERE id_deportista = ?');
        $stmt->execute([$id]);
    }

    public function events(): array
    {
        return $this->db->query('SELECT * FROM eventos')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function managedEvents(): array
    {
        $stmt = $this->db->query('SELECT * FROM eventos ORDER BY fecha DESC');
        $events = $stmt->fetchAll(PDO::FETCH_OBJ);
        foreach ($events as $event) {
            $event->total_inscritos = $this->countEventRegistrations((int)$event->id_evento);
        }
        return $events;
    }

    public function eventById(string $id): ?object
    {
        $stmt = $this->db->prepare('SELECT * FROM eventos WHERE id_evento = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function createEvent(array $data): void
    {
        $nextId = (int)$this->db->query('SELECT COALESCE(MAX(id_evento), 0) + 1 FROM eventos')->fetchColumn();
        $stmt = $this->db->prepare('INSERT INTO eventos (id_evento, titulo, fecha, id_rol, tipo_evento, costo, cuotas) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$nextId, $data['titulo'], $data['fecha'], $data['id_rol'], $data['tipo_evento'], $data['costo'], $data['cuotas']]);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function updateEvent(string $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE eventos SET titulo = ?, fecha = ?, id_rol = ?, tipo_evento = ?, costo = ?, cuotas = ? WHERE id_evento = ?');
        $stmt->execute([$data['titulo'], $data['fecha'], $data['id_rol'], $data['tipo_evento'], $data['costo'], $data['cuotas'], $id]);
    }

    public function toggleEvent(string $id): void
    {
        $stmt = $this->db->prepare('UPDATE eventos SET estado = IF(estado = 1, 0, 1) WHERE id_evento = ?');
        $stmt->execute([$id]);
    }

    public function eventRegistrations(string $eventId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.nombres AS nombre_dep, d.apellidos AS apellido_dep,
                   u.nombres AS nombre_user, u.apellidos AS apellido_user
            FROM inscripciones i
            JOIN deportistas d ON i.id_deportista = d.id_deportista
            JOIN usuarios u ON i.id_usuario = u.id_usuario
            WHERE i.id_evento = ?
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function registerInEvent(int $eventId, int $userId, string $athleteId): string
    {
        if ($eventId <= 0 || !$this->isEventOpenForEnrollment($eventId)) {
            return 'Evento invalido';
        }

        $resolvedAthleteId = $this->resolveAthleteIdForUser($athleteId, $userId);
        if ($resolvedAthleteId === '') {
            return 'Debe seleccionar un deportista';
        }

        $athleteOwner = $this->db->prepare('SELECT 1 FROM deportistas WHERE id_deportista = ? AND id_usuario = ?');
        $athleteOwner->execute([$resolvedAthleteId, $userId]);
        if (!$athleteOwner->fetchColumn()) {
            return 'El deportista seleccionado no pertenece a tu cuenta.';
        }

        $check = $this->db->prepare('SELECT 1 FROM inscripciones WHERE id_evento = ? AND id_deportista = ?');
        $check->execute([$eventId, $resolvedAthleteId]);
        if ($check->fetchColumn()) {
            return 'ya';
        }

        $stmt = $this->db->prepare('INSERT INTO inscripciones (id_evento, id_usuario, id_deportista) VALUES (?, ?, ?)');
        $stmt->execute([$eventId, $userId, $resolvedAthleteId]);
        return 'ok';
    }

    private function isEventOpenForEnrollment(int $eventId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM eventos WHERE id_evento = ? AND estado = 1 AND fecha >= CURDATE()');
        $stmt->execute([$eventId]);
        return (bool)$stmt->fetchColumn();
    }

    private function resolveAthleteIdForUser(string $athleteInput, int $userId): string
    {
        $candidate = trim($athleteInput);
        if ($candidate !== '') {
            if (ctype_digit($candidate)) {
                return $candidate;
            }

            // Fallback when frontend sends the athlete full name instead of the id.
            $byName = $this->db->prepare("
                SELECT id_deportista
                FROM deportistas
                WHERE id_usuario = ?
                  AND TRIM(CONCAT(nombres, ' ', apellidos)) = ?
                LIMIT 2
            ");
            $byName->execute([$userId, $candidate]);
            $rows = $byName->fetchAll(PDO::FETCH_COLUMN);
            if (count($rows) === 1) {
                return (string)$rows[0];
            }
        }

        // If the user has only one athlete, auto-pick it to avoid false negatives.
        $uniqueAthlete = $this->db->prepare('SELECT id_deportista FROM deportistas WHERE id_usuario = ? ORDER BY id_deportista ASC LIMIT 2');
        $uniqueAthlete->execute([$userId]);
        $ids = $uniqueAthlete->fetchAll(PDO::FETCH_COLUMN);
        if (count($ids) === 1) {
            return (string)$ids[0];
        }

        return '';
    }

    private function countEventRegistrations(int $eventId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM inscripciones WHERE id_evento = ?');
        $stmt->execute([$eventId]);
        return (int)$stmt->fetchColumn();
    }

    private function isUserRegisteredInEvent(int $eventId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM inscripciones WHERE id_evento = ? AND id_usuario = ?');
        $stmt->execute([$eventId, $userId]);
        return (bool)$stmt->fetchColumn();
    }
}
