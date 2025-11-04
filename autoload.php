<?php
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/includes/';

    $folders = ['models', 'controllers', 'helpers'];

    foreach ($folders as $folder) {
        $file = $baseDir . $folder . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});