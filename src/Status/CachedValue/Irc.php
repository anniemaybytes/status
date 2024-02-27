<?php

declare(strict_types=1);

namespace Status\CachedValue;

/**
 * Class Irc
 *
 * @package Status\CachedValue
 */
final class Irc extends Base
{
    protected static function getCacheKey(mixed $param): string
    {
        return 'irc';
    }

    protected static function getCacheDuration(mixed $param): int
    {
        return 180;
    }

    protected static function fetchValue(mixed $param): int
    {
        $addr = @dns_get_record($param, DNS_A)[0]['ip'] ?? null;
        if (!is_string($addr)) {
            return 0;
        }

        if (!$file = @fsockopen($addr, 7000, $errno, $errstr, 3)) {
            $status = 0;
        } else {
            fclose($file);
            $status = 1;
        }

        return $status;
    }
}
