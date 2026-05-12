<?php

class User
{
    private PDO $db;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . "/config/conexion.php";
        $this->db = $conexion;
    }

    public static function existsByEmail(string $email): bool
    {
        $model = new self();
        return (bool)$model->findByEmail($email);
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function create(array $data): bool
    {
        $model = new self();

        $document = preg_replace('/\D+/', '', (string)($data['id_usuario'] ?? $data['dni'] ?? '')) ?? '';
        if ($document === '' || !filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($model->findByEmail((string)$data['email'])) {
            return false;
        }

        $fullName = trim((string)($data['nombre'] ?? ''));
        $names = trim((string)($data['nombres'] ?? $fullName));
        $lastNames = trim((string)($data['apellidos'] ?? ''));
        if ($lastNames === '' && $fullName !== '') {
            $parts = preg_split('/\s+/', $fullName) ?: [];
            $names = (string)($parts[0] ?? $fullName);
            $lastNames = trim(implode(' ', array_slice($parts, 1)));
        }

        $password = (string)($data['password'] ?? '');
        if ($password === '') {
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        }

        $stmt = $model->db->prepare(
            "INSERT INTO usuarios
             (id_usuario, tipo_documento, id_escuela, nombres, apellidos, email, contrasena, telefono, id_rol, registros_disponibles, habilitado, estado)
             VALUES
             (:id_usuario, :tipo_documento, :id_escuela, :nombres, :apellidos, :email, :contrasena, :telefono, :id_rol, :registros_disponibles, :habilitado, :estado)"
        );

        try {
            return $stmt->execute([
                ':id_usuario' => (int)$document,
                ':tipo_documento' => (string)($data['tipo_documento'] ?? 'CC'),
                ':id_escuela' => isset($data['id_escuela']) ? (int)$data['id_escuela'] : 1,
                ':nombres' => $names !== '' ? $names : 'Usuario',
                ':apellidos' => $lastNames,
                ':email' => (string)$data['email'],
                ':contrasena' => $password,
                ':telefono' => (string)($data['telefono'] ?? ''),
                ':id_rol' => isset($data['id_rol']) ? (int)$data['id_rol'] : 1,
                ':registros_disponibles' => isset($data['cantidad']) ? (int)$data['cantidad'] : null,
                ':habilitado' => 1,
                ':estado' => 'aprobado',
            ]);
        } catch (PDOException $e) {
            error_log('No se pudo crear usuario: ' . $e->getMessage());
            return false;
        }
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveToken(string $email, string $token, string $expira): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios 
             SET reset_token = :token, token_expiry = :expiry
             WHERE email = :email"
        );
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expiry", $expira);
        $stmt->bindParam(":email", $email);
        return $stmt->execute();
    }

    public function findByToken(string $token): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios
             WHERE reset_token = :token
             AND token_expiry > NOW()
             LIMIT 1"
        );
        $stmt->bindParam(":token", $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword(string $token, string $password): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET contrasena = :password,
                 reset_token = NULL,
                 token_expiry = NULL
             WHERE reset_token = :token"
        );
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":token", $token);
        return $stmt->execute();
    }
}

if (!class_exists('App\\Models\\User', false)) {
    class_alias(User::class, 'App\\Models\\User');
}
