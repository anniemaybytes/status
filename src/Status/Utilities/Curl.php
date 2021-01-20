<?php

declare(strict_types=1);

namespace Status\Utilities;

use CurlHandle;

use function curl_close;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function curl_setopt_array;

/**
 * Class Curl
 *
 * @package Status\Utilities
 */
final class Curl
{
    private CurlHandle $curl;

    public function __construct(?string $url = null)
    {
        if ($url === null) {
            $this->curl = curl_init();
        } else {
            $this->curl = curl_init($url);
        }
        curl_setopt_array($this->curl, [CURLOPT_RETURNTRANSFER => true]);
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    public function setopt(int $option, mixed $value): bool
    {
        return curl_setopt($this->curl, $option, $value);
    }

    public function getInfo(int $option): mixed
    {
        return curl_getinfo($this->curl, $option);
    }

    public function setoptArray(array $options): bool
    {
        return curl_setopt_array($this->curl, $options);
    }

    public function exec(): bool|string
    {
        return curl_exec($this->curl);
    }

    public function error(): string
    {
        return curl_error($this->curl);
    }
}
