CREATE TABLE `search` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `keywords` TEXT NOT NULL UNIQUE,
    `category_ids` TEXT DEFAULT NULL,
    `price_min` REAL DEFAULT NULL,
    `price_max` REAL DEFAULT NULL,
    `distance` TEXT DEFAULT '400',
    `latitude` REAL DEFAULT NULL,
    `longitude` REAL DEFAULT NULL,
    `is_shippable` INTEGER DEFAULT NULL,
    `extra_filters` TEXT DEFAULT NULL,
    `active` INTEGER DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `item` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `search_id` INTEGER NOT NULL,
    `wallapop_id` TEXT NOT NULL,
    `title` TEXT NOT NULL,
    `description` TEXT DEFAULT NULL,
    `price` REAL NOT NULL,
    `currency` TEXT DEFAULT 'EUR',
    `url` TEXT NOT NULL,
    `images` TEXT DEFAULT NULL,
    `location_city` TEXT DEFAULT NULL,
    `location_postal_code` TEXT DEFAULT NULL,
    `location_region` TEXT DEFAULT NULL,
    `location_country` TEXT DEFAULT NULL,
    `is_shippable` INTEGER DEFAULT 0,
    `is_bumped` INTEGER DEFAULT 0,
    `is_refurbished` INTEGER DEFAULT 0,
    `has_warranty` INTEGER DEFAULT 0,
    `category_id` INTEGER DEFAULT NULL,
    `wallapop_created_at` INTEGER DEFAULT NULL,
    `wallapop_modified_at` INTEGER DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `user_id` TEXT DEFAULT NULL,
    `type_attributes` TEXT DEFAULT NULL,
    `is_favorite` INTEGER DEFAULT 0,
    `is_hidden` INTEGER DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(`search_id`, `wallapop_id`),
    FOREIGN KEY (`search_id`) REFERENCES `search`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `migration` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `name` TEXT NOT NULL UNIQUE,
    `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
