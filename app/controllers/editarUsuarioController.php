<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 3) {
    header("Location: ../dashboard.php");
    exit();
}

$id = $_POST["id_usuario"];
$accion = $_POST["accion"];

// ???? ACTIVAR / ???? DESHABILITAR
if ($accion == "activar" || $accion == "deshabilitar") {

    $nuevoEstado = ($accion == "activar") ? "aprobado" : "deshabilitado";

    $sql = $conexion->prepare("
        UPDATE usuarios 
        SET estado = :estado 
        WHERE id_usuario = :id
    ");

    $sql->bindParam(":estado", $nuevoEstado);
    $sql->bindParam(":id", $id);
    $sql->execute();
} else {

    // ?????? ACTUALIZAR DATOS
    $nombres = $_POST["nombres"];
    $apellidos = $_POST["apellidos"];
    $email = $_POST["email"];
    $telefono = $_POST["telefono"];
    $id_rol = $_POST["id_rol"];
    $nueva_contrasena = $_POST["nueva_contrasena"];

    if (!empty($nueva_contrasena)) {

        $passwordHash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

        $sql = $conexion->prepare("
        UPDATE usuarios 
        SET nombres = :nombres,
            apellidos = :apellidos,
            email = :email,
            telefono = :telefono,
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
            id_rol = :id_rol
        WHERE id_usuario = :id
        ");
    }

    $sql->bindParam(":nombres", $nombres);
    $sql->bindParam(":apellidos", $apellidos);
    $sql->bindParam(":email", $email);
    $sql->bindParam(":telefono", $telefono);
    $sql->bindParam(":id_rol", $id_rol);
    $sql->bindParam(":id", $id);

    $sql->execute();
}

// ???? Redirecci??n
header("Location: ../admin_usuarios.php");
exit();




