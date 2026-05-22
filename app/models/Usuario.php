<?php

class Usuario
{

    private PDO $conexion;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    public function login(string $email, string $password): array|false
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

    public function registrar(string|int $id_usuario, string $tipo_documento, string|int|null $id_escuela, string $nombres, string $apellidos, string $email, string $password, string $telefono, int $id_rol): bool
    {

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Formador (2) y admin escuela (3) requieren aprobacion.
        if ($id_rol == 2) {
            $estado = 'pendiente';
            $habilitado = 0;
        } elseif ($id_rol == 3) {
            $estado = 'pago_pendiente';
            $habilitado = 0;
        } else {
            $estado = 'aprobado';
            $habilitado = 1;
        }

        try {
            $sql = $this->conexion->prepare("INSERT INTO usuarios 
        (id_usuario, tipo_documento, id_escuela, nombres, apellidos, email, contrasena, telefono, id_rol, habilitado, estado) 
        VALUES 
        (:id_usuario, :tipo_documento, :id_escuela, :nombres, :apellidos, :email, :contrasena, :telefono, :id_rol, :habilitado, :estado)");

            $sql->bindParam(":id_usuario", $id_usuario);
            $sql->bindParam(":tipo_documento", $tipo_documento);
            if ($id_escuela === null || $id_escuela === '') {
                $sql->bindValue(":id_escuela", null, PDO::PARAM_NULL);
            } else {
                $sql->bindValue(":id_escuela", (int)$id_escuela, PDO::PARAM_INT);
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
        } catch (PDOException $e) {
            return false;
        }
    }

    public function escuelaExiste(string|int $idEscuela): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM escuelas WHERE id_escuela = :id_escuela');
        $stmt->bindParam(':id_escuela', $idEscuela);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function crearSolicitudPagoAdmin(string|int $idUsuario, string $comprobantePath): bool
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
