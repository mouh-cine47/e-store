<?php

$envPath = __DIR__ . '/../.env';
if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, "\"'");

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

function project_path($path = '')
{
    $base = dirname(__DIR__);
    if ($path === '') {
        return $base;
    }

    return $base . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/core',
        __DIR__ . '/models',
        __DIR__ . '/controllers',
    ];

    foreach ($directories as $directory) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getFilename() !== $class . '.php') {
                continue;
            }

            require_once $file->getPathname();
            return;
        }
    }
});

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Geo.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/../config/pdo.php';
