<?php declare(strict_types=1);

require __DIR__.'/../autoload.php';

use App\Models\Search;
use App\Utils\Console;

$searches = Search::findAll();

if (empty($searches)) {
    Console::error('No hay búsquedas configuradas.');
    exit;
}

Console::line(sprintf("%-4s | %-25s | %-6s | %-8s | %-8s | %-6s | %-9s", 'ID', 'Keywords', 'Active', 'Min', 'Max', 'Dist', 'Shippable'));
Console::line(str_repeat('-', 85));

foreach ($searches as $search) {
    Console::line(sprintf("%-4s | %-25s | %-6s | %-8s | %-8s | %-6s | %-9s",
        $search->id,
        substr($search->keywords, 0, 25),
        $search->active ? 'Yes' : 'No',
        $search->price_min ?? '-',
        $search->price_max ?? '-',
        $search->distance ?? '-',
        $search->is_shippable === null ? '-' : ($search->is_shippable ? 'Yes' : 'No')
    ));
}
