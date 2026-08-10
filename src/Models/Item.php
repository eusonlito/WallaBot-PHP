<?php declare(strict_types=1);

namespace App\Models;

use Exception;

class Item extends ModelAbstract
{
    public int $id;
    public int $search_id;
    public string $wallapop_id;
    public string $title;
    public ?string $description;
    public float $price;
    public string $currency;
    public string $url;
    public ?string $images;
    public ?string $location_city;
    public ?string $location_postal_code;
    public ?string $location_region;
    public ?string $location_country;
    public ?float $location_latitude;
    public ?float $location_longitude;
    public int $is_shippable;
    public int $is_bumped;
    public int $is_refurbished;
    public int $has_warranty;
    public ?int $category_id;
    public ?int $wallapop_created_at;
    public ?int $wallapop_modified_at;
    public ?string $notes;
    public ?string $user_id;
    public ?string $type_attributes;
    public int $is_favorite;
    public int $is_hidden;
    public string $created_at;
    public string $updated_at;

    public static function findByWallapopId(int $searchId, string $wallapopId): ?self
    {
        $row = self::database()->selectOne("SELECT * FROM `item` WHERE `search_id` = :search_id AND `wallapop_id` = :wallapop_id", [
            'search_id' => $searchId,
            'wallapop_id' => $wallapopId,
        ]);

        return $row ? new self($row) : null;
    }

    public static function removeItemsNotMatchingSearch(Search $search): void
    {
        $rows = self::database()->select('SELECT * FROM `item` WHERE `search_id` = :search_id', ['search_id' => $search->id]);

        foreach ($rows as $row) {
            $item = new self($row);

            if (self::matchesSearch($item, $search)) {
                continue;
            }

            self::database()->execute('DELETE FROM `item` WHERE `id` = :id', ['id' => $item->id]);
        }
    }

    private static function matchesSearch(self $item, Search $search): bool
    {
        $title = helper()->normalize($item->title);
        $description = helper()->normalize((string)$item->description);
        $text = $search->title_only ? $title : $title.' '.$description;

        foreach (array_filter(explode(' ', helper()->normalize($search->keywords))) as $keyword) {
            if (str_contains($text, $keyword) === false) {
                return false;
            }
        }

        if (!empty($search->exclude_keywords)) {
            foreach (array_filter(preg_split('/[\s,;]+/', helper()->normalize($search->exclude_keywords))) as $keyword) {
                if (str_contains($title.' '.$description, $keyword)) {
                    return false;
                }
            }
        }

        if (($search->price_min !== null && $item->price < $search->price_min)
            || ($search->price_max !== null && $item->price > $search->price_max)
            || ($search->is_shippable !== null && $item->is_shippable !== $search->is_shippable)) {
            return false;
        }

        if (!empty($search->category_ids)) {
            $categoryIds = array_filter(explode(',', $search->category_ids));
            if (!in_array((string)$item->category_id, $categoryIds, true)) {
                return false;
            }
        }

        if (!self::matchesDistance($item, $search)) {
            return false;
        }

        return self::matchesExtraFilters($item, $search);
    }

