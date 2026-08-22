<?php

declare(strict_types=1);

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle !== '' && strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        $needleLength = strlen($needle);
        if ($needleLength > strlen($haystack)) {
            return false;
        }

        return substr($haystack, -$needleLength) === $needle;
    }
}

if (!function_exists('sm_load_env_file')) {
    /**
     * Loads a simple .env file into getenv()/$_ENV/$_SERVER.
     */
    function sm_load_env_file(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $separator = strpos($line, '=');
            if ($separator === false) {
                continue;
            }

            $name = trim(substr($line, 0, $separator));
            if ($name === '' || str_contains($name, ' ')) {
                continue;
            }

            $value = trim(substr($line, $separator + 1));
            if ($value !== '') {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if (getenv($name) !== false) {
                continue;
            }

            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
sm_load_env_file($envPath);

<<<<<<< HEAD
if (!function_exists('sm_base_path')) {
    function sm_base_path(): string
    {
        if (defined('BASE_PATH')) {
            return (string)BASE_PATH;
        }

        $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($basePath === '.' || $basePath === '/') {
            return '';
        }

        return $basePath;
    }
}

if (!function_exists('sm_url')) {
    function sm_url(string $path = '', array $query = []): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            $url = sm_base_path() . '/';
        } elseif (preg_match('#^[a-z][a-z0-9+.-]*:#i', $trimmed) === 1 || str_starts_with($trimmed, '//')) {
            $url = $trimmed;
        } else {
            $fragment = '';
            $queryString = '';
            $hashPos = strpos($trimmed, '#');
            if ($hashPos !== false) {
                $fragment = substr($trimmed, $hashPos);
                $trimmed = substr($trimmed, 0, $hashPos);
            }
            $queryPos = strpos($trimmed, '?');
            if ($queryPos !== false) {
                $queryString = substr($trimmed, $queryPos + 1);
                $trimmed = substr($trimmed, 0, $queryPos);
            }
            $basePath = sm_base_path();
            $cleanPath = ltrim($trimmed, '/');
            $url = ($basePath === '' ? '' : $basePath) . '/' . $cleanPath;
            if ($queryString !== '') {
                $url .= '?' . $queryString;
            }
            $url .= $fragment;
        }

        if ($query !== []) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query($query);
        }

        return $url;
    }
}

if (!function_exists('sm_asset_url')) {
    function sm_asset_url(string $path = ''): string
    {
        return sm_url('' . ltrim($path, '/'));
    }
}
=======
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
