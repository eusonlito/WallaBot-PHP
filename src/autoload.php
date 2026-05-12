<?php declare(strict_types=1);

date_default_timezone_set('UTC');

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $file = __DIR__.'/'.str_replace('\\', '/', substr($class, $len)).'.php';

    if (is_file($file)) {
        require $file;
    }
});

if (defined('REQUEST_ID') === false) {
    define('REQUEST_ID', uniqid());
}

require __DIR__.'/Utils/functions.php';
