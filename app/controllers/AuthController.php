<?php
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . "/app/models/User.php";

$autoloadPath = $projectRoot . "/vendor/autoload.php";
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use PHPMailer\PHPMailer\PHPMailer;

class AuthController
{
    private User $user;
    private string $projectRoot;

    public function __construct()
    {
        $this->projectRoot = dirname(__DIR__, 2);
        $this->user = new User();
    }

    public function showRecuperar(): void
    {
        require $this->projectRoot . "/app/views/layout/recuperar.php";
    }

    public function enviarReset(): void
    {
        $email = $_POST['email'] ?? '';
        $user = $this->user->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));
            $this->user->saveToken($email, $token, $expira);
            $resetUrl = $this->buildResetUrl($token);

            if (class_exists(PHPMailer::class)) {
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'termostatosolar2022@gmail.com';
                    $mail->Password = 'ctgrayjsgmradzeg';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom('termostatosolar2022@gmail.com', 'Soporte');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperar contrase??a';
                    $mail->Body = "<h3>Recuperar contrase??a</h3><a href='{$resetUrl}'>Restablecer contrase??a</a>";
                    $mail->send();
                } catch (Throwable $e) {
                    error_log('Error enviando correo de recuperaci??n: ' . $e->getMessage());
                }
            } else {
                $subject = "Recuperar contrase??a";
                $message = "Para restablecer tu contrase??a entra aqu??: " . $resetUrl;
                $sent = @mail($email, $subject, $message);
                if (!$sent) {
                    error_log('No se pudo enviar correo con mail() para: ' . $email);
                }
            }
        }

        require $this->projectRoot . "/app/views/layout/mensaje.php";
    }

    public function showReset(): void
    {
        $token = $_GET['token'] ?? '';
        $user = $this->user->findByToken($token);

        if (!$user) {
            die("Token inv??lido o expirado");
        }

        require $this->projectRoot . "/app/views/layout/nueva_password.php";
    }

    public function guardarPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($token !== '' && $password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $this->user->updatePassword($token, $passwordHash);
        }

        require $this->projectRoot . "/app/views/layout/mensaje.php";
    }

    private function buildResetUrl(string $token): string
    {
        $configuredBaseUrl = getenv('APP_URL');
        if (is_string($configuredBaseUrl) && trim($configuredBaseUrl) !== '') {
            $baseUrl = rtrim(trim($configuredBaseUrl), '/');
            return "{$baseUrl}/index.php?url=reset&token={$token}";
        }

        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        $baseUrl = "{$scheme}://{$host}" . ($basePath !== '' ? $basePath : '');

        return "{$baseUrl}/index.php?url=reset&token={$token}";
    }
}