    private static function matchesDistance(self $item, Search $search): bool
    {
        if (empty($search->latitude) || empty($search->longitude) || !is_numeric($search->distance)
            || $item->location_latitude === null || $item->location_longitude === null) {
            return true;
        }

        $earthRadius = 6371;
        $latDiff = deg2rad($item->location_latitude - $search->latitude);
        $lonDiff = deg2rad($item->location_longitude - $search->longitude);
        $a = sin($latDiff / 2) ** 2
            + cos(deg2rad($search->latitude)) * cos(deg2rad($item->location_latitude)) * sin($lonDiff / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)) <= (float)$search->distance;
    }

    private static function matchesExtraFilters(self $item, Search $search): bool
    {
        $filters = json_decode((string)$search->extra_filters, true);
        if (empty($filters) || !is_array($filters)) {
            return true;
        }

        $attributes = json_decode((string)$item->type_attributes, true);
        if (!is_array($attributes)) {
            return false;
        }

        foreach ($filters as $key => $value) {
            $attributeKey = match ($key) {
                'min_year', 'max_year' => 'year',
                'max_km' => 'km',
                default => $key,
            };
            $attribute = $attributes[$attributeKey] ?? null;

            if ($attribute === null) {
                return false;
            }

            if ($key === 'min_year' || $key === 'rooms' || $key === 'bathrooms') {
                if ((float)$attribute < (float)$value) {
                    return false;
                }
            } elseif ($key === 'max_year' || $key === 'max_km') {
                if ((float)$attribute > (float)$value) {
                    return false;
                }
            } elseif (!self::matchesAttributeValue($attribute, $value)) {
                return false;
            }
        }

        return true;
    }

    private static function matchesAttributeValue(mixed $attribute, mixed $filter): bool
    {
        $values = is_array($attribute) ? $attribute : explode(',', (string)$attribute);
        $expected = array_filter(explode(',', (string)$filter));

        foreach ($values as $value) {
            if (in_array(helper()->normalize((string)$value), array_map(static fn(string $expectedValue) => helper()->normalize($expectedValue), $expected), true)) {
                return true;
            }
        }

        return false;
    }

    public static function findOrFail(int $id): self
    {
        $row = self::database()->selectOne("SELECT * FROM `item` WHERE `id` = :id", ['id' => $id]);

        if (empty($row)) {
            throw new Exception('Item not found');
        }

        return new self($row);
    }

    public function toggleFavorite(): void
    {
        $this->is_favorite = $this->is_favorite ? 0 : 1;

        self::database()->execute("UPDATE `item` SET `is_favorite` = :fav WHERE `id` = :id", [
            'fav' => $this->is_favorite,
            'id' => $this->id,
        ]);
    }

    public function hide(): void
    {
        self::database()->execute("UPDATE `item` SET `is_hidden` = 1 WHERE `id` = :id", [
            'id' => $this->id,
        ]);

        $this->is_hidden = 1;
    }

    public static function create(array $data): self
    {
        $id = self::database()->insert(self::createSql(), [
            'search_id' => $data['search_id'],
            'wallapop_id' => $data['wallapop_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'currency' => $data['currency'] ?? 'EUR',
            'url' => $data['url'],
            'images' => isset($data['images']) ? json_encode($data['images'], JSON_UNESCAPED_SLASHES) : null,
            'location_city' => $data['location_city'] ?? null,
            'location_postal_code' => $data['location_postal_code'] ?? null,
            'location_region' => $data['location_region'] ?? null,
            'location_country' => $data['location_country'] ?? null,
            'location_latitude' => $data['location_latitude'] ?? null,
            'location_longitude' => $data['location_longitude'] ?? null,
            'is_shippable' => !empty($data['is_shippable']) ? 1 : 0,
            'is_bumped' => !empty($data['is_bumped']) ? 1 : 0,
            'is_refurbished' => !empty($data['is_refurbished']) ? 1 : 0,
            'has_warranty' => !empty($data['has_warranty']) ? 1 : 0,
            'category_id' => $data['category_id'] ?? null,
            'wallapop_created_at' => $data['wallapop_created_at'] ?? null,
            'wallapop_modified_at' => $data['wallapop_modified_at'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'type_attributes' => isset($data['type_attributes']) ? json_encode($data['type_attributes'], JSON_UNESCAPED_UNICODE) : null,
        ]);

        return static::findOrFail($id);
    }

    private static function createSql(): string
    {
        return <<<'SQL'
            INSERT INTO `item` (
                `search_id`, `wallapop_id`, `title`, `description`, `price`, `currency`, `url`, `images`,
                `location_city`, `location_postal_code`, `location_region`, `location_country`, `location_latitude`, `location_longitude`,
                `is_shippable`, `is_bumped`, `is_refurbished`, `has_warranty`, `category_id`,
                `wallapop_created_at`, `wallapop_modified_at`, `user_id`, `type_attributes`, `is_favorite`, `is_hidden`
            ) VALUES (
                :search_id, :wallapop_id, :title, :description, :price, :currency, :url, :images,
                :location_city, :location_postal_code, :location_region, :location_country, :location_latitude, :location_longitude,
                :is_shippable, :is_bumped, :is_refurbished, :has_warranty, :category_id,
                :wallapop_created_at, :wallapop_modified_at, :user_id, :type_attributes, 0, 0
            )
        SQL;
    }

    public function updatePrice(float $price, string $notes): self
    {
        $this->price = $price;
        $this->notes = $notes;

        self::database()->execute("UPDATE `item` SET `price` = :price, `notes` = :notes, `updated_at` = CURRENT_TIMESTAMP WHERE `id` = :id", [
            'price' => $this->price,
            'notes' => $this->notes,
            'id' => $this->id,
        ]);

        return $this;
    }

    public static function countBySearch(): array
    {
        $rows = self::database()->select("SELECT `search_id`, COUNT(*) as `total` FROM `item` WHERE `is_hidden` = 0 GROUP BY `search_id`");
        $counts = [];

        foreach ($rows as $row) {
            $counts[$row['search_id']] = (int)$row['total'];
        }

        return $counts;
    }

    public static function findLatest(int $limit = 50, ?int $searchId = null, string $sort = 'date_desc', bool $favoritesOnly = false): array
    {
        $sql = "SELECT * FROM `item` WHERE `is_hidden` = 0 ";
        $params = ['limit' => $limit];

        if ($searchId) {
            $sql .= "AND `search_id` = :search_id ";
            $params['search_id'] = $searchId;
        }

        if ($favoritesOnly) {
            $sql .= "AND `is_favorite` = 1 ";
        }

        if ($sort === 'price_asc') {
            $sql .= "ORDER BY `price` ASC, `updated_at` DESC ";
        } elseif ($sort === 'price_desc') {
            $sql .= "ORDER BY `price` DESC, `updated_at` DESC ";
        } else {
            $sql .= "ORDER BY `updated_at` DESC ";
        }

        $sql .= "LIMIT :limit";
        $rows = self::database()->select($sql, $params);

        return array_map(static fn(array $row) => new self($row), $rows);
    }
}
