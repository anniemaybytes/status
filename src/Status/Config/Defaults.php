<?php

declare(strict_types=1);

namespace Status\Config;

/**
 * Class Defaults
 *
 * @package Status\Config
 */
final class Defaults
{
    public const CONFIG = [
        'mode' => 'production', // ensure we fail safely and dont expose sensitive data
        'logs_dir' => UndefinedValue::class,
        'proxy' => false,

        'app.site_name' => UndefinedValue::class,

        'static.location' => '/static/',

        'templates.cache_path' => UndefinedValue::class,

        'site.domain' => UndefinedValue::class,

        'mei.domain' => UndefinedValue::class,

        'irc.domain' => UndefinedValue::class,

        'tracker.domain' => UndefinedValue::class,
        'tracker.ns' => [],

        'twitter.enabled' => false,
        'twitter.username' => UndefinedValue::class,
    ];
}
