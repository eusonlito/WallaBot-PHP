<?php declare(strict_types=1);

namespace App\Models;

use BadMethodCallException;
use ReflectionClass;
use App\Utils\Database;

abstract class ModelAbstract
{
    protected static array $properties;
    protected static Database $database;

    public function __construct(protected array $attributes)
    {
        $this->attributes();
    }

    protected function attributes(): void
    {
        $this->propertiesCheck();

        foreach ($this->attributes as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function propertiesCheck(): void
    {
        $properties = $this->properties();
        $keys = array_keys($this->attributes);

        if ($diff = array_diff($keys, $properties)) {
            throw new BadMethodCallException(sprintf('Invalid attributes %s on %s', implode(', ', $diff), static::class));
        }
    }

    protected function properties(): array
    {
        return static::$properties[static::class] ??= $this->propertiesMap();
    }

    protected function propertiesMap(): array
    {
        return array_values(array_filter(array_map(
            static fn ($value) => $value->isPublic() ? $value->getName() : null,
            new ReflectionClass($this)->getProperties()
        )));
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    protected static function database(): Database
    {
        return self::$database ??= new Database();
    }
}
