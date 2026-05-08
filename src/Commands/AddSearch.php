<?php declare(strict_types=1);

require __DIR__.'/../autoload.php';

use App\Models\Search;
use App\Utils\Console;

$options = getopt('', ['keywords:', 'category-ids:', 'price-min:', 'price-max:', 'distance:', 'latitude:', 'longitude:', 'shippable:']);

if (empty($options['keywords'])) {
    Console::error(<<<'TXT'

    Usage: php src/Commands/AddSearch.php --keywords="KEYWORDS" [options]
    Options:
    --category-ids=IDS
    --price-min=MIN
    --price-max=MAX
    --distance=DIST
    --latitude=LAT
    --longitude=LONG
    --shippable=true|false

    TXT);

    exit(1);
}

Search::create([
    'keywords' => $options['keywords'],
    'category_ids' => $options['category-ids'] ?? null,
    'price_min' => isset($options['price-min']) ? floatval($options['price-min']) : null,
    'price_max' => isset($options['price-max']) ? floatval($options['price-max']) : null,
    'distance' => $options['distance'] ?? '400',
    'latitude' => isset($options['latitude']) ? floatval($options['latitude']) : null,
    'longitude' => isset($options['longitude']) ? floatval($options['longitude']) : null,
    'is_shippable' => isset($options['shippable']) ? ($options['shippable'] === 'true' ? 1 : 0) : null,
    'extra_filters' => null,
]);

Console::success('Search added/updated successfully.');
