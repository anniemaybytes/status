<?php

declare(strict_types=1);

namespace Status\CachedValue;

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

    protected static function getCacheDuration(mixed $param): int
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return 60 + random_int(0, 30);
    }

    /** @noinspection CurlSslServerSpoofingInspection */
    protected static function fetchValue(mixed $param): int
    {
        $curl = new Curl("https://{$param['ns']}/alive");
        $curl->setoptArray(
            [
                CURLOPT_HTTPHEADER => ["Host: {$param['domain']}", 'Connection: Close'],
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_SSL_VERIFYHOST => 0
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
