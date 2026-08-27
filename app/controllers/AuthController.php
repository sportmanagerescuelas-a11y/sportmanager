<?php
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . "/app/models/User.php";
require_once $projectRoot . "/app/helpers/ui.php";
require_once $projectRoot . "/app/helpers/password.php";

$autoloadPath = $projectRoot . "/vendor/autoload.php";
<<<<<<< HEAD
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use SendGrid\Mail\Mail;
use SendGrid\Mail\To;
use SendGrid\Mail\From;
use SendGrid\Mail\Subject;
use SendGrid\Mail\HtmlContent;
=======
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
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910

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
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
            header('Location: ' . sm_url('recuperar?error=empty'));
            exit;
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            header('Location: ' . sm_url('recuperar?error=invalidemail'));
<<<<<<< HEAD
=======
=======
            header('Location: recuperar?error=empty');
            exit;
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            header('Location: recuperar?error=invalidemail');
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
            exit;
        }

        $messageMode = 'reset_sent';
        $user = $this->user->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expira = (new \DateTimeImmutable('now', $this->timezone))
                ->modify('+5 minutes')
                ->format('Y-m-d H:i:s');
            $this->user->saveToken($email, $token, $expira);
            $resetUrl = $this->buildResetUrl($token);
<<<<<<< HEAD

            $sent = $this->sendViaSendGrid($email, $resetUrl);
