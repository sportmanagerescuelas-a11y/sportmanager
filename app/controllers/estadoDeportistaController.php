<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/conexion.php';

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 3) {
<<<<<<< HEAD
    header("Location: " . sm_url("panel"));
=======
<<<<<<< HEAD
    header("Location: " . sm_url("panel"));
=======
    header("Location: ../panel");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
    exit();
}

$id = $_POST["id_deportista"];
$accion = $_POST["accion"];
$schoolId = (int)($_SESSION['usuario']['id_escuela'] ?? 0);
if ($schoolId <= 0) {
<<<<<<< HEAD
    header("Location: " . sm_url("deportistas"));
=======
<<<<<<< HEAD
    header("Location: " . sm_url("deportistas"));
=======
    header("Location: ../deportistas");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
    exit();
}

$nuevoEstado = ($accion == "activar") ? 1 : 2 ; // 1=activo, 2=suspendido

$sql = $conexion->prepare("
    UPDATE deportistas d
    INNER JOIN usuarios u ON u.id_usuario = d.id_usuario
    SET d.id_estado = :estado
    WHERE d.id_deportista = :id
      AND u.id_escuela = :id_escuela
");

$sql->bindParam(":estado", $nuevoEstado);
$sql->bindParam(":id", $id);
$sql->bindParam(":id_escuela", $schoolId, PDO::PARAM_INT);
$sql->execute();

<<<<<<< HEAD
header("Location: " . sm_url("deportistas"));
=======
<<<<<<< HEAD
header("Location: " . sm_url("deportistas"));
=======
header("Location: ../deportistas");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
exit();



<<<<<<< HEAD
=======
<<<<<<< HEAD
=======

>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
