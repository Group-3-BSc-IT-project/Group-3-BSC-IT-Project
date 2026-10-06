<?php

require_once __DIR__ . '/../config/config.php';

spl_autoload_register(function (string $class): void {
    $directories = [
        __DIR__ . '/Core/',
        __DIR__ . '/Models/',
        __DIR__ . '/Repositories/',
        __DIR__ . '/Services/',
        __DIR__ . '/Controllers/',
    ];
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

Session::start();
$db = (new Database())->getConnection();
