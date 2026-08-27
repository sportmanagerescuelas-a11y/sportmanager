<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . '/../../config/conexion.php';

// Solo superadmin
if (!isset($_SESSION["rol"]) || (int)$_SESSION["rol"] !== 4) {
<<<<<<< HEAD
    header("Location: " . sm_url("dashboard"));
=======
<<<<<<< HEAD
    header("Location: " . sm_url("dashboard"));
=======
    header("Location: ../dashboard");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
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
<<<<<<< HEAD
                header("Location: " . sm_url("admin_usuarios?error=solo_admin"));
=======
<<<<<<< HEAD
                header("Location: " . sm_url("admin_usuarios?error=solo_admin"));
=======
                header("Location: ../admin_usuarios&error=solo_admin");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
                exit();
            }

            $stmtInvoice = $conexion->prepare("SELECT 1 FROM facturas WHERE id = :id_usuario LIMIT 1");
            $stmtInvoice->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmtInvoice->execute();
            $hasInvoice = (bool)$stmtInvoice->fetchColumn();

            if (!$hasInvoice) {
<<<<<<< HEAD
                header("Location: " . sm_url("admin_usuarios?error=sin_factura"));
=======
<<<<<<< HEAD
                header("Location: " . sm_url("admin_usuarios?error=sin_factura"));
=======
                header("Location: ../admin_usuarios&error=sin_factura");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
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
            $sqlUser = $conexion->prepare("UPDATE usuarios SET estado = 'crear_escuela', habilitado = 1 WHERE id_usuario = :id_usuario AND estado = 'pago_pendiente'");
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

<<<<<<< HEAD
header("Location: " . sm_url("admin_usuarios"));
=======
<<<<<<< HEAD
header("Location: " . sm_url("admin_usuarios"));
=======
header("Location: ../admin_usuarios");
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
exit();
