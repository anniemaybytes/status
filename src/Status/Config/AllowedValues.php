<?php

declare(strict_types=1);

namespace Status\Config;

/**
 * Class Defaults
 *
 * @package Status\Config
 */
final class AllowedValues
{
    public const array CONFIG = [
        'mode' => ['production', 'staging', 'development'],

        'twitter.enabled' => [true, false]
    ];
}
