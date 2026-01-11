<?php
spl_autoload_register(function ($class) {
    $prefix = 'OtusApp\\';
    $base_dir = __DIR__ . '/OtusApp/';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative_class = substr($class, strlen($prefix));
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
