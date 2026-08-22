<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../helpers/password.php';

if (!isset($_SESSION["rol"]) || !in_array((int)$_SESSION["rol"], [3, 4], true)) {
<<<<<<< HEAD
    header("Location: " . sm_url("dashboard"));
=======
    header("Location: ../dashboard");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
    exit();
}

$id = $_POST["id_usuario"];
$accion = $_POST["accion"];
$currentRole = (int)($_SESSION['rol'] ?? 0);
$schoolId = (int)($_SESSION['usuario']['id_escuela'] ?? 0);

if ($currentRole === 3) {
    $stmtTarget = $conexion->prepare("SELECT id_escuela FROM usuarios WHERE id_usuario = :id_usuario LIMIT 1");
    $stmtTarget->bindParam(":id_usuario", $id, PDO::PARAM_INT);
    $stmtTarget->execute();
    $targetSchoolId = (int)($stmtTarget->fetchColumn() ?: 0);
    if ($schoolId <= 0 || $targetSchoolId !== $schoolId) {
<<<<<<< HEAD
        header("Location: " . sm_url("admin_usuarios"));
=======
        header("Location: ../admin_usuarios");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
        exit();
    }
}

// ???? ACTIVAR / ???? DESHABILITAR
if ($accion == "activar" || $accion == "deshabilitar") {

    $nuevoEstado = ($accion == "activar") ? "aprobado" : "deshabilitado";
    $nuevoHabilitado = ($accion == "activar") ? 1 : 0;

    $sql = $conexion->prepare("
        UPDATE usuarios 
        SET estado = :estado,
            habilitado = :habilitado
        WHERE id_usuario = :id
    ");

    $sql->bindParam(":estado", $nuevoEstado);
    $sql->bindParam(":habilitado", $nuevoHabilitado, PDO::PARAM_INT);
    $sql->bindParam(":id", $id);
    $sql->execute();
} else {

    // ?????? ACTUALIZAR DATOS
    $nombres = $_POST["nombres"];
    $apellidos = $_POST["apellidos"];
    $email = $_POST["email"];
    $telefono = preg_replace('/\D+/', '', (string)($_POST["telefono"] ?? '')) ?? '';
    $id_escuela = isset($_POST["id_escuela"]) ? trim((string)$_POST["id_escuela"]) : '';
    $id_rol = $_POST["id_rol"];
    $nueva_contrasena = $_POST["nueva_contrasena"];

    if (!preg_match('/^\d{10}$/', $telefono)) {
<<<<<<< HEAD
        header("Location: " . sm_url("admin_usuarios"));
=======
        header("Location: ../admin_usuarios");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
        exit();
    }

    if ($id_escuela !== '' && !ctype_digit($id_escuela)) {
<<<<<<< HEAD
        header("Location: " . sm_url("admin_usuarios"));
=======
        header("Location: ../admin_usuarios");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
        exit();
    }

    if (!empty($nueva_contrasena)) {
        if (!sm_password_is_valid((string)$nueva_contrasena)) {
<<<<<<< HEAD
            header("Location: " . sm_url("admin_usuarios?error=password"));
=======
            header("Location: ../admin_usuarios&error=password");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
            exit();
        }

        $passwordHash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

        $sql = $conexion->prepare("
        UPDATE usuarios 
        SET nombres = :nombres,
            apellidos = :apellidos,
            email = :email,
            telefono = :telefono,
            id_escuela = :id_escuela,
            id_rol = :id_rol,
            contrasena = :contrasena
        WHERE id_usuario = :id
        ");

        $sql->bindParam(":contrasena", $passwordHash);
    } else {

        $sql = $conexion->prepare("
        UPDATE usuarios 
        SET nombres = :nombres,
            apellidos = :apellidos,
            email = :email,
            telefono = :telefono,
            id_escuela = :id_escuela,
            id_rol = :id_rol
        WHERE id_usuario = :id
        ");
    }

    $sql->bindParam(":nombres", $nombres);
    $sql->bindParam(":apellidos", $apellidos);
    $sql->bindParam(":email", $email);
    $sql->bindParam(":telefono", $telefono);
    if ($id_escuela === '') {
        $sql->bindValue(":id_escuela", null, PDO::PARAM_NULL);
    } else {
        $sql->bindValue(":id_escuela", (int)$id_escuela, PDO::PARAM_INT);
    }
    $sql->bindParam(":id_rol", $id_rol);
    $sql->bindParam(":id", $id);

    $sql->execute();
}

// ???? Redirecci??n
<<<<<<< HEAD
header("Location: " . sm_url("admin_usuarios"));
=======
header("Location: ../admin_usuarios");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
exit();



<<<<<<< HEAD
=======

>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
