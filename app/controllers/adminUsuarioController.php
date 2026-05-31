<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';

// Solo superadmin
if (!isset($_SESSION["rol"]) || (int)$_SESSION["rol"] !== 4) {
    header("Location: ../dashboard");
    exit();
}

if (isset($_POST["id_usuario"])) {
    $id_usuario = (int)$_POST["id_usuario"];

    if (isset($_POST["aprobar"])) {
        $stmtUser = $conexion->prepare("SELECT id_rol FROM usuarios WHERE id_usuario = :id_usuario LIMIT 1");
        $stmtUser->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmtUser->execute();
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $rol = (int)$user['id_rol'];

            if ($rol !== 3) {
                header("Location: ../admin_usuarios&error=solo_admin");
                exit();
            }

            $stmtInvoice = $conexion->prepare("SELECT 1 FROM facturas WHERE id = :id_usuario LIMIT 1");
            $stmtInvoice->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmtInvoice->execute();
            $hasInvoice = (bool)$stmtInvoice->fetchColumn();

            if (!$hasInvoice) {
                header("Location: ../admin_usuarios&error=sin_factura");
                exit();
            }

            $sql = $conexion->prepare("UPDATE usuarios SET estado = 'crear_escuela', habilitado = 1 WHERE id_usuario = :id_usuario");
            $sql->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $sql->execute();
        }
    }

    if (isset($_POST["verificar_pago"])) {
        $stmtInvoice = $conexion->prepare("SELECT 1 FROM facturas WHERE id = :id_usuario LIMIT 1");
        $stmtInvoice->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmtInvoice->execute();
        $hasInvoice = (bool)$stmtInvoice->fetchColumn();

        if ($hasInvoice) {
            $sqlUser = $conexion->prepare("UPDATE usuarios SET estado = 'pendiente' WHERE id_usuario = :id_usuario AND estado = 'pago_pendiente'");
            $sqlUser->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $sqlUser->execute();
        }
    }

    if (isset($_POST["rechazar"])) {
        $sql = $conexion->prepare("DELETE FROM facturas WHERE id = :id_usuario");
        $sql->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sql->execute();

        $sql = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
        $sql->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sql->execute();
    }
}

header("Location: ../admin_usuarios");
exit();
