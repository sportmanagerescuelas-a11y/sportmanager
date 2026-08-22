<?php

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
<<<<<<< HEAD
    $directFile = __DIR__ . DIRECTORY_SEPARATOR . $relativePath;

    if (is_file($directFile)) {
        require_once $directFile;
        return;
    }

    $segments = explode('\\', $relative);
    $topLevel = array_shift($segments);
    if (!is_string($topLevel) || $topLevel === '') {
        return;
    }

    $appDirs = @scandir(__DIR__);
    if (!is_array($appDirs)) {
        return;
    }

    foreach ($appDirs as $dirName) {
        if ($dirName === '.' || $dirName === '..') {
            continue;
        }
        $dirPath = __DIR__ . DIRECTORY_SEPARATOR . $dirName;
        if (!is_dir($dirPath) || strcasecmp($dirName, $topLevel) !== 0) {
            continue;
        }

        $fallbackFile = $dirPath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $segments) . '.php';
        if (is_file($fallbackFile)) {
            require_once $fallbackFile;
        }
        return;
    }
});
=======
    $file = __DIR__ . DIRECTORY_SEPARATOR . $relativePath;

    if (is_file($file)) {
        require_once $file;
    }
});

>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
