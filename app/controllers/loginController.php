<?php

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';

if (isset($_POST["login"])) {

    if (empty($_POST["email"]) || empty($_POST["password"])) {
        header("Location: ../index.php?url=login&error=1"); // Error generico para no dar pistas
        exit();
    }

    // Sanitizar email y obtener contrase??a
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"]; // La contrase??a no se sanitiza para poder compararla con el hash

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../index.php?url=login&error=1"); // Error generico para no dar pistas
        exit();
    }

    $usuarioModel = new Usuario($conexion);

    $usuario = $usuarioModel->login($email, $password);

    if ($usuario) {

        // ???? Verificar contrase??a
        if (!password_verify($password, $usuario['contrasena'])) {
            header("Location: ../index.php?url=login&error=invalid");
            exit();
        }

        // ???? Verificar estado
        if ($usuario['estado'] == 'pendiente') {
            header("Location: ../index.php?url=login&error=pending");
            exit();
        }

        // ???? Opcional: usuario deshabilitado
        if ($usuario['habilitado'] == 0) {
            header("Location: ../index.php?url=login&error=disabled");
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

        header("Location: ../dashboard.php");
        exit();
    } else {
        header("Location: ../index.php?url=login&error=invalid");
        exit();
    }
}




