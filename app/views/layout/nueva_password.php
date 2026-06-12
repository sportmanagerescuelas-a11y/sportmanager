<?php
$token = (string)($_GET['token'] ?? '');
$resetErrorCode = isset($_GET['error']) ? (string)$_GET['error'] : '';
$stylePath = __DIR__ . '/../../../assets/css/style.css';
$passwordTogglePath = __DIR__ . '/../../../assets/js/password-toggle.js';
$styleVersion = is_file($stylePath) ? (string)filemtime($stylePath) : (string)time();
$passwordToggleVersion = is_file($passwordTogglePath) ? (string)filemtime($passwordTogglePath) : (string)time();
$passwordPolicy = [
    'Minimo 8 caracteres',
    'Una letra mayuscula',
    'Una letra minuscula',
    'Un numero',
    'Un caracter especial (@$!%*?&._-)',
];
$assetBase = '/sportmanager/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="<?= htmlspecialchars($assetBase . 'assets/img/balonfutbol.png', ENT_QUOTES, 'UTF-8') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css?v=<?= urlencode($styleVersion) ?>" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-shell container">
    <section class="auth-panel reveal-up">
        <div class="auth-kicker">Nueva contraseña</div>
        <h1 class="auth-title">Crea una clave fuerte y vuelve a entrar.</h1>
        <p class="auth-copy mb-0">
            El enlace solo funciona por pocos minutos y la contraseña debe cumplir la misma política que el registro.
        </p>
        <div class="auth-badges">
            <div class="auth-badge"><span></span> Vence en 5 minutos</div>
            <div class="auth-badge"><span></span> Misma política del registro</div>
            <div class="auth-badge"><span></span> Ocultar o mostrar clave</div>
        </div>
    </section>

    <section class="auth-card card reveal-up delay-1">
        <div class="card-header">
            <h2 class="text-center mb-1">Nueva contrasena</h2>
            <p class="auth-subtitle text-center mb-0">Usa una contraseña segura para terminar el proceso.</p>
        </div>
        <div class="card-body">
            <?php if ($resetErrorCode === 'password'): ?>
                <div class="alert alert-danger mb-3" role="alert">
                    La contraseña no cumple los requisitos mínimos. Intenta de nuevo.
                </div>
            <?php endif; ?>
            <form method="POST" action="index.php?url=guardar">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">Nueva contraseña</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control auth-input"
                        placeholder="Crea una contraseña"
                        required
                        data-password-policy
                    >
                    <div class="invalid-feedback" id="passwordFeedback">Debes ingresar una contraseña valida.</div>
                </div>
                <div class="mb-3">
                    <small class="form-text text-muted d-block mb-2">La contraseña debe cumplir todos estos requisitos:</small>
                    <ul id="passwordRequirements" class="mt-2 mb-0 ps-3 small">
                        <?php foreach ($passwordPolicy as $index => $rule): ?>
                            <li id="req-<?= (int)$index ?>" class="text-danger">✖ <?= htmlspecialchars($rule, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div id="passwordMissing" class="mt-2 small text-danger"></div>
                </div>
                <button class="btn btn-success w-100 auth-action" type="submit">Guardar nueva contraseña</button>
            </form>
        </div>
    </section>
</div>
<script src="assets/js/password-toggle.js?v=<?= urlencode($passwordToggleVersion) ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('password');
    const missing = document.getElementById('passwordMissing');
    const rules = [
        { id: 'req-0', test: value => value.length >= 8, text: 'Minimo 8 caracteres' },
        { id: 'req-1', test: value => /[A-Z]/.test(value), text: 'Una letra mayuscula' },
        { id: 'req-2', test: value => /[a-z]/.test(value), text: 'Una letra minuscula' },
        { id: 'req-3', test: value => /\d/.test(value), text: 'Un numero' },
        { id: 'req-4', test: value => /[@$!%*?&._\-]/.test(value), text: 'Un caracter especial (@$!%*?&._-)' }
    ];

    const paint = (value) => {
        const missingRules = [];

        rules.forEach(rule => {
            const ok = rule.test(value);
            const element = document.getElementById(rule.id);
            if (element) {
                element.className = ok ? 'text-success' : 'text-danger';
                element.textContent = (ok ? '✓ ' : '✖ ') + rule.text;
            }
            if (!ok) {
                missingRules.push(rule.text.toLowerCase());
            }
        });

        if (missing) {
            missing.textContent = value.length === 0
                ? ''
                : (missingRules.length > 0
                    ? 'Te falta: ' + missingRules.join(', ') + '.'
                    : 'La contrasena cumple todos los requisitos.');
            missing.className = missingRules.length > 0 ? 'mt-2 small text-danger' : 'mt-2 small text-success';
        }
    };

    if (input) {
        input.addEventListener('input', function () {
            paint(this.value);
        });
        paint(input.value);
    }
});
</script>
</body>
</html>
