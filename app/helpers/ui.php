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

if (!function_exists('sm_render_modal_message')) {
    /**
     * @param array<int,string> $actionsHtml
     */
    function sm_render_modal_message(
        string $id,
        string $title,
        string $message,
        string $variant = 'primary',
        array $actionsHtml = [],
        bool $autoShow = true
    ): void {
        $safeId = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $safeVariant = htmlspecialchars($variant, ENT_QUOTES, 'UTF-8');
        ?>
        <div class="modal fade" id="<?= $safeId ?>" tabindex="-1" aria-labelledby="<?= $safeId ?>Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-<?= $safeVariant ?>">
                    <div class="modal-header bg-<?= $safeVariant ?> text-white">
                        <h5 class="modal-title" id="<?= $safeId ?>Label"><?= $safeTitle ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body"><?= $safeMessage ?></div>
                    <div class="modal-footer">
                        <?php foreach ($actionsHtml as $actionHtml): ?>
                            <?= $actionHtml ?>
                        <?php endforeach; ?>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($autoShow): ?>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalElement = document.getElementById('<?= $safeId ?>');
                if (modalElement && window.bootstrap && bootstrap.Modal) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
            </script>
        <?php endif;
    }
}
