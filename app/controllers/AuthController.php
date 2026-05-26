<?php
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . "/app/models/User.php";
require_once $projectRoot . "/app/helpers/ui.php";
require_once $projectRoot . "/app/helpers/password.php";

$autoloadPath = $projectRoot . "/vendor/autoload.php";
$phpMailerLoaded = false;
if (file_exists($autoloadPath)) {
    try {
        require_once $autoloadPath;
        $phpMailerLoaded = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
    } catch (Throwable $e) {
        error_log('No se pudo cargar Composer autoload en AuthController: ' . $e->getMessage());
    }
}

if (!$phpMailerLoaded) {
    $phpMailerBasePath = $projectRoot . '/vendor/phpmailer/phpmailer/src/';
    $phpMailerFiles = [
        'Exception.php',
        'PHPMailer.php',
        'SMTP.php',
    ];
    foreach ($phpMailerFiles as $file) {
        $fullPath = $phpMailerBasePath . $file;
        if (is_file($fullPath)) {
            require_once $fullPath;
        }
    }
}

use PHPMailer\PHPMailer\PHPMailer;

class AuthController
{
    private User $user;
    private string $projectRoot;
    private \DateTimeZone $timezone;

    public function __construct()
    {
        $this->projectRoot = dirname(__DIR__, 2);
        $this->timezone = new \DateTimeZone('America/Bogota');
        $this->user = new User();
    }

    public function showRecuperar(): void
    {
        require $this->projectRoot . "/app/views/layout/recuperar.php";
    }

    public function enviarReset(): void
    {
        $emailRaw = $_POST['email'] ?? '';
        $email = trim((string)$emailRaw);
        if ($email === '') {
            header('Location: index.php?url=recuperar&error=empty');
            exit;
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            header('Location: index.php?url=recuperar&error=invalidemail');
            exit;
        }

        $user = $this->user->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expira = (new \DateTimeImmutable('now', $this->timezone))
                ->modify('+5 minutes')
                ->format('Y-m-d H:i:s');
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
        $password = (string)($_POST['password'] ?? '');

        if ($token === '' || $password === '' || !sm_password_is_valid($password)) {
            header('Location: index.php?url=reset&token=' . urlencode((string)$token) . '&error=password');
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            header('Location: index.php?url=reset&token=' . urlencode((string)$token) . '&error=password');
            exit;
        }

        $this->user->updatePassword($token, $passwordHash);

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

