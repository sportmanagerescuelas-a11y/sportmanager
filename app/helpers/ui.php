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
                document.addEventListener('DOMContentLoaded', function() {
                    const modalElement = document.getElementById('<?= $safeId ?>');
                    if (modalElement && window.bootstrap && bootstrap.Modal) {
                        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                        modal.show();
                    }
                });
            </script>
        <?php endif;
    }
}

if (!function_exists('sm_csrf_token')) {
    function sm_csrf_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }

        if (!isset($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token']) || strlen($_SESSION['_csrf_token']) < 32) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('sm_csrf_input')) {
    function sm_csrf_input(): void
    {
        $token = sm_csrf_token();
        if ($token === '') {
            return;
        }

        ?>
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
    <?php
    }
}

if (!function_exists('sm_csrf_verify')) {
    function sm_csrf_verify(?string $submittedToken): bool
    {
        $token = sm_csrf_token();
        if ($token === '' || !is_string($submittedToken) || $submittedToken === '') {
            return false;
        }

        return hash_equals($token, $submittedToken);
    }
}

if (!function_exists('sm_birth_date_to_category_label')) {
    function sm_birth_date_to_category_label(?string $birthDate): string
    {
        $value = trim((string)$birthDate);
        if ($value === '') {
            return '';
        }

        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$birth) {
            return '';
        }

        $today = new DateTimeImmutable('today');
        if ($birth > $today) {
            return '';
        }

        $age = (int)$birth->diff($today)->y;
        if ($age <= 6) {
            return 'sub-7';
        }
        if ($age <= 8) {
            return 'sub-9';
        }
        if ($age <= 10) {
            return 'sub-11';
        }
        if ($age <= 12) {
            return 'sub-13';
        }
        if ($age <= 14) {
            return 'sub-15';
        }
        if ($age <= 16) {
            return 'sub-17';
        }

        return 'sub-19';
    }
}

if (!function_exists('sm_category_id_for_birth_date')) {
    /**
     * @param array<int,object|array<string,mixed>> $categories
     */
    function sm_category_id_for_birth_date(?string $birthDate, array $categories = []): int
    {
        $label = sm_birth_date_to_category_label($birthDate);
        if ($label === '') {
            return 0;
        }

        foreach ($categories as $category) {
            if (is_object($category)) {
                $name = trim((string)($category->nombre_cat ?? ''));
                if ($name !== '' && strcasecmp($name, $label) === 0) {
                    return (int)($category->id_categoria ?? 0);
                }
            } elseif (is_array($category)) {
                $name = trim((string)($category['nombre_cat'] ?? ''));
                if ($name !== '' && strcasecmp($name, $label) === 0) {
                    return (int)($category['id_categoria'] ?? 0);
                }
            }
        }

        return 0;
    }
}

