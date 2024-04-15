<?php

declare(strict_types=1);

namespace Status\CachedValue;

use Status\Enum\Status;

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
        $addr = @dns_get_record($param['domain'], DNS_A)[0]['ip'] ?? null;
        if (!is_string($addr)) {
            return Status::DOWN->value;
        }

        if (!$file = @fsockopen($addr, $param['port'], $errno, $errstr, 3)) {
            return Status::DOWN->value;
        }

        fclose($file);
        return Status::NORMAL->value;
    }
}
