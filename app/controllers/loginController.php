<?php

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';

if (isset($_POST["login"])) {

    if (empty($_POST["email"]) || empty($_POST["password"])) {
        header("Location: " . sm_url("login?error=empty"));
        exit();
    }

    // Sanitizar email y obtener contrase??a
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"]; // La contrase??a no se sanitiza para poder compararla con el hash

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: " . sm_url("login?error=invalidemail"));
        exit();
    }

    $usuarioModel = new Usuario($conexion);

    $usuario = $usuarioModel->login($email, $password);

    if ($usuario) {

        // ???? Verificar contrase??a
        if (!password_verify($password, $usuario['contrasena'])) {
            header("Location: " . sm_url("login?error=invalid"));
            exit();
        }

        $estadoUsuario = trim((string)($usuario['estado'] ?? ''));
        $habilitadoUsuario = (int)($usuario['habilitado'] ?? 1);

        if ($estadoUsuario === 'pendiente') {
            header("Location: " . sm_url("login?error=pending"));
            exit();
        }
        if ($estadoUsuario === 'pago_pendiente') {
            header("Location: " . sm_url("login?error=payment_pending"));
            exit();
        }

        // Verificar el bloqueo general despues de los estados pendientes para
        // informar al usuario la razon real por la que aun no puede ingresar.
        if ($estadoUsuario === 'deshabilitado' || $habilitadoUsuario === 0) {
            header("Location: " . sm_url("login?error=disabled"));
            exit();
        }

        // ??? Login correcto
        $_SESSION["usuario"] = $usuario;
        $_SESSION["rol"] = $usuario["id_rol"];
        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        
        // ???? Obtener nombre del rol desde la BD
        $sqlRol = $conexion->prepare("SELECT nombre_rol FROM roles WHERE id_rol = ?");
        $sqlRol->execute([$usuario["id_rol"]]);

        $rolData = $sqlRol->fetch(PDO::FETCH_ASSOC);

        // Guardar en sesi??n
        $_SESSION["nombre_rol"] = $rolData["nombre_rol"];

        if ($usuario['estado'] === 'crear_escuela' && (int)$usuario['id_rol'] === 3) {
            header("Location: " . sm_url("crear_escuela"));
            exit();
        }

        $rolUsuario = (int)$usuario['id_rol'];
        $rolDestino = 'dashboard';
        switch ($rolUsuario) {
            case 4:
                $rolDestino = 'dashboard';
                break;
            case 2:
                $rolDestino = 'registrar-asistencia';
                break;
            case 1:
            case 3:
            default:
                $rolDestino = 'dashboard';
                break;
        }

        header("Location: " . sm_url($rolDestino));
        exit();
    } else {
        header("Location: " . sm_url("login?error=invalid"));
        exit();
    }
}
