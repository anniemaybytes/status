<?php

declare(strict_types=1);

namespace Status\Utilities;

use DateInterval;
use DateTime;
use Exception;

/**
 * Class Time
 *
 * @package Status\Utilities
 */
final class Time
{
    public const ZERO_SQLTIME = '0000-00-00 00:00:00';

    /**
     * Constructs a DateTime object from a UNIX timestamp.
     *
     * Unix timestamp format is 'U'
     *
     * @param mixed $str
     *
     * @return DateTime
     */
    public static function fromEpoch($str): DateTime
    {
        return DateTime::createFromFormat('U', (string)(int)$str);
    }

    /**
     * Returns the current time.
     *
     * @return DateTime
     * @throws Exception
     */
    public static function now(): DateTime
    {
        return new DateTime();
    }

    /**
     * Creates an interval from the given time string. For example,
     *  interval('-1 day');
     *  interval('+1 year');
     *
     * @param string $s
     *
     * @return DateInterval
     */
    public static function interval(string $s): DateInterval
    {
        return DateInterval::createFromDateString($s);
    }
}
