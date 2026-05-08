<?php declare(strict_types=1);

require __DIR__.'/../autoload.php';

use App\Models\Migration;
use App\Utils\Console;

Migration::setup();

$migrationsDir = dirname(__DIR__, 2).'/migrations';
$applied = Migration::names();
$count = 0;

foreach (glob($migrationsDir.'/*.sql') as $file) {
    $name = basename($file, '.sql');

    if (in_array($name, $applied, true)) {
        continue;
    }

    Console::write(sprintf('Applying %s.sql...', $name));
    Migration::apply($name);
    Console::success(' OK');

    $count++;
}

if ($count === 0) {
    Console::info('Nothing to migrate.');
} else {
    Console::success(sprintf("Applied %d migration%s.", $count, $count === 1 ? '' : 's'));
}
