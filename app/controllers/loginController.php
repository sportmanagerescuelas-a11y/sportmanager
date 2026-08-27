<?php

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';

if (isset($_POST["login"])) {

    if (empty($_POST["email"]) || empty($_POST["password"])) {
<<<<<<< HEAD
        header("Location: " . sm_url("login?error=empty"));
=======
<<<<<<< HEAD
        header("Location: " . sm_url("login?error=empty"));
=======
        header("Location: ../login&error=empty");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
        exit();
    }

    // Sanitizar email y obtener contrase??a
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"]; // La contrase??a no se sanitiza para poder compararla con el hash

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
<<<<<<< HEAD
        header("Location: " . sm_url("login?error=invalidemail"));
=======
<<<<<<< HEAD
        header("Location: " . sm_url("login?error=invalidemail"));
=======
        header("Location: ../login&error=invalidemail");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
        exit();
    }

    $usuarioModel = new Usuario($conexion);

    $usuario = $usuarioModel->login($email, $password);

    if ($usuario) {

        // ???? Verificar contrase??a
        if (!password_verify($password, $usuario['contrasena'])) {
<<<<<<< HEAD
            header("Location: " . sm_url("login?error=invalid"));
=======
<<<<<<< HEAD
            header("Location: " . sm_url("login?error=invalid"));
=======
            header("Location: ../login&error=invalid");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
            exit();
        }

        $estadoUsuario = trim((string)($usuario['estado'] ?? ''));
        $habilitadoUsuario = (int)($usuario['habilitado'] ?? 1);

        if ($estadoUsuario === 'pendiente') {
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
            header("Location: " . sm_url("login?error=pending"));
            exit();
        }
        if ($estadoUsuario === 'pago_pendiente') {
            header("Location: " . sm_url("login?error=payment_pending"));
<<<<<<< HEAD
=======
=======
            header("Location: ../login&error=pending");
            exit();
        }
        if ($estadoUsuario === 'pago_pendiente') {
            header("Location: ../login&error=payment_pending");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
            exit();
        }

        // Verificar el bloqueo general despues de los estados pendientes para
        // informar al usuario la razon real por la que aun no puede ingresar.
        if ($estadoUsuario === 'deshabilitado' || $habilitadoUsuario === 0) {
<<<<<<< HEAD
            header("Location: " . sm_url("login?error=disabled"));
=======
<<<<<<< HEAD
            header("Location: " . sm_url("login?error=disabled"));
=======
            header("Location: ../login&error=disabled");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
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
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
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
<<<<<<< HEAD
=======
=======
            header("Location: ../crear_escuela");
            exit();
        }

        header("Location: ../dashboard");
        exit();
    } else {
        header("Location: ../login&error=invalid");
        exit();
    }
}




>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
