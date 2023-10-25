<?php

declare(strict_types=1);

namespace Status\CachedValue;

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
        $curl = new Curl("https://$param/images/error.jpg");
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
        $body = $curl->exec();
        if (!$body || $curl->error()) { // if there's no body (including headers) or there was error then its down
            unset($curl);
            return 0;
        }
        $httpCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        unset($curl);

        preg_match("/\r?\n(?:Location|URI): *(.*?) *\r?\n/im", $body, $headers);
        if ($httpCode === 302 && $headers[1] === '/error.jpg') {
            return 1;
        }

        return 0;
    }
}