=======
            $sent = false;

            if (class_exists(PHPMailer::class)) {
                try {
                    $mail = new PHPMailer(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Timeout = 12;
                    $mail->SMTPDebug = 0;
                    $mail->Debugoutput = static function (string $str): void {
                        error_log('SMTP debug (recuperacion): ' . trim($str));
                    };

                    $smtp = $this->mailSettings();
                    $fromAddress = $smtp['from_address'] !== '' ? $smtp['from_address'] : 'no-reply@sportmanager.local';
                    $fromName = $smtp['from_name'] !== '' ? $smtp['from_name'] : 'Soporte';
                    $mail->setFrom($fromAddress, $fromName);
                    $mail->addAddress($email);

                    $logoPath = $this->projectRoot . '/assets/img/escudo_sportmanager.png';
                    if (is_file($logoPath)) {
                        $mail->addEmbeddedImage($logoPath, 'sportmanager-logo', 'escudo_sportmanager.png');
                    }

                    if ($smtp['host'] !== '' && $smtp['username'] !== '' && $smtp['password'] !== '') {
                        $mail->isSMTP();
                        $mail->Host = $smtp['host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtp['username'];
                        $mail->Password = $smtp['password'];
                        $mail->Port = $smtp['port'];
                        $mail->SMTPAutoTLS = true;
                        $secureMode = $this->normalizeSmtpEncryption($smtp['encryption']);
                        if ($secureMode !== '') {
                            $mail->SMTPSecure = $secureMode;
                        } else {
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        }
                        $mail->SMTPOptions = [
                            'ssl' => [
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true,
                            ],
                        ];
                    } else {
                        error_log('No hay credenciales SMTP para recuperacion de contraseña. Se usara mail().');
                        $mail->isMail();
                    }

                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperar contrasena';
                    $mail->Body = $this->recoverEmailBodyV2($resetUrl);
                    $sent = $mail->send();
                } catch (Throwable $e) {
                    error_log('Error enviando correo de recuperacion: ' . $e->getMessage());
                    if (isset($mail) && $mail instanceof PHPMailer) {
                        error_log('Detalle SMTP de recuperacion: ' . $mail->ErrorInfo);
                    }
                }
            } else {
                $subject = 'Recuperar contrasena';
                $message = 'Para restablecer tu contrasena entra aqui: ' . $resetUrl;
                $sent = @mail($email, $subject, $message);
                if (!$sent) {
                    error_log('No se pudo enviar correo con mail() para: ' . $email);
                }
            }
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910

            if (!$sent) {
                $messageMode = 'reset_failed';
            }
        }

        require $this->projectRoot . "/app/views/layout/mensaje.php";
    }

    public function showReset(): void
    {
        $token = $_GET['token'] ?? '';
        $user = $this->user->findByToken($token);

        if (!$user) {
            die("Token invalido o expirado");
        }

        require $this->projectRoot . "/app/views/layout/nueva_password.php";
    }

    public function guardarPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = (string)($_POST['password'] ?? '');

        if ($token === '' || $password === '' || !sm_password_is_valid($password)) {
<<<<<<< HEAD
            header('Location: ' . sm_url('reset?token=' . urlencode((string)$token) . '&error=password'));
=======
<<<<<<< HEAD
            header('Location: ' . sm_url('reset?token=' . urlencode((string)$token) . '&error=password'));
=======
            header('Location: reset?token=' . urlencode((string)$token) . '&error=password');
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
<<<<<<< HEAD
            header('Location: ' . sm_url('reset?token=' . urlencode((string)$token) . '&error=password'));
=======
<<<<<<< HEAD
            header('Location: ' . sm_url('reset?token=' . urlencode((string)$token) . '&error=password'));
=======
            header('Location: reset?token=' . urlencode((string)$token) . '&error=password');
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
            exit;
        }

        $this->user->updatePassword($token, $passwordHash);

        $messageMode = 'password_success';
        require $this->projectRoot . "/app/views/layout/mensaje.php";
    }

    private function buildResetUrl(string $token): string
    {
<<<<<<< HEAD
        $configuredBaseUrl = sm_env('APP_URL');
        if (trim($configuredBaseUrl) !== '') {
=======
        $configuredBaseUrl = getenv('APP_URL');
        if (is_string($configuredBaseUrl) && trim($configuredBaseUrl) !== '') {
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
            $baseUrl = rtrim(trim($configuredBaseUrl), '/');
            return "{$baseUrl}/reset?token=" . urlencode($token);
        }

        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        $baseUrl = "{$scheme}://{$host}" . ($basePath !== '' ? $basePath : '');

        return "{$baseUrl}/reset?token=" . urlencode($token);
    }

    /**
<<<<<<< HEAD
     * Envía el correo de recuperación usando el SDK oficial de SendGrid.
     * No requiere SMTP; funciona en hostings con salida HTTP/HTTPS como byethost.
     */
    private function sendViaSendGrid(string $toEmail, string $resetUrl): bool
    {
        $apiKey      = trim(sm_env('SENDGRID_API_KEY'));
        $fromAddress = sm_env('MAIL_FROM_ADDRESS', 'no-reply@sportmanager.local');
        $fromName    = sm_env('MAIL_FROM_NAME', 'Sport Manager');

        if ($apiKey === '') {
            error_log('SendGrid: SENDGRID_API_KEY no configurada.');
            return false;
        }

        if (!class_exists(\SendGrid::class)) {
            error_log('SendGrid: SDK no disponible. Verifica vendor/autoload.php.');
            return false;
        }

        try {
            $email = new Mail();
            $email->setFrom($fromAddress, $fromName);
            $email->setSubject('Recuperar contrasena - Sport Manager');
            $email->addTo($toEmail);
            $email->addContent('text/html', $this->recoverEmailBodyV2($resetUrl));

            $sg       = new \SendGrid($apiKey);
            $response = $sg->send($email);

            $status = $response->statusCode();

            // SendGrid devuelve 202 Accepted cuando el correo se encola
            if ($status !== 202) {
                error_log('SendGrid HTTP ' . $status . ': ' . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            error_log('SendGrid excepcion: ' . $e->getMessage());
            return false;
        }
=======
     * @return array{host:string,username:string,password:string,port:int,from_address:string,from_name:string,encryption:string}
     */
    private function mailSettings(): array
    {
        $host = trim((string)(getenv('MAIL_HOST') ?: 'smtp.gmail.com'));
        $username = trim((string)(getenv('MAIL_USERNAME') ?: 'sportmanager.escuelas@gmail.com'));
        $password = str_replace([' ', "\t", "\r", "\n"], '', trim((string)(getenv('MAIL_PASSWORD') ?: '')));
        $port = (int)(getenv('MAIL_PORT') ?: 587);
        $fromAddress = trim((string)(getenv('MAIL_FROM_ADDRESS') ?: 'sportmanager.escuelas@gmail.com'));
        $fromName = trim((string)(getenv('MAIL_FROM_NAME') ?: 'Sport Manager'));
        $encryption = strtolower(trim((string)(getenv('MAIL_ENCRYPTION') ?: '')));

        return [
            'host' => $host,
            'username' => $username,
            'password' => $password,
            'port' => $port,
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'encryption' => $encryption,
        ];
    }

    private function normalizeSmtpEncryption(string $encryption): string
    {
        $normalized = strtolower(trim($encryption));

        if ($normalized === 'ssl') {
            return PHPMailer::ENCRYPTION_SMTPS;
        }

        if ($normalized === 'tls') {
            return PHPMailer::ENCRYPTION_STARTTLS;
        }

        return '';
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
    }

    private function recoverEmailBodyV2(string $resetUrl): string
    {
        $safeResetUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

<<<<<<< HEAD
        // URL pública del logo — se construye desde APP_URL para funcionar en cualquier entorno
        $appUrl   = rtrim(sm_env('APP_URL'), '/');
        $logoUrl  = htmlspecialchars($appUrl . '/assets/img/escudo_sportmanager.png', ENT_QUOTES, 'UTF-8');

=======
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
        return <<<HTML
<!doctype html>
<html lang="es">
<body style="margin:0;padding:0;background:#eef4fa;font-family:Arial,Helvetica,sans-serif;color:#102a43;">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    Solicitud de recuperacion de contrasena para Sport Manager.
  </div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(180deg,#0f2340 0%,#17334e 42%,#eef4fa 42.1%,#eef4fa 100%);padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:22px;overflow:hidden;box-shadow:0 18px 40px rgba(8,18,31,.16);">
          <tr>
            <td style="background:linear-gradient(135deg,#07111f 0%,#16314c 55%,#2f7fbd 100%);padding:28px 28px 22px;text-align:center;">
<<<<<<< HEAD
              <img src="{$logoUrl}" alt="Sport Manager" width="72" height="72" style="display:block;margin:0 auto 14px;border-radius:18px;background:#fff;padding:10px;object-fit:contain;">
=======
              <img src="cid:sportmanager-logo" alt="Sport Manager" width="72" height="72" style="display:block;margin:0 auto 14px;border-radius:18px;background:#fff;padding:10px;object-fit:contain;">
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
              <div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.78);font-weight:700;">Sport Manager</div>
              <div style="margin-top:6px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.7);">Gestion deportiva</div>
              <h1 style="margin:10px 0 0;font-size:28px;line-height:1.1;color:#fff;">Restablece tu contrasena</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:30px 30px 12px;text-align:center;">
              <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#334155;">
                Recibimos una solicitud para restablecer tu contrasena en <strong>Sport Manager</strong>.
              </p>
              <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#475569;">
                Si fuiste tu, haz clic en el boton siguiente. El enlace tiene vigencia limitada por seguridad.
              </p>
              <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto 26px;">
                <tr>
                  <td align="center" style="border-radius:999px;background:#2f7fbd;">
                    <a href="{$safeResetUrl}" style="display:inline-block;padding:14px 24px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:999px;">Restablecer contrasena</a>
                  </td>
                </tr>
              </table>
              <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">
                Si no solicitaste este cambio, puedes ignorar este correo sin problema.
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:0 30px 28px;text-align:center;">
              <div style="height:1px;background:#e2e8f0;margin:8px 0 18px;"></div>
              <p style="margin:0;font-size:12px;line-height:1.6;color:#94a3b8;">
                Sport Manager - Gestion deportiva
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

<<<<<<< HEAD
=======
    private function recoverEmailBody(string $resetUrl): string
    {
        $safeResetUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="es">
<body style="margin:0;padding:0;background:#eef4fa;font-family:Arial,Helvetica,sans-serif;color:#102a43;">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    Solicitud de recuperacion de contrasena para Sport Manager.
  </div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(180deg,#0f2340 0%,#17334e 42%,#eef4fa 42.1%,#eef4fa 100%);padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:22px;overflow:hidden;box-shadow:0 18px 40px rgba(8,18,31,.16);">
          <tr>
            <td style="background:linear-gradient(135deg,#07111f 0%,#16314c 55%,#2f7fbd 100%);padding:28px 28px 22px;text-align:center;">
              <img src="cid:sportmanager-logo" alt="Sport Manager" width="72" height="72" style="display:block;margin:0 auto 14px;border-radius:18px;background:#fff;padding:10px;object-fit:contain;">
              <div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.78);font-weight:700;">Recuperacion de cuenta</div>
              <h1 style="margin:10px 0 0;font-size:28px;line-height:1.1;color:#fff;">Restablece tu contrasena</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:30px 30px 12px;text-align:center;">
              <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#334155;">
                Recibimos una solicitud para restablecer tu contrasena en <strong>Sport Manager</strong>.
              </p>
              <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#475569;">
                Si fuiste tu, haz clic en el boton siguiente. El enlace tiene vigencia limitada por seguridad.
              </p>
              <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto 26px;">
                <tr>
                  <td align="center" style="border-radius:999px;background:#2f7fbd;">
                    <a href="{$safeResetUrl}" style="display:inline-block;padding:14px 24px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:999px;">Restablecer contrasena</a>
                  </td>
                </tr>
              </table>
              <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">
                Si no solicitaste este cambio, puedes ignorar este correo sin problema.
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:0 30px 28px;text-align:center;">
              <div style="height:1px;background:#e2e8f0;margin:8px 0 18px;"></div>
              <p style="margin:0;font-size:12px;line-height:1.6;color:#94a3b8;">
                Sport Manager - Gestion deportiva
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
}
