<?php declare(strict_types=1);

namespace App\Models;

use Exception;

class Search extends ModelAbstract
{
    public int $id;
    public string $keywords;
    public ?string $category_ids;
    public ?float $price_min;
    public ?float $price_max;
    public string $distance;
    public ?float $latitude;
    public ?float $longitude;
    public ?int $is_shippable;
    public ?string $extra_filters;
    public int $active;
    public int $title_only;
    public string $created_at;
    public string $updated_at;

    public static function create(array $data): self
    {
        $id = self::database()->insert(self::createSql(), [
            'keywords' => $data['keywords'],
            'category_ids' => $data['category_ids'] ?? null,
            'price_min' => $data['price_min'] ?? null,
            'price_max' => $data['price_max'] ?? null,
            'distance' => $data['distance'] ?? '400',
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_shippable' => $data['is_shippable'] ?? null,
            'extra_filters' => $data['extra_filters'] ?? null,
            'title_only' => isset($data['title_only']) ? (int)$data['title_only'] : 1,
        ]);

        return static::findOrFail($id);
    }

    private static function createSql(): string
    {
        return <<<'SQL'
            INSERT INTO `search` (`keywords`, `category_ids`, `price_min`, `price_max`, `distance`, `latitude`, `longitude`, `is_shippable`, `extra_filters`, `title_only`)
            VALUES (:keywords, :category_ids, :price_min, :price_max, :distance, :latitude, :longitude, :is_shippable, :extra_filters, :title_only)
            ON CONFLICT(`keywords`) DO UPDATE SET
                `category_ids` = excluded.`category_ids`,
                `price_min` = excluded.`price_min`,
                `price_max` = excluded.`price_max`,
                `distance` = excluded.`distance`,
                `latitude` = excluded.`latitude`,
                `longitude` = excluded.`longitude`,
                `is_shippable` = excluded.`is_shippable`,
                `extra_filters` = excluded.`extra_filters`,
                `title_only` = excluded.`title_only`,
                `active` = 1,
                `updated_at` = CURRENT_TIMESTAMP
        SQL;
    }

    public static function findAllActive(): array
    {
        $rows = self::database()->select("SELECT * FROM `search` WHERE `active` = 1");

        return array_map(static fn(array $row) => new self($row), $rows);
    }

    public static function findAll(): array
    {
        $rows = self::database()->select("SELECT * FROM `search` ORDER BY `created_at` DESC");

        return array_map(static fn(array $row) => new self($row), $rows);
    }

    public static function findOrFail(int $id): ?self
    {
        $row = self::database()->selectOne("SELECT * FROM `search` WHERE `id` = :id", ['id' => $id]);

        if (empty($row)) {
            throw new Exception('Search not found');
        }

        return new self($row);
    }

    public function update(array $data): self
    {
        $sets = ['`updated_at` = CURRENT_TIMESTAMP'];
        $params = ['id' => $this->id];

        foreach ($data as $key => $value) {
            $sets[] = "`$key` = :$key";
            $params[$key] = $value;
        }

        self::database()->execute("UPDATE `search` SET ".implode(', ', $sets)." WHERE `id` = :id", $params);

        return static::findOrFail($this->id);
    }

    public static function delete(int $id): void
    {
        self::database()->execute("DELETE FROM `search` WHERE `id` = :id", ['id' => $id]);
    }
}
