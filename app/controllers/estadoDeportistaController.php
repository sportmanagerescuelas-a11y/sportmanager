<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 3) {
    header("Location: ../dashboard.php");
    exit();
}

$id = $_POST["id_deportista"];
$accion = $_POST["accion"];

$nuevoEstado = ($accion == "activar") ? 1 : 2 ; // 1=activo, 2=suspendido

$sql = $conexion->prepare("
    UPDATE deportistas 
    SET id_estado = :estado 
    WHERE id_deportista = :id
");

$sql->bindParam(":estado", $nuevoEstado);
$sql->bindParam(":id", $id);
$sql->execute();

header("Location: ../deportistas.php");
exit();




