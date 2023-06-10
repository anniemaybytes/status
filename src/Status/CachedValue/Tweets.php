<?php

declare(strict_types=1);

namespace Status\CachedValue;

use Status\Exception\TwitterException;
use Status\Utilities\Twitter;

/**
 * Class Tweets
 *
 * @package Status\CachedValue
 */
final class Tweets extends Base
{
    protected static function getCacheKey(mixed $param): string
    {
        return 'tweets/' . $param['twitter.user'];
    }

    protected static function getCacheDuration(mixed $param): int
    {
        return 300;
    }

    /** @throws TwitterException */
    protected static function fetchValue(mixed $param): array
    {
        $twitter = new Twitter();
        return $twitter->getTimeline($twitter->getUserIdByName($param['twitter.user']), $param['twitter.count']);
    }
}
