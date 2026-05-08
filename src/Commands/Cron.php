<?php declare(strict_types=1);

require __DIR__.'/../autoload.php';

use App\Models\Search;
use App\Services\SearchSync;
use App\Utils\Logger;

Logger::info("Cron started");

$sync = new SearchSync();

foreach (Search::findAllActive() as $search) {
    Logger::info("Searching for: {$search->keywords}");
    $sync->sync($search, true);
}

Logger::info("Cron finished");
