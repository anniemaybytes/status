<?php

namespace Status\Utilities;

use DateInterval;
use DateTime;
use Exception;

class Time
{
    const ZERO_SQLTIME = '0000-00-00 00:00:00';

    /**
     * Constructs a DateTime object from a UNIX timestamp.
     *
     * Unix timestamp format is 'U'
     *
     * @param $str
     * @return DateTime
     */
    public static function fromEpoch($str)
    {
        return DateTime::createFromFormat('U', $str);
    }

    /**
     * Returns the current time.
     *
     * @return DateTime
     * @throws Exception
     */
    public static function now()
    {
        return new DateTime();
    }

    /**
     * Creates an interval from the given time string. For example,
     *  interval('-1 day');
     *  interval('+1 year');
     *
     * @param $s
     * @return DateInterval
     */
    public static function interval($s)
    {
        return DateInterval::createFromDateString($s);
    }
}
