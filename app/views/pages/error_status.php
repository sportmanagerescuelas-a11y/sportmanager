<?php
$errorCode = isset($code) ? (string)$code : '500';
$errorTitle = isset($title) ? (string)$title : 'Fuera de juego';
$errorMessage = isset($message) ? (string)$message : 'Ocurrio un error inesperado.';
$errorBackUrl = isset($backUrl) ? (string)$backUrl : 'index.php';
$errorBackLabel = isset($backLabel) ? (string)$backLabel : 'Ir al inicio';
$assetBase = '/sportmanager/';

$leftDigit = substr($errorCode, 0, 1) ?: '5';
$rightDigit = substr($errorCode, -1) ?: '0';
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 sm-404-card">
                <div class="card-body text-center p-5">
                    <p class="sm-404-code mb-2" aria-label="Error <?php echo htmlspecialchars($errorCode, ENT_QUOTES, 'UTF-8'); ?>">
                        <span><?php echo htmlspecialchars($leftDigit, ENT_QUOTES, 'UTF-8'); ?></span>
                        <img src="<?= htmlspecialchars($assetBase . 'assets/img/balonfutbol.png', ENT_QUOTES, 'UTF-8') ?>" alt="0" class="sm-error-ball">
                        <span><?php echo htmlspecialchars($rightDigit, ENT_QUOTES, 'UTF-8'); ?></span>
                    </p>
                    <h1 class="h2 fw-bold mb-3"><?php echo htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p class="text-muted mb-4"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                    <a href="<?php echo htmlspecialchars($errorBackUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary px-4">
                        <?php echo htmlspecialchars($errorBackLabel, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
