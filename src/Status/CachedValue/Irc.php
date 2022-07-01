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
        $nsRecord = @dns_get_record($param, DNS_A)[0]['ip'] ?? null;
        if (!is_string($nsRecord)) {
            return 0;
        }
        $file = @fsockopen($nsRecord, 7000, $errno, $errstr, 3);
        if (!$file) {
            $status = 0;
        } else {
            fclose($file);
            $status = 1;
        }

        return $status;
    }
}
