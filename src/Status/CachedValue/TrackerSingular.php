<?php

declare(strict_types=1);

namespace Status\CachedValue;

use Random\RandomException;
use Status\Enum\Status;
use Status\Utilities\Curl;

/**
 * Class TrackerSingular
 *
 * @package Status\CachedValue
 */
final class TrackerSingular extends Base
{
    protected static function getCacheKey(mixed $param): string
    {
        return 'tracker/' . $param['ns'];
    }

    /** @throws RandomException */
    protected static function getCacheDuration(mixed $param): int
    {
        return 60 + random_int(0, 30);
    }

    /** @return value-of<Status> */
    protected static function fetchValue(mixed $param): int
    {
        $curl = new Curl("https://{$param['domain']}/alive");
        $curl->setoptArray(
            [
                CURLOPT_HTTPHEADER => ["Host: {$param['domain']}", 'Connection: Close'],
                CURLOPT_RESOLVE => ["{$param['domain']}:443:{$param['ns']}"]
            ]
        );
        $content = $curl->exec();
        $rescode = $curl->getInfo(CURLINFO_HTTP_CODE);

        if ($rescode >= 200 && $rescode < 300 && is_string($content)) {
            return Status::from((int)!str_contains($content, 'unavailable'))->value;
        }

        return Status::DOWN->value;
    }
}
