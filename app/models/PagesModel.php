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
            $this->ensureSchoolCustomizationColumns();
            $this->ensureEventSchoolColumn();
            return;
        }

        if (class_exists('Database') && method_exists('Database', 'getConnection')) {
            /** @var PDO $pdo */
            $pdo = Database::getConnection();
            $this->db = $pdo;
            $this->ensureSchoolCustomizationColumns();
            $this->ensureEventSchoolColumn();
            return;
        }

        throw new RuntimeException('No se pudo inicializar la conexion de base de datos en PagesModel.');
    }

    private function ensureSchoolCustomizationColumns(): void
    {
        try {
            $primaryExistsStmt = $this->db->query("SHOW COLUMNS FROM escuelas LIKE 'color_primario'");
            $primaryExists = $primaryExistsStmt !== false && $primaryExistsStmt->fetch(PDO::FETCH_ASSOC) !== false;
            if (!$primaryExists) {
                $this->db->exec("ALTER TABLE escuelas ADD COLUMN color_primario CHAR(7) NOT NULL DEFAULT '#0d6efd' AFTER firma_path");
            }

            $secondaryExistsStmt = $this->db->query("SHOW COLUMNS FROM escuelas LIKE 'color_secundario'");
            $secondaryExists = $secondaryExistsStmt !== false && $secondaryExistsStmt->fetch(PDO::FETCH_ASSOC) !== false;
            if (!$secondaryExists) {
                $this->db->exec("ALTER TABLE escuelas ADD COLUMN color_secundario CHAR(7) NOT NULL DEFAULT '#198754' AFTER color_primario");
            }
        } catch (Throwable) {
            // Si falla, el sistema sigue funcionando con valores por defecto en vista.
        }
    }

    private function ensureEventSchoolColumn(): void
    {
        try {
            $existsStmt = $this->db->query("SHOW COLUMNS FROM eventos LIKE 'id_escuela'");
            $exists = $existsStmt !== false && $existsStmt->fetch(PDO::FETCH_ASSOC) !== false;
            if (!$exists) {
                $this->db->exec('ALTER TABLE eventos ADD COLUMN id_escuela INT(11) NULL AFTER estado');
            }
        } catch (Throwable) {
            // Mantener compatibilidad si no se puede alterar estructura.
        }
    }

    public function pendingUsers(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, e.nombre AS nombre_escuela,
                   f.id_factura AS factura_id,
                   f.numero_factura AS factura_numero,
                   f.fecha_emision AS factura_fecha,
                   f.monto AS factura_monto,
                   f.descripcion AS factura_descripcion,
                   f.tipo_pago AS factura_tipo_pago,
                   f.id_evento AS factura_id_evento,
                   f.id_deportista AS factura_id_deportista
            FROM usuarios u
            LEFT JOIN escuelas e ON e.id_escuela = u.id_escuela
            LEFT JOIN (
                SELECT f1.*
                FROM facturas f1
                INNER JOIN (
                    SELECT id, MAX(id_factura) AS max_factura
                    FROM facturas
                    GROUP BY id
                ) f2 ON f1.id = f2.id AND f1.id_factura = f2.max_factura
            ) f ON f.id = u.id_usuario
            WHERE u.id_rol = 3
              AND u.estado IN ('pendiente', 'pago_pendiente')
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approvedUsers(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, e.nombre AS nombre_escuela, COUNT(d.id_deportista) AS total_deportistas,
                   f.id_factura AS factura_id,
                   f.numero_factura AS factura_numero,
                   f.fecha_emision AS factura_fecha,
                   f.monto AS factura_monto,
                   f.descripcion AS factura_descripcion,
                   f.tipo_pago AS factura_tipo_pago
            FROM usuarios u
            LEFT JOIN escuelas e ON e.id_escuela = u.id_escuela
            LEFT JOIN deportistas d ON d.id_usuario = u.id_usuario
            LEFT JOIN (
                SELECT f1.*
                FROM facturas f1
                INNER JOIN (
                    SELECT id, MAX(id_factura) AS max_factura
                    FROM facturas
                    GROUP BY id
                ) f2 ON f1.id = f2.id AND f1.id_factura = f2.max_factura
            ) f ON f.id = u.id_usuario
            WHERE u.estado IN ('aprobado', 'deshabilitado', 'crear_escuela')
              AND u.id_rol = 3
            GROUP BY u.id_usuario
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function usersBySchool(int $schoolId): array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, e.nombre AS nombre_escuela, COUNT(d.id_deportista) AS total_deportistas
            FROM usuarios u
            LEFT JOIN escuelas e ON e.id_escuela = u.id_escuela
            LEFT JOIN deportistas d ON d.id_usuario = u.id_usuario
            WHERE u.id_escuela = :school_id
              AND u.id_rol IN (1, 2, 3)
            GROUP BY u.id_usuario
            ORDER BY u.nombres ASC, u.apellidos ASC
        ");
        $stmt->execute([':school_id' => $schoolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function athletesBySchool(int $schoolId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, c.nombre_cat, n.nombre, u.nombres AS nombre_usuario, u.apellidos AS apellido_usuario
            FROM deportistas d
            INNER JOIN categoria c ON d.id_categoria = c.id_categoria
            INNER JOIN nivel n ON d.id_nivel = n.id_nivel
            INNER JOIN usuarios u ON d.id_usuario = u.id_usuario
            WHERE u.id_escuela = :school_id
            ORDER BY d.nombres ASC, d.apellidos ASC
        ");
        $stmt->execute([':school_id' => $schoolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function userById(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id_usuario = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function userNeedsSchoolCreation(int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM usuarios WHERE id_usuario = ? AND id_rol = 3 AND estado = 'crear_escuela' LIMIT 1");
        $stmt->execute([$userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function assignSchoolToUser(int $userId, int $schoolId): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET id_escuela = ?, estado = 'aprobado' WHERE id_usuario = ? AND id_rol = 3");
        return $stmt->execute([$schoolId, $userId]);
    }

    public function categories(): array
    {
        return $this->db->query('SELECT * FROM categoria')->fetchAll(PDO::FETCH_OBJ);
    }

    public function levels(): array
    {
        return $this->db->query('SELECT * FROM nivel')->fetchAll(PDO::FETCH_OBJ);
    }

    public function categoryExists(string $id): bool
    {
        if ($id === '' || !ctype_digit($id)) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT 1 FROM categoria WHERE id_categoria = ?');
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    public function levelExists(string $id): bool
    {
        if ($id === '' || !ctype_digit($id)) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT 1 FROM nivel WHERE id_nivel = ?');
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    public function athleteExists(string $id): bool
    {
        if ($id === '' || !ctype_digit($id)) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT 1 FROM deportistas WHERE id_deportista = ?');
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    public function schools(): array
    {
        $stmt = $this->db->query('SELECT id_escuela, nombre, disciplina FROM escuelas ORDER BY nombre ASC');
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function allSchools(): array
    {
        $stmt = $this->db->query("
            SELECT e.*,
                   (SELECT COUNT(*) FROM usuarios u WHERE u.id_escuela = e.id_escuela) AS total_usuarios
            FROM escuelas e
            ORDER BY e.nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function schoolById(string $id): ?object
    {
        if ($id === '' || !ctype_digit($id)) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM escuelas WHERE id_escuela = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
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
                (id_escuela, nombre, disciplina, dia_pago, valor_inscripcion, valor_mensualidad, correo, pass_app, telefono, direccion, escudo_path, firma_path, color_primario, color_secundario)
                VALUES
                (:id_escuela, :nombre, :disciplina, :dia_pago, :valor_inscripcion, :valor_mensualidad, :correo, :pass_app, :telefono, :direccion, :escudo_path, :firma_path, :color_primario, :color_secundario)
            ");

            $ok = $stmt->execute([
                ':id_escuela' => $nextId,
                ':nombre' => $data['nombre'],
                ':disciplina' => $data['disciplina'],
                ':dia_pago' => $data['dia_pago'],
                ':valor_inscripcion' => $data['valor_inscripcion'],
                ':valor_mensualidad' => $data['valor_mensualidad'],
                ':correo' => $data['correo'],
                ':pass_app' => (string)($data['pass_app'] ?? ''),
                ':telefono' => $data['telefono'],
                ':direccion' => $data['direccion'],
                ':escudo_path' => $data['escudo_path'],
                ':firma_path' => $data['firma_path'],
                ':color_primario' => $data['color_primario'],
                ':color_secundario' => $data['color_secundario'],
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

    /**
     * @param array<string,mixed> $data
     */
    public function updateSchool(string $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE escuelas
                SET nombre = :nombre,
                    disciplina = :disciplina,
                    dia_pago = :dia_pago,
                    valor_inscripcion = :valor_inscripcion,
                    valor_mensualidad = :valor_mensualidad,
                    correo = :correo,
                    pass_app = :pass_app,
                    telefono = :telefono,
                    direccion = :direccion,
                    escudo_path = :escudo_path,
                    firma_path = :firma_path,
                    color_primario = :color_primario,
                    color_secundario = :color_secundario
                WHERE id_escuela = :id_escuela
            ");

            $ok = $stmt->execute([
                ':id_escuela' => $id,
                ':nombre' => $data['nombre'],
                ':disciplina' => $data['disciplina'],
                ':dia_pago' => $data['dia_pago'],
                ':valor_inscripcion' => $data['valor_inscripcion'],
                ':valor_mensualidad' => $data['valor_mensualidad'],
                ':correo' => $data['correo'],
                ':pass_app' => (string)($data['pass_app'] ?? ''),
                ':telefono' => $data['telefono'],
                ':direccion' => $data['direccion'],
                ':escudo_path' => $data['escudo_path'],
                ':firma_path' => $data['firma_path'],
                ':color_primario' => $data['color_primario'],
                ':color_secundario' => $data['color_secundario'],
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
            return true;
        } catch (Throwable $e) {
            $sqlState = $e instanceof PDOException ? (string)$e->getCode() : '';
            $this->lastError = $this->mapSchoolDbError($sqlState, '', $e->getMessage());
            return false;
        }
    }

    public function deleteSchool(string $id): bool
    {
        try {
            if ($this->countSchoolUsers($id) > 0) {
                $this->lastError = 'No puedes eliminar una escuela con usuarios inscritos.';
                return false;
            }

            $stmt = $this->db->prepare('DELETE FROM escuelas WHERE id_escuela = ?');
            $ok = $stmt->execute([$id]);
            $this->lastError = $ok ? '' : 'No se pudo eliminar la escuela.';
            return $ok;
        } catch (Throwable $e) {
            $this->lastError = $this->mapSchoolDbError($e instanceof PDOException ? (string)$e->getCode() : '', '', $e->getMessage());
            return false;
        }
    }

    public function countSchoolUsers(string $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuarios WHERE id_escuela = ?');
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
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
            $event->user_registered_athlete_ids = $this->userRegisteredAthleteIdsInEvent((int)$event->id_evento, $userId);
            $event->inscrito = $this->areAllUserAthletesRegisteredInEvent((int)$event->id_evento, $userId);
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

        if ($role === 3) {
            $stmt = $this->db->prepare($sql . '
                WHERE u.id_escuela = (
                    SELECT id_escuela
                    FROM usuarios
                    WHERE id_usuario = :id_usuario
                    LIMIT 1
                )');
            $stmt->execute([':id_usuario' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        if ($role === 2) {
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

    public function events(?int $schoolId = null): array
    {
        if ($schoolId !== null && $schoolId > 0) {
            $stmt = $this->db->prepare('SELECT * FROM eventos WHERE id_escuela = :id_escuela ORDER BY fecha DESC, id_evento DESC');
            $stmt->execute([':id_escuela' => $schoolId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->db->query('SELECT * FROM eventos ORDER BY fecha DESC, id_evento DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function managedEvents(?int $schoolId = null): array
    {
        if ($schoolId !== null && $schoolId > 0) {
            $stmt = $this->db->prepare('SELECT * FROM eventos WHERE id_escuela = :id_escuela ORDER BY fecha DESC');
            $stmt->execute([':id_escuela' => $schoolId]);
        } else {
            $stmt = $this->db->query('SELECT * FROM eventos ORDER BY fecha DESC');
        }
        $events = $stmt->fetchAll(PDO::FETCH_OBJ);
        foreach ($events as $event) {
            $event->total_inscritos = $this->countEventRegistrations((int)$event->id_evento);
        }
        return $events;
    }

    public function eventById(string $id, ?int $schoolId = null): ?object
    {
        if ($schoolId !== null && $schoolId > 0) {
            $stmt = $this->db->prepare('SELECT * FROM eventos WHERE id_evento = ? AND id_escuela = ?');
            $stmt->execute([$id, $schoolId]);
        } else {
            $stmt = $this->db->prepare('SELECT * FROM eventos WHERE id_evento = ?');
            $stmt->execute([$id]);
        }
        return $stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function createEvent(array $data, ?int $schoolId = null): void
    {
        $nextId = (int)$this->db->query('SELECT COALESCE(MAX(id_evento), 0) + 1 FROM eventos')->fetchColumn();
        $stmt = $this->db->prepare('INSERT INTO eventos (id_evento, titulo, fecha, id_rol, tipo_evento, costo, cuotas, id_escuela) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$nextId, $data['titulo'], $data['fecha'], $data['id_rol'], $data['tipo_evento'], $data['costo'], $data['cuotas'], $schoolId]);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function updateEvent(string $id, array $data, ?int $schoolId = null): void
    {
        if ($schoolId !== null && $schoolId > 0) {
            $stmt = $this->db->prepare('UPDATE eventos SET titulo = ?, fecha = ?, id_rol = ?, tipo_evento = ?, costo = ?, cuotas = ? WHERE id_evento = ? AND id_escuela = ?');
            $stmt->execute([$data['titulo'], $data['fecha'], $data['id_rol'], $data['tipo_evento'], $data['costo'], $data['cuotas'], $id, $schoolId]);
            return;
        }
        $stmt = $this->db->prepare('UPDATE eventos SET titulo = ?, fecha = ?, id_rol = ?, tipo_evento = ?, costo = ?, cuotas = ? WHERE id_evento = ?');
        $stmt->execute([$data['titulo'], $data['fecha'], $data['id_rol'], $data['tipo_evento'], $data['costo'], $data['cuotas'], $id]);
    }

    public function toggleEvent(string $id, ?int $schoolId = null): void
    {
        if ($schoolId !== null && $schoolId > 0) {
            $stmt = $this->db->prepare('UPDATE eventos SET estado = IF(estado = 1, 0, 1) WHERE id_evento = ? AND id_escuela = ?');
            $stmt->execute([$id, $schoolId]);
            return;
        }
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

    private function areAllUserAthletesRegisteredInEvent(int $eventId, int $userId): bool
    {
        $totalAthletesStmt = $this->db->prepare('SELECT COUNT(*) FROM deportistas WHERE id_usuario = ?');
        $totalAthletesStmt->execute([$userId]);
        $totalAthletes = (int)$totalAthletesStmt->fetchColumn();

        if ($totalAthletes <= 0) {
            return false;
        }

        $registeredAthletesStmt = $this->db->prepare("
            SELECT COUNT(DISTINCT i.id_deportista)
            FROM inscripciones i
            INNER JOIN deportistas d ON d.id_deportista = i.id_deportista
            WHERE i.id_evento = ?
              AND d.id_usuario = ?
        ");
        $registeredAthletesStmt->execute([$eventId, $userId]);
        $registeredAthletes = (int)$registeredAthletesStmt->fetchColumn();

        return $registeredAthletes >= $totalAthletes;
    }

    /**
     * @return array<int,string>
     */
    private function userRegisteredAthleteIdsInEvent(int $eventId, int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT i.id_deportista
            FROM inscripciones i
            INNER JOIN deportistas d ON d.id_deportista = i.id_deportista
            WHERE i.id_evento = ?
              AND d.id_usuario = ?
        ");
        $stmt->execute([$eventId, $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return array_map('strval', is_array($rows) ? $rows : []);
    }
}
