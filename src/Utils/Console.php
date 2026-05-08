<?php declare(strict_types=1);

namespace App\Utils;

class Console
{
    private const RESET = "\033[0m";
    private const RED = "\033[31m";
    private const GREEN = "\033[32m";
    private const YELLOW = "\033[33m";
    private const BLUE = "\033[34m";

    public static function success(string $message): void
    {
        self::line($message, self::GREEN);
    }

    public static function error(string $message): void
    {
        self::line($message, self::RED);
    }

    public static function info(string $message): void
    {
        self::line($message, self::BLUE);
    }

    public static function warning(string $message): void
    {
        self::line($message, self::YELLOW);
    }

    public static function line(string $message = '', string $color = ''): void
    {
        echo $color.$message.($color ? self::RESET : '').PHP_EOL;
    }

    public static function write(string $message, string $color = ''): void
    {
        echo $color.$message.($color ? self::RESET : '');
    }
}
