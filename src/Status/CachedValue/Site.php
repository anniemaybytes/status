<?php

declare(strict_types=1);

namespace Status\CachedValue;

use DOMDocument;
use Status\Enum\Status;
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

        if ($httpCode >= 200 && $httpCode < 300) {
            if (is_string($content)) {
                $doc = new DOMDocument();
                if (!@$doc->loadHTML($content)) {
                    return Status::DOWN->value;
                }

                $nodes = $doc->getElementsByTagName('title');
                $title = $nodes->item(0)?->nodeValue;
                if (stripos($title, 'maintenance') !== false) {
                    return Status::ISSUES->value;
                }
            } else {
                return Status::DOWN->value;
            }

            return Status::NORMAL->value;
        }

        return Status::DOWN->value;
    }
}
