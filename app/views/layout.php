<?php
$viewData = get_defined_vars();
$title = (string)($viewData['title'] ?? 'Modulo');
$content = (string)($viewData['content'] ?? '');
$appCssPath = __DIR__ . '/../../assets/css/app.css';
$styleCssPath = __DIR__ . '/../../assets/css/style.css';
$appJsPath = __DIR__ . '/../../assets/js/app.js';
$appCssVersion = is_file($appCssPath) ? (string)filemtime($appCssPath) : (string)time();
$styleCssVersion = is_file($styleCssPath) ? (string)filemtime($styleCssPath) : (string)time();
$appJsVersion = is_file($appJsPath) ? (string)filemtime($appJsPath) : (string)time();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/app.css?v=<?= urlencode($appCssVersion) ?>" rel="stylesheet">
    <link href="assets/css/style.css?v=<?= urlencode($styleCssVersion) ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container-fluid px-3 px-lg-4">
            <span class="navbar-brand fw-semibold">Asistencia - Deportistas</span>
        </div>
    </nav>

    <main class="container-fluid px-3 px-lg-4 py-3 py-lg-4 flex-grow-1">
        <?= $content ?>
    </main>

    <?php if (isset($_SESSION['usuario'])): ?>
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js?v=<?= urlencode($appJsVersion) ?>" defer></script>
</body>
</html>
