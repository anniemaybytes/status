<?php

declare(strict_types=1);

namespace Status\CachedValue;

use Status\Enum\Status;
use Status\Utilities\Curl;

/**
 * Class Mei
 *
 * @package Status\CachedValue
 */
final class Mei extends Base
{
    protected static function getCacheKey(mixed $param): string
    {
        return 'mei';
    }

    protected static function getCacheDuration(mixed $param): int
    {
        return 90;
    }

    /** @return value-of<Status> */
    protected static function fetchValue(mixed $param): int
    {
        $curl = new Curl("https://$param/alive");
        $curl->setoptArray(
            [
                CURLOPT_HTTPHEADER => ["Host: $param", 'Connection: Close'],
            ]
        );
        $content = $curl->exec();
        $rescode = $curl->getInfo(CURLINFO_HTTP_CODE);

        return Status::from((int)($rescode === 200 && is_string($content)))->value;
    }
}
