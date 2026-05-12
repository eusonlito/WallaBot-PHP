<?php declare(strict_types=1);

use App\Utils\Helper;

function helper()
{
    static $cache;

    return $cache ??= new Helper();
}