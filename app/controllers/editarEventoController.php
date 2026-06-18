<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 3) {
    header("Location: ../panel");
    exit();
}

$id = $_POST["id_evento"];
$titulo = $_POST["titulo"];
$fecha = $_POST["fecha"];
$tipo = $_POST["tipo_evento"];
$costo = $_POST["costo"];
$cuotas = $_POST["cuotas"];
$schoolId = (int)($_SESSION['usuario']['id_escuela'] ?? 0);
if ($schoolId <= 0) {
    header("Location: ../gestion-eventos");
    exit();
}

$sql = $conexion->prepare("
    UPDATE eventos 
    SET titulo = :titulo,
        fecha = :fecha,
        tipo_evento = :tipo,
        costo = :costo,
        cuotas = :cuotas
    WHERE id_evento = :id
      AND id_escuela = :id_escuela
");

$sql->bindParam(":titulo", $titulo);
$sql->bindParam(":fecha", $fecha);
$sql->bindParam(":tipo", $tipo);
$sql->bindParam(":costo", $costo);
$sql->bindParam(":cuotas", $cuotas);
$sql->bindParam(":id", $id);
$sql->bindParam(":id_escuela", $schoolId, PDO::PARAM_INT);

$sql->execute();

header("Location: ../eventos");
exit();



