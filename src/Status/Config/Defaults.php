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
    public const array CONFIG = [
        'mode' => 'production', // ensure we fail safely and dont expose sensitive data
        'logs_dir' => UndefinedValue::class,
        'proxy' => false,

        'app.site_name' => UndefinedValue::class,

        'static.location' => '/static/',

        'templates.cache_path' => UndefinedValue::class,

        'site.canonical' => UndefinedValue::class,

        'mei.canonical' => UndefinedValue::class,

        'irc.domain' => UndefinedValue::class,
        'irc.port' => UndefinedValue::class,

        'tracker.domain' => UndefinedValue::class,
        'tracker.ns' => [],

        'twitter.enabled' => false,
        'twitter.username' => UndefinedValue::class,
    ];
}
