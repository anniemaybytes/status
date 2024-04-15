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

    protected static function fetchValue(mixed $param): int
    {
        $curl = new Curl("https://$param/alive");
        $curl->setoptArray(
            [
                CURLOPT_HTTPHEADER => ["Host: $param", 'Connection: Close'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => false,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
            ]
        );
        $content = $curl->exec();
        $httpCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        unset($curl);

        return Status::from((int)($httpCode === 200 && is_string($content)))->value;
    }
}
