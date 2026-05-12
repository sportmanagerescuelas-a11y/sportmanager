<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';

// ???? Solo admin
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 3) {
    header("Location: ../admin_usuarios.php");
    exit();
}

if (isset($_POST["id_usuario"])) {

    $id_usuario = $_POST["id_usuario"];

    // ??? APROBAR USUARIO
    if (isset($_POST["aprobar"])) {

        $sql = $conexion->prepare("UPDATE usuarios 
            SET estado = 'aprobado', habilitado = 1 
            WHERE id_usuario = :id_usuario");

        $sql->bindParam(":id_usuario", $id_usuario);
        $sql->execute();
    }

    // ??? RECHAZAR USUARIO
    if (isset($_POST["rechazar"])) {

        $sql = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
        $sql->bindParam(":id_usuario", $id_usuario);
        $sql->execute();
    }
    
}

header("Location: ../admin_usuarios.php");
exit();