if (!function_exists('sm_render_gold_card')) {
    /**
     * @param array<string,mixed> $card
     */
    function sm_render_gold_card(array $card = []): void
    {
        $uid = preg_replace('/[^A-Za-z0-9_-]/', '', uniqid('goldcard_', true));
        if (!is_string($uid) || $uid === '') {
            $uid = 'goldcard_' . bin2hex(random_bytes(4));
        }

        $name = trim((string)($card['name'] ?? 'Tu nombre'));
        $category = trim((string)($card['category'] ?? 'Categoria'));
        $jornada = trim((string)($card['jornada'] ?? $card['level'] ?? 'Jornada'));
        $gender = trim((string)($card['gender'] ?? 'Genero'));
        $imageSrc = trim((string)($card['imageSrc'] ?? $card['image'] ?? ''));
        $imageAlt = trim((string)($card['imageAlt'] ?? 'Vista previa'));
        $imageClass = trim((string)($card['imageClass'] ?? 'gold-card__image'));
        $cardClass = trim((string)($card['class'] ?? ''));
        $theme = is_array($card['theme'] ?? null) ? $card['theme'] : [];

        $themeDefaults = [
            'color-top' => 'var(--school-primary-color, #7c6c1e)',
            'color-mid' => 'var(--school-secondary-color, #d4af37)',
            'color-bottom' => 'var(--school-secondary-color, #6f5410)',
            'color-left' => 'var(--school-primary-color, #9a7d24)',
            'color-right' => 'var(--school-secondary-color, #8d6a19)',
            'line-color' => '#f4dc8d',
            'line-opacity' => '0.58',
            'glow-color' => '#fff0a8',
            'glow-opacity' => '0',
            'banner-color' => '#b8860b',
            'banner-edge' => '#f8e7ae',
            'frame-dark' => '#8a6415',
            'frame-accent' => '#f4dc8d',
            'line-spacing' => '26px',
            'line-width' => '4px',
        ];

        $mergedTheme = array_merge($themeDefaults, $theme);
        $styleParts = [];
        foreach ($mergedTheme as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $styleParts[] = '--' . $key . ': ' . trim((string)$value);
        }

        $styleAttr = htmlspecialchars(implode('; ', $styleParts), ENT_QUOTES, 'UTF-8');
        $cardClassAttr = trim('gold-card ' . $cardClass);
        $safeUid = htmlspecialchars($uid, ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeCategory = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
        $safeJornada = htmlspecialchars($jornada, ENT_QUOTES, 'UTF-8');
        $safeGender = htmlspecialchars($gender, ENT_QUOTES, 'UTF-8');
        $safeImageSrc = $imageSrc !== '' ? htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') : '';
        $safeImageAlt = htmlspecialchars($imageAlt !== '' ? $imageAlt : 'Vista previa', ENT_QUOTES, 'UTF-8');
        $safeImageClass = htmlspecialchars($imageClass !== '' ? $imageClass : 'gold-card__image', ENT_QUOTES, 'UTF-8');
    ?>
        <div class="<?= htmlspecialchars($cardClassAttr, ENT_QUOTES, 'UTF-8') ?>" id="<?= $safeUid ?>" style="<?= $styleAttr ?>">
            <svg class="gold-card__svg" viewBox="0 0 540 840" role="presentation" aria-hidden="true" focusable="false">
                <defs>
                    <clipPath id="<?= $safeUid ?>-outer-clip" clipPathUnits="userSpaceOnUse">
                        <polygon points="270,16 10,93 8,692 256,831 528,687 526,92"></polygon>
                    </clipPath>
                    <clipPath id="<?= $safeUid ?>-inner-clip" clipPathUnits="userSpaceOnUse">
                        <polygon points="257,44 38,109 36,677 256,798 500,671 498,108"></polygon>
                    </clipPath>

                    <linearGradient id="<?= $safeUid ?>-base-gradient" x1="18" y1="10" x2="522" y2="818" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="var(--color-top, #7c6c1e)"></stop>
                        <stop offset="48%" stop-color="var(--color-mid, #d4af37)"></stop>
                        <stop offset="100%" stop-color="var(--color-bottom, #4f3a08)"></stop>
                    </linearGradient>
                    <linearGradient id="<?= $safeUid ?>-cross-gradient" x1="0" y1="0" x2="540" y2="840" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="var(--color-left, #9a7d24)"></stop>
                        <stop offset="52%" stop-color="var(--color-mid, #d4af37)"></stop>
                        <stop offset="100%" stop-color="var(--color-right, #8d6a19)"></stop>
                    </linearGradient>
                    <pattern id="<?= $safeUid ?>-line-pattern" patternUnits="userSpaceOnUse" style="width: var(--line-spacing, 26px); height: var(--line-spacing, 26px);" patternTransform="rotate(45)">
                        <rect width="100%" height="100%" fill="transparent"></rect>
                        <rect x="0" y="0" style="width: var(--line-width, 4px); height: calc(var(--line-spacing, 26px) * 2);" fill="var(--line-color, #e8c96c)" opacity="var(--line-opacity, 0.58)"></rect>
                    </pattern>
                    <radialGradient id="<?= $safeUid ?>-glow-gradient" cx="50%" cy="18%" r="58%" fx="50%" fy="18%">
                        <stop offset="0%" stop-color="var(--glow-color, #fff0a8)" stop-opacity="0.9"></stop>
                        <stop offset="42%" stop-color="var(--glow-color, #fff0a8)" stop-opacity="0.35"></stop>
                        <stop offset="100%" stop-color="var(--glow-color, #fff0a8)" stop-opacity="0"></stop>
                    </radialGradient>
                    <linearGradient id="<?= $safeUid ?>-banner-gradient" x1="180" y1="470" x2="540" y2="840" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#f8e7ae"></stop>
                        <stop offset="45%" stop-color="#b8860b"></stop>
                        <stop offset="100%" stop-color="#8a6415"></stop>
                    </linearGradient>
                </defs>

                <g clip-path="url(#<?= $safeUid ?>-outer-clip)">
                    <rect x="0" y="0" width="540" height="840" fill="url(#<?= $safeUid ?>-base-gradient)"></rect>
                    <rect x="0" y="0" width="540" height="840" fill="url(#<?= $safeUid ?>-cross-gradient)" opacity="0.55"></rect>
                </g>

                <g clip-path="url(#<?= $safeUid ?>-inner-clip)" opacity="0.9">
                    <rect x="0" y="0" width="540" height="840" fill="url(#<?= $safeUid ?>-line-pattern)"></rect>
                </g>

                <g style="opacity: var(--glow-opacity, 0);">
                    <rect x="0" y="0" width="540" height="840" fill="url(#<?= $safeUid ?>-glow-gradient)"></rect>
                </g>

                <g clip-path="url(#<?= $safeUid ?>-outer-clip)">
                    <polygon points="520,480 900,300 900,900 -100,900 95,685" fill="url(#<?= $safeUid ?>-banner-gradient)" opacity="0.92"></polygon>
                </g>

                <polygon points="270,16 10,93 8,692 256,831 528,687 526,92" fill="none" stroke="#8a6415" stroke-width="13" stroke-linejoin="round" vector-effect="non-scaling-stroke"></polygon>
                <polygon points="264,24 22,98 20,684 256,820 516,680 514,98" fill="none" stroke="#f4dc8d" stroke-width="5" stroke-linejoin="round" vector-effect="non-scaling-stroke"></polygon>
                <polygon points="257,44 38,109 36,677 256,798 500,671 498,108" fill="none" stroke="#8a6415" stroke-width="9" stroke-linejoin="round" vector-effect="non-scaling-stroke"></polygon>
            </svg>

            <div class="gold-card__shield" aria-hidden="true"></div>

            <div class="gold-card__slot">
                <?php if ($safeImageSrc !== ''): ?>
                    <img src="<?= $safeImageSrc ?>" alt="<?= $safeImageAlt ?>" class="<?= $safeImageClass ?>">
                <?php endif; ?>
            </div>

            <div class="gold-card__name" aria-label="Nombre del deportista"><?= $safeName ?></div>

            <div class="gold-card__meta" aria-hidden="true">
                <div><?= $safeGender ?></div>
                <div><?= $safeJornada ?></div>
                <div><?= $safeCategory ?></div>
            </div>
        </div>
<?php
    }
}
