<?php

declare(strict_types=1);

namespace Status\CachedValue;

use DOMDocument;
use Status\Utilities\Curl;

/**
 * Class Site
 *
 * @package Status\CachedValue
 */
final class Site extends Base
{
    protected static function getCacheKey(mixed $param): string
    {
        return 'site';
    }

    protected static function getCacheDuration(mixed $param): int
    {
        return 60;
    }

    protected static function fetchValue(mixed $param): int
    {
        $curl = new Curl("https://$param");
        $curl->setoptArray(
            [
                CURLOPT_USERAGENT => 'status (cURL) like Twitterbot/1.0',
                CURLOPT_HTTPHEADER => ["Host: $param", 'Connection: Close'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => false,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_SSL_VERIFYHOST => 2
            ]
        );
        $content = $curl->exec();
        $httpCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        unset($curl);

        if (is_string($content)) {
            $doc = new DOMDocument();
            if (!@$doc->loadHTML($content)) { // unable to parse output, assume site is down
                return 0;
            }

            $nodes = $doc->getElementsByTagName('title');
            $title = $nodes->item(0)->nodeValue;
            if ($title === 'Down for Maintenance') {
                return 2;
            }
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return 1;
        }

        return 0;
    }
}
