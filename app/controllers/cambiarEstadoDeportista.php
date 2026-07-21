<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 3) {
    header("Location: ../panel");
    exit();
}

$id = $_POST["id_deportista"];
$id_estado = $_POST["id_estado"];
$schoolId = (int)($_SESSION['usuario']['id_escuela'] ?? 0);
if ($schoolId <= 0) {
    header("Location: ../deportistas");
    exit();
}

$sql = $conexion->prepare("
    UPDATE deportistas d
    INNER JOIN usuarios u ON u.id_usuario = d.id_usuario
    SET d.id_estado = :estado
    WHERE d.id_deportista = :id
      AND u.id_escuela = :id_escuela
");

$sql->bindParam(":estado", $id_estado);
$sql->bindParam(":id", $id);
$sql->bindParam(":id_escuela", $schoolId, PDO::PARAM_INT);
$sql->execute();

// ???? volver
header("Location: ../deportistas");
exit();



