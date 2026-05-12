<?php

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';

if (isset($_POST["register"])) {

    // Sanitizaci??n de datos de entrada para prevenir XSS
    $id_usuario = filter_var(trim($_POST["id_usuario"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tipo_documento = filter_var(trim($_POST["tipo_documento"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id_escuela = filter_var(trim($_POST["id_escuela"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $nombres = filter_var(trim($_POST["nombres"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $apellidos = filter_var(trim($_POST["apellidos"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"]; // La contrase??a no se sanitiza para no alterar caracteres especiales
    $telefono = filter_var(trim($_POST["telefono"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id_rol = isset($_POST['id_rol']) ? (int) $_POST['id_rol'] : 1;

    // Validar que el rol exista (ejemplo: 3=admin, 1=acudiente, 2=entrenador)
    $roles_validos = [1, 2, 3];

    if (!in_array($id_rol, $roles_validos)) {
        $id_rol = 1; // forzar a usuario normal
    }

    if (empty($id_usuario) || empty($tipo_documento) || empty($id_escuela) || empty($nombres) || empty($apellidos) || empty($email) || empty($password) || empty($telefono) || empty($id_rol)) {
        header("Location: ../index.php?url=register&error=empty");
        exit();
    }

    // Validaci??n de formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../index.php?url=register&error=invalidemail");
        exit();
    }

    $usuarioModel = new Usuario($conexion);

    if (!preg_match('/^\d{7,11}$/', $telefono)) {
        header("Location: ../index.php?url=register&error=phone");
        exit();
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&._-]).{8,}$/', $password)) {
        header("Location: ../index.php?url=register&error=password");
        exit();
    }

    $stmtUserId = $conexion->prepare("SELECT 1 FROM usuarios WHERE id_usuario = ? LIMIT 1");
    $stmtUserId->execute([$id_usuario]);
    if ($stmtUserId->fetchColumn()) {
        header("Location: ../index.php?url=register&error=duplicateid");
        exit();
    }

    $stmtEmail = $conexion->prepare("SELECT 1 FROM usuarios WHERE email = ? LIMIT 1");
    $stmtEmail->execute([$email]);
    if ($stmtEmail->fetchColumn()) {
        header("Location: ../index.php?url=register&error=duplicateemail");
        exit();
    }

    $stmtEscuelas = $conexion->query("SELECT COUNT(*) FROM escuelas");
    $totalEscuelas = (int)$stmtEscuelas->fetchColumn();
    if ($totalEscuelas <= 0) {
        header("Location: ../index.php?url=register&error=schoolnone");
        exit();
    }

    if (!$usuarioModel->escuelaExiste($id_escuela)) {
        header("Location: ../index.php?url=register&error=school");
        exit();
    }

    if ($usuarioModel->registrar($id_usuario, $tipo_documento, $id_escuela, $nombres, $apellidos, $email, $password, $telefono, $id_rol)) {
        if ($id_rol == 2 || $id_rol == 3) {
            header("Location: ../index.php?url=register&success=pending");
        } else {
            header("Location: ../index.php?url=register&success=1");
        }
        exit();
    } else {
        header("Location: ../index.php?url=register&error=db");
        exit();
    }
} else {
    header("Location: ../index.php?url=register");
    exit();
}




