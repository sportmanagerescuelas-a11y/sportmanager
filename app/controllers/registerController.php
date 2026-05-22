<?php

require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';

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
        header("Location: ../index.php?url=register&error=empty");
        exit();
    }

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

    if ($id_rol !== 3 && !$usuarioModel->escuelaExiste($id_escuela)) {
        header("Location: ../index.php?url=register&error=school");
        exit();
    }

    if ($id_rol === 3) {
        $id_escuela = null;
    }

    if ($id_rol === 3) {
        if (empty($_FILES['comprobante_pago']['tmp_name']) || empty($_FILES['comprobante_pago']['name'])) {
            header("Location: ../index.php?url=register&error=comprobante");
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
                header("Location: ../index.php?url=register&success=payment_pending");
                exit();
            }

            header("Location: ../index.php?url=register&error=comprobante_upload");
            exit();
        }

        if ($id_rol === 2) {
            header("Location: ../index.php?url=register&success=pending");
        } else {
            header("Location: ../index.php?url=register&success=1");
        }
        exit();
    }

    header("Location: ../index.php?url=register&error=db");
    exit();
}

header("Location: ../index.php?url=register");
exit();
