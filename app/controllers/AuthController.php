<?php
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . "/app/models/User.php";
require_once $projectRoot . "/app/helpers/ui.php";
require_once $projectRoot . "/app/helpers/password.php";

$autoloadPath = $projectRoot . "/vendor/autoload.php";
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use SendGrid\Mail\Mail;
use SendGrid\Mail\To;
use SendGrid\Mail\From;
use SendGrid\Mail\Subject;
use SendGrid\Mail\HtmlContent;

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
            header('Location: ' . sm_url('recuperar?error=empty'));
            exit;
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            header('Location: ' . sm_url('recuperar?error=invalidemail'));
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

            $sent = $this->sendViaSendGrid($email, $resetUrl);

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
            header('Location: ' . sm_url('reset?token=' . urlencode((string)$token) . '&error=password'));
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            header('Location: ' . sm_url('reset?token=' . urlencode((string)$token) . '&error=password'));
            exit;
        }

        $this->user->updatePassword($token, $passwordHash);

        $messageMode = 'password_success';
        require $this->projectRoot . "/app/views/layout/mensaje.php";
    }

    private function buildResetUrl(string $token): string
    {
        $configuredBaseUrl = sm_env('APP_URL');
        if (trim($configuredBaseUrl) !== '') {
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
    }

    private function recoverEmailBodyV2(string $resetUrl): string
    {
        $safeResetUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

        // URL pública del logo — se construye desde APP_URL para funcionar en cualquier entorno
        $appUrl   = rtrim(sm_env('APP_URL'), '/');
        $logoUrl  = htmlspecialchars($appUrl . '/assets/img/escudo_sportmanager.png', ENT_QUOTES, 'UTF-8');

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
              <img src="{$logoUrl}" alt="Sport Manager" width="72" height="72" style="display:block;margin:0 auto 14px;border-radius:18px;background:#fff;padding:10px;object-fit:contain;">
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

}
