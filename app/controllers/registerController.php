<?php

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/password.php';

if (isset($_POST["register"])) {

    $id_usuario = filter_var(trim($_POST["id_usuario"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tipo_documento = filter_var(trim($_POST["tipo_documento"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id_escuela = filter_var(trim((string)($_POST["id_escuela"] ?? '')), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $nombres = filter_var(trim($_POST["nombres"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $apellidos = filter_var(trim($_POST["apellidos"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $telefono = filter_var(trim($_POST["telefono"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id_rol = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 1;

    $roles_validos = [1, 2, 3];
    if (!in_array($id_rol, $roles_validos, true)) {
        $id_rol = 1;
    }

    if (empty($id_usuario) || empty($tipo_documento) || empty($nombres) || empty($apellidos) || empty($email) || empty($password) || empty($telefono) || empty($id_rol)) {
        header("Location: register?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register?error=invalidemail");
        exit();
    }

    if (!preg_match('/^\d{1,11}$/', $id_usuario)) {
        header("Location: register?error=empty");
        exit();
    }

    $usuarioModel = new Usuario($conexion);

    if (!preg_match('/^\d{10}$/', $telefono)) {
        header("Location: register?error=phone");
        exit();
    }

    if (!sm_password_is_valid($password)) {
        header("Location: register?error=password");
        exit();
    }

    $stmtUserId = $conexion->prepare("SELECT 1 FROM usuarios WHERE id_usuario = ? LIMIT 1");
    $stmtUserId->execute([$id_usuario]);
    if ($stmtUserId->fetchColumn()) {
        header("Location: register?error=duplicateid");
        exit();
    }

    $stmtEmail = $conexion->prepare("SELECT 1 FROM usuarios WHERE email = ? LIMIT 1");
    $stmtEmail->execute([$email]);
    if ($stmtEmail->fetchColumn()) {
        header("Location: register?error=duplicateemail");
        exit();
    }

    if ($id_rol !== 3 && !$usuarioModel->escuelaExiste($id_escuela)) {
        header("Location: register?error=school");
        exit();
    }

    if ($id_rol === 3) {
        // Para admin no se exige escuela.
        $id_escuela = null;
    }

    if ($usuarioModel->registrar($id_usuario, $tipo_documento, $id_escuela, $nombres, $apellidos, $email, $password, $telefono, $id_rol)) {
        if ($id_rol === 3) {
            // Solicitud creada para seguimiento interno; se marcara como verificada
            // tras la confirmacion exitosa en pasarela.
            $usuarioModel->crearSolicitudPagoAdmin($id_usuario, 'pasarela');

            $_SESSION['registro_temporal'] = [
                'id_usuario' => $id_usuario,
                'tipo_documento' => $tipo_documento,
                'id_escuela' => null,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'nombre' => trim($nombres . ' ' . $apellidos),
                'email' => $email,
                'password' => $password,
                'telefono' => $telefono,
                'id_rol' => 3,
                'cantidad' => 1,
            ];

            header("Location: iniciar?evento=Pago%20registro%20administrador&monto=35000&cantidad=1&return_to=register");
            exit();
        }

        if ($id_rol === 2) {
            header("Location: register?success=pending");
        } else {
            header("Location: register?success=1");
        }
        exit();
    }

    $debug = $usuarioModel->lastError();
    if ($debug !== '') {
        header("Location: register?error=db&debug=" . urlencode(substr($debug, 0, 220)));
    } else {
        header("Location: register?error=db");
    }
    exit();
}

header("Location: register");
exit();
    // La columna id_usuario en BD es INT firmado (max 2147483647).
    if ((int)$id_usuario > 2147483647) {
        header("Location: register?error=duplicateid");
        exit();
    }
