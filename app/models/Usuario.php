<?php

class Usuario
{

    private PDO $conexion;
    private string $lastError = '';
    private string $lastPasswordHash = '';

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function login(string $email, string $password)
    {

        $sql = $this->conexion->prepare("SELECT * FROM usuarios WHERE email = :email");
        $sql->bindParam(":email", $email);
        $sql->execute();

        if ($sql->rowCount() > 0) {

            $usuario = $sql->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $usuario['contrasena'])) {

                return $usuario;
            } else {

                return false;
            }
        } else {
            return false;
        }
    }

    public function registrar($id_usuario, string $tipo_documento, $id_escuela, string $nombres, string $apellidos, string $email, string $password, string $telefono, int $id_rol): bool
    {
        $this->lastError = '';
        $this->lastPasswordHash = '';

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $this->lastPasswordHash = $passwordHash;

        // Formador (2), acudiente (1) y admin escuela (3) requieren flujo de validacion.
        if ($id_rol == 1) {
            $estado = 'pago_pendiente';
            $habilitado = 0;
        } elseif ($id_rol == 2) {
            $estado = 'pendiente';
            $habilitado = 0;
        } elseif ($id_rol == 3) {
            $estado = 'pago_pendiente';
            $habilitado = 0;
        } else {
            $estado = 'aprobado';
            $habilitado = 1;
        }

        $insertUser = function ($schoolId) use (
            $id_usuario,
            $tipo_documento,
            $nombres,
            $apellidos,
            $email,
            $passwordHash,
            $telefono,
            $id_rol,
            $habilitado,
            $estado
        ): bool {
            $sql = $this->conexion->prepare("INSERT INTO usuarios 
            (id_usuario, tipo_documento, id_escuela, nombres, apellidos, email, contrasena, telefono, id_rol, habilitado, estado) 
            VALUES 
            (:id_usuario, :tipo_documento, :id_escuela, :nombres, :apellidos, :email, :contrasena, :telefono, :id_rol, :habilitado, :estado)");

            $sql->bindParam(":id_usuario", $id_usuario);
            $sql->bindParam(":tipo_documento", $tipo_documento);
            if ($schoolId === null || $schoolId === '') {
                $sql->bindValue(":id_escuela", null, PDO::PARAM_NULL);
            } else {
                $sql->bindValue(":id_escuela", (int)$schoolId, PDO::PARAM_INT);
            }
            $sql->bindParam(":nombres", $nombres);
            $sql->bindParam(":apellidos", $apellidos);
            $sql->bindParam(":email", $email);
            $sql->bindParam(":contrasena", $passwordHash);
            $sql->bindParam(":telefono", $telefono);
            $sql->bindParam(":id_rol", $id_rol);
            $sql->bindParam(":habilitado", $habilitado);
            $sql->bindParam(":estado", $estado);
            return $sql->execute();
        };

        try {
            return $insertUser($id_escuela);
        } catch (PDOException $e) {
            error_log('Error al registrar usuario: ' . $e->getMessage());
            if ($this->lastError === '') {
                $this->lastError = $e->getMessage();
            }
            return false;
        }
    }

    public function lastError(): string
    {
        return $this->lastError;
    }

    public function lastPasswordHash(): string
    {
        return $this->lastPasswordHash;
    }

    public function escuelaExiste($idEscuela): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM escuelas WHERE id_escuela = :id_escuela');
        $stmt->bindParam(':id_escuela', $idEscuela);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function crearSolicitudPagoAdmin($idUsuario, string $comprobantePath): bool
    {
        $sql = $this->conexion->prepare("
            INSERT INTO admin_payment_requests (id_usuario, comprobante_path, estado)
            VALUES (:id_usuario, :comprobante_path, 'pendiente')
        ");

        return $sql->execute([
            ':id_usuario' => $idUsuario,
            ':comprobante_path' => $comprobantePath,
        ]);
    }
}
