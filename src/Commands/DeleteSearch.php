<?php declare(strict_types=1);

require __DIR__.'/../autoload.php';

use App\Models\Search;
use App\Utils\Console;

$options = getopt('', ['id:']);

if (empty($options['id'])) {
    Console::error('Usage: php src/Commands/DeleteSearch.php --id=ID');
    exit(1);
}

$search = Search::find(intval($options['id']));

if ($search === null) {
    Console::error("Error: No se encontró la búsqueda con ID '{$options['id']}'.");
    exit(1);
}

Search::delete(intval($options['id']));
Console::success('Búsqueda eliminada con éxito.');
