<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\User;

<<<<<<< HEAD
if (!defined('APP_BASE_PATH')) {
    require_once dirname(__DIR__) . '/bootstrap.php';
}

=======
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
final class RegistroController
{
    public function handle(): void
    {
        $errorMessage = null;
        $showModal = false;

        if (isset($_SESSION['error'])) {
            $errorMessage = (string)$_SESSION['error'];
            unset($_SESSION['error']);
        }

        if (!empty($_SESSION['flash_show_modal'])) {
            $showModal = true;
            unset($_SESSION['flash_show_modal']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = (string)filter_var($_POST['nombre'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $email = (string)filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = (string)($_POST['contrasena'] ?? '');
            $passwordConfirm = (string)($_POST['confirmar_contrasena'] ?? '');
            $telefono = (string)filter_var($_POST['telefono'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $telefono = preg_replace('/\D+/', '', $telefono ?? '') ?? '';

            if ($password !== $passwordConfirm) {
                $_SESSION['error'] = 'Las contraseñas no coinciden. Por favor, inténtelo de nuevo.';
<<<<<<< HEAD
                header('Location: ' . sm_url('registro'));
=======
                header('Location: ' . BASE_PATH . '/registro');
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
                exit();
            }

            if ($email === '') {
                $_SESSION['error'] = 'Email inválido.';
<<<<<<< HEAD
                header('Location: ' . sm_url('registro'));
=======
                header('Location: ' . BASE_PATH . '/registro');
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
                exit();
            }

            if (!preg_match('/^\d{10}$/', $telefono)) {
                $_SESSION['error'] = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
<<<<<<< HEAD
                header('Location: ' . sm_url('registro'));
=======
                header('Location: ' . BASE_PATH . '/registro');
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
                exit();
            }

            if (User::existsByEmail($email)) {
                $_SESSION['error'] = 'El correo electrónico ya está registrado. Por favor, utiliza otro correo.';
<<<<<<< HEAD
                header('Location: ' . sm_url('registro'));
=======
                header('Location: ' . BASE_PATH . '/registro');
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
                exit();
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                $_SESSION['error'] = 'No se pudo procesar la contraseña.';
<<<<<<< HEAD
                header('Location: ' . sm_url('registro'));
=======
                header('Location: ' . BASE_PATH . '/registro');
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
                exit();
            }

            $_SESSION['registro_temporal'] = [
                'nombre' => $nombre,
                'email' => $email,
                'password' => $hashedPassword,
                'telefono' => $telefono,
            ];

            // PRG: evita que al refrescar se vuelva a abrir el modal.
            $_SESSION['flash_show_modal'] = true;
<<<<<<< HEAD
            header('Location: ' . sm_url('registro'));
=======
            header('Location: ' . BASE_PATH . '/registro');
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
            exit();
        }

        View::render('registro', [
            'errorMessage' => $errorMessage,
            'showModal' => $showModal,
        ]);
    }
}
