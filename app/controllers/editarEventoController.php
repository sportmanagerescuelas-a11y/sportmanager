<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 3) {
    header("Location: ../dashboard.php");
    exit();
}

$id = $_POST["id_evento"];
$titulo = $_POST["titulo"];
$fecha = $_POST["fecha"];
$tipo = $_POST["tipo_evento"];
$costo = $_POST["costo"];
$cuotas = $_POST["cuotas"];

$sql = $conexion->prepare("
    UPDATE eventos 
    SET titulo = :titulo,
        fecha = :fecha,
        tipo_evento = :tipo,
        costo = :costo,
        cuotas = :cuotas
    WHERE id_evento = :id
");

$sql->bindParam(":titulo", $titulo);
$sql->bindParam(":fecha", $fecha);
$sql->bindParam(":tipo", $tipo);
$sql->bindParam(":costo", $costo);
$sql->bindParam(":cuotas", $cuotas);
$sql->bindParam(":id", $id);

$sql->execute();

header("Location: ../eventos.php");
exit();



