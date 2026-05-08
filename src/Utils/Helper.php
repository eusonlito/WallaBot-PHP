<?php declare(strict_types=1);

namespace App\Utils;

class Helper
{
    public static function mkdir(string $dir, bool $file = false): string
    {
        if ($file) {
            $dir = dirname($dir);
        }

        if (is_dir($dir)) {
            return $dir;
        }

        try {
            mkdir($dir, 0755, true);
        } catch (\Throwable) {
        }

        return $dir;
    }

    public static function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        
        $unwanted = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'ñ' => 'n', 'ç' => 'c',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'À' => 'a', 'È' => 'e', 'Ì' => 'i', 'Ò' => 'o', 'Ù' => 'u',
            'Ä' => 'a', 'Ë' => 'e', 'Ï' => 'i', 'Ö' => 'o', 'Ü' => 'u',
            'Ñ' => 'n', 'Ç' => 'c',
        ];

        return strtr($text, $unwanted);
    }
}
