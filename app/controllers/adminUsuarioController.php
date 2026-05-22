<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';

// Solo superadmin
if (!isset($_SESSION["rol"]) || (int)$_SESSION["rol"] !== 4) {
    header("Location: ../index.php?url=dashboard");
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

            if ($rol === 3) {
                $stmtPayment = $conexion->prepare("SELECT estado FROM admin_payment_requests WHERE id_usuario = :id_usuario ORDER BY id DESC LIMIT 1");
                $stmtPayment->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
                $stmtPayment->execute();
                $paymentStatus = (string)$stmtPayment->fetchColumn();

                if ($paymentStatus !== 'verificado') {
                    header("Location: ../index.php?url=admin_usuarios&error=pago_no_verificado");
                    exit();
                }
            }

            if ($rol !== 3) {
                header("Location: ../index.php?url=admin_usuarios&error=solo_admin");
                exit();
            }

            $sql = $conexion->prepare("UPDATE usuarios SET estado = 'crear_escuela', habilitado = 1 WHERE id_usuario = :id_usuario");
            $sql->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $sql->execute();
        }
    }

    if (isset($_POST["verificar_pago"])) {
        $sql = $conexion->prepare("UPDATE admin_payment_requests SET estado = 'verificado', verificado_por = :verificado_por, fecha_verificacion = NOW() WHERE id_usuario = :id_usuario AND estado = 'pendiente'");
        $sql->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sql->bindParam(":verificado_por", $_SESSION['id_usuario'], PDO::PARAM_INT);
        $sql->execute();

        $sqlUser = $conexion->prepare("UPDATE usuarios SET estado = 'pendiente' WHERE id_usuario = :id_usuario AND estado = 'pago_pendiente'");
        $sqlUser->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sqlUser->execute();
    }

    if (isset($_POST["rechazar"])) {
        $sql = $conexion->prepare("DELETE FROM admin_payment_requests WHERE id_usuario = :id_usuario");
        $sql->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sql->execute();

        $sql = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
        $sql->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $sql->execute();
    }
}

header("Location: ../index.php?url=admin_usuarios");
exit();
