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

    /** @return value-of<Status> */
    protected static function fetchValue(mixed $param): int
    {
        $curl = new Curl("https://$param");
        $curl->setoptArray(
            [
                CURLOPT_HTTPHEADER => ["Host: $param", 'Connection: Close'],
            ]
        );
        $content = $curl->exec();
        $rescode = $curl->getInfo(CURLINFO_HTTP_CODE);

        if ($rescode >= 200 && $rescode < 300) {
            if (is_string($content)) {
                $doc = new DOMDocument();
                if (!@$doc->loadHTML($content)) {
                    return Status::DOWN->value;
                }

                $nodes = $doc->getElementsByTagName('title');
                $title = $nodes->item(0)?->nodeValue;
                if (stripos($title, 'maintenance') !== false) {
                    return Status::MAINTENANCE->value;
                }
            } else {
                return Status::DOWN->value;
            }

            return Status::NORMAL->value;
        }

        return Status::DOWN->value;
    }
}
