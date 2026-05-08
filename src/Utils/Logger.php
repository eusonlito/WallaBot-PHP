<?php declare(strict_types=1);

namespace App\Utils;

class Logger
{
    public static function info(mixed $data): void
    {
        file_put_contents(static::file(), '['.date('Y-m-d H:i:s').'] '.static::message($data).PHP_EOL, FILE_APPEND);
    }

    protected static function message(mixed $data): string
    {
        if (is_scalar($data)) {
            return (string)$data;
        }

        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private static function file(): string
    {
        $file = dirname(__DIR__, 2).'/logs/'.date('Y-m-d').'/app.log';

        Helper::mkdir($file, true);

        return $file;
    }
}
