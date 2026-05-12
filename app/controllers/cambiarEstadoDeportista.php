<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 3) {
    header("Location: ../dashboard.php");
    exit();
}

$id = $_POST["id_deportista"];
$id_estado = $_POST["id_estado"];

$sql = $conexion->prepare("
    UPDATE deportistas 
    SET id_estado = :estado 
    WHERE id_deportista = :id
");

$sql->bindParam(":estado", $id_estado);
$sql->bindParam(":id", $id);
$sql->execute();

// ???? volver
header("Location: ../deportistas.php");
exit();



