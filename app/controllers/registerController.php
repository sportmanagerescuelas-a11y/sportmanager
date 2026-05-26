<?php

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/password.php';

if (isset($_POST["register"])) {

    $id_usuario = filter_var(trim($_POST["id_usuario"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tipo_documento = filter_var(trim($_POST["tipo_documento"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id_escuela = filter_var(trim($_POST["id_escuela"]), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
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
        header("Location: ../register&error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../register&error=invalidemail");
        exit();
    }

    if (!preg_match('/^\d{1,11}$/', $id_usuario)) {
        header("Location: ../register&error=empty");
        exit();
    }

    $usuarioModel = new Usuario($conexion);

    if (!preg_match('/^\d{10}$/', $telefono)) {
        header("Location: ../register&error=phone");
        exit();
    }

<<<<<<< HEAD
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&._-]).{8,}$/', $password)) {
        header("Location: ../register&error=password");
=======
    if (!sm_password_is_valid($password)) {
        header("Location: ../index.php?url=register&error=password");
>>>>>>> 126dda8f7753308ef803a8c539ad3930816285c5
        exit();
    }

    $stmtUserId = $conexion->prepare("SELECT 1 FROM usuarios WHERE id_usuario = ? LIMIT 1");
    $stmtUserId->execute([$id_usuario]);
    if ($stmtUserId->fetchColumn()) {
        header("Location: ../register&error=duplicateid");
        exit();
    }

    $stmtEmail = $conexion->prepare("SELECT 1 FROM usuarios WHERE email = ? LIMIT 1");
    $stmtEmail->execute([$email]);
    if ($stmtEmail->fetchColumn()) {
        header("Location: ../register&error=duplicateemail");
        exit();
    }

    if ($id_rol !== 3 && !$usuarioModel->escuelaExiste($id_escuela)) {
        header("Location: ../register&error=school");
        exit();
    }

    if ($id_rol === 3) {
        $id_escuela = null;
    }

    if ($id_rol === 3) {
        if (empty($_FILES['comprobante_pago']['tmp_name']) || empty($_FILES['comprobante_pago']['name'])) {
            header("Location: ../register&error=comprobante");
            exit();
        }
    }

    if ($usuarioModel->registrar($id_usuario, $tipo_documento, $id_escuela, $nombres, $apellidos, $email, $password, $telefono, $id_rol)) {
        if ($id_rol === 3) {
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$_FILES['comprobante_pago']['name']);
            $fileName = time() . '_' . $id_usuario . '_' . $safeName;
            $targetDir = dirname(__DIR__, 2) . '/fotos/admin_payments';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }

            $targetPath = $targetDir . '/' . $fileName;
            $publicPath = 'fotos/admin_payments/' . $fileName;

            $saved = move_uploaded_file($_FILES['comprobante_pago']['tmp_name'], $targetPath);
            if ($saved) {
                $usuarioModel->crearSolicitudPagoAdmin($id_usuario, $publicPath);
                header("Location: ../register&success=payment_pending");
                exit();
            }

            header("Location: ../register&error=comprobante_upload");
            exit();
        }

        if ($id_rol === 2) {
            header("Location: ../register&success=pending");
        } else {
            header("Location: ../register&success=1");
        }
        exit();
    }

    header("Location: ../register&error=db");
    exit();
}

header("Location: ../register");
exit();
