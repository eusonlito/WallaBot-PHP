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

    /**
     * Removes the items that are no longer part of a refreshed search result.
     *
     * @param string[] $wallapopIds
     */
    public static function deleteBySearchExceptWallapopIds(int $searchId, array $wallapopIds): void
    {
        $params = ['search_id' => $searchId];

        if (empty($wallapopIds)) {
            self::database()->execute('DELETE FROM `item` WHERE `search_id` = :search_id', $params);

            return;
        }

        $placeholders = [];
        foreach (array_values(array_unique($wallapopIds)) as $index => $wallapopId) {
            $placeholder = ':wallapop_id_'.$index;
            $placeholders[] = $placeholder;
            $params[substr($placeholder, 1)] = $wallapopId;
        }

        self::database()->execute(
            'DELETE FROM `item` WHERE `search_id` = :search_id AND `wallapop_id` NOT IN ('.implode(', ', $placeholders).')',
            $params
        );
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
                `location_city`, `location_postal_code`, `location_region`, `location_country`,
                `is_shippable`, `is_bumped`, `is_refurbished`, `has_warranty`, `category_id`,
                `wallapop_created_at`, `wallapop_modified_at`, `user_id`, `type_attributes`, `is_favorite`, `is_hidden`
            ) VALUES (
                :search_id, :wallapop_id, :title, :description, :price, :currency, :url, :images,
                :location_city, :location_postal_code, :location_region, :location_country,
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
