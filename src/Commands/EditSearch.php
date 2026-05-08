<?php declare(strict_types=1);

require __DIR__.'/../autoload.php';

use App\Models\Search;
use App\Utils\Console;

$options = getopt('', ['id:', 'keywords:', 'category-ids:', 'price-min:', 'price-max:', 'distance:', 'latitude:', 'longitude:', 'shippable:', 'active:']);

if (empty($options['id'])) {
    Console::error(<<<'TXT'

    Usage: php src/Commands/EditSearch.php --id=ID [options]
    Options:
    --keywords=KEYWORDS
    --category-ids=IDS|null
    --price-min=MIN|null
    --price-max=MAX|null
    --distance=DIST|null
    --latitude=LAT|null
    --longitude=LONG|null
    --shippable=true|false|null
    --active=1|0

    TXT);

    exit(1);
}

$search = Search::find(intval($options['id']));

if ($search === null) {
    Console::error("Error: No se encontró la búsqueda con ID '{$options['id']}'.");
    exit(1);
}

$update = [];

if (isset($options['keywords'])) {
    $update['keywords'] = $options['keywords'];
}
if (isset($options['category-ids'])) {
    $update['category_ids'] = $options['category-ids'] === 'null' ? null : $options['category-ids'];
}
if (isset($options['price-min'])) {
    $update['price_min'] = $options['price-min'] === 'null' ? null : floatval($options['price-min']);
}
if (isset($options['price-max'])) {
    $update['price_max'] = $options['price-max'] === 'null' ? null : floatval($options['price-max']);
}
if (isset($options['distance'])) {
    $update['distance'] = $options['distance'] === 'null' ? null : $options['distance'];
}
if (isset($options['latitude'])) {
    $update['latitude'] = $options['latitude'] === 'null' ? null : floatval($options['latitude']);
}
if (isset($options['longitude'])) {
    $update['longitude'] = $options['longitude'] === 'null' ? null : floatval($options['longitude']);
}
if (isset($options['shippable'])) {
    $update['is_shippable'] = $options['shippable'] === 'null' ? null : ($options['shippable'] === 'true' ? 1 : 0);
}
if (isset($options['active'])) {
    $update['active'] = intval($options['active']);
}

if (empty($update)) {
    Console::warning('Nada que actualizar.');
    exit;
}

$search->update($update);
Console::success('Búsqueda actualizada con éxito.');
