<?php

if (!function_exists('sm_error_text')) {
    /**
     * @param array<string,string> $map
     */
    function sm_error_text(?string $code, array $map = []): string
    {
        $value = trim((string)$code);
        if ($value === '') {
            return '';
        }

        return $map[$value] ?? $value;
    }
}

if (!function_exists('sm_render_alert')) {
    function sm_render_alert(string $message, string $title = 'Fuera de juego', string $variant = 'danger', bool $dismissible = true): void
    {
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $baseClass = 'alert alert-' . $variant . ' sm-error-alert';
        $class = $dismissible ? ($baseClass . ' alert-dismissible fade show') : $baseClass;
        ?>
        <div class="mt-4 <?= $class ?>" role="alert">
            <div class="d-flex align-items-start gap-3">
                <div class="sm-error-icon" aria-hidden="true">!</div>
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold"><?= $safeTitle ?></h5>
                    <p class="mb-0"><?= $safeMessage ?></p>
                </div>
            </div>
            <?php if ($dismissible): ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            <?php endif; ?>
        </div>
        <?php
    }
}
