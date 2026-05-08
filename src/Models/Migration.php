<?php declare(strict_types=1);

namespace App\Models;

use App\Utils\Database;

class Migration extends ModelAbstract
{
    public int $id;
    public string $name;
    public string $applied_at;

    public static function setup(): void
    {
        $dbDir = dirname(__DIR__, 2).'/db';
        if (is_dir($dbDir) === false) {
            mkdir($dbDir, 0755, true);
        }

        $dbFile = $dbDir.'/database.sqlite';
        if (is_file($dbFile) === false) {
            touch($dbFile);
        }

        Database::exec(self::setupMigrationsTableSql());
    }

    private static function setupMigrationsTableSql(): string
    {
        return <<<'SQL'
            CREATE TABLE IF NOT EXISTS `migration` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `name` TEXT NOT NULL UNIQUE,
                `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        SQL;
    }

    public static function apply(string $name): void
    {
        $file = dirname(__DIR__, 2).'/migrations/'.$name.'.sql';

        Database::transaction(static function () use ($name, $file) {
            Database::exec(file_get_contents($file));
            self::database()->insert("INSERT INTO `migration` (`name`) VALUES (:name)", ['name' => $name]);
        });
    }

    public static function names(): array
    {
        return array_column(self::database()->select("SELECT `name` FROM `migration`"), 'name');
    }
}
