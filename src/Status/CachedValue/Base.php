<?php

declare(strict_types=1);

namespace Status\CachedValue;

use Status\Cache\IKeyStore;

/**
 * Class Base
 *
 * @package Status\CachedValue
 */
abstract class Base
{
    // === CACHE ===

    public static function get(IKeyStore $cache, mixed $param): mixed
    {
        static::validateParam($param);
        $value = $cache->get(static::getCacheKey($param));
        if ($value === false) {
            $value = static::fetchValue($param);
            static::set($cache, $param, $value);
        }
        return $value;
    }

    public static function set(IKeyStore $cache, mixed $param, mixed $value): void
    {
        static::validateParam($param);
        $cache->set(
            static::getCacheKey($param),
            $value,
            static::getCacheDuration($param)
        );
    }

    public static function clear(IKeyStore $cache, mixed $param): void
    {
        static::validateParam($param);
        $cache->delete(static::getCacheKey($param));
    }

    // === ABSTRACT ===

    abstract protected static function getCacheKey(mixed $param): string;

    abstract protected static function getCacheDuration(mixed $param): int;

    protected static function validateParam(mixed $param): bool
    {
        return true;
    }

    abstract protected static function fetchValue(mixed $param): mixed;
}
