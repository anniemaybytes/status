<?php

namespace spec\Status;

use PhpSpec\ObjectBehavior;
use Status\ConfigLoader;

class ConfigLoaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ConfigLoader::class);
    }

    function it_parses_simple_ini()
    {
        $iniString = '[site]
assets_root = assets

[templates]
path = templates';
        $iniArray = [
            'site.assets_root' => 'assets',
            'templates.path' => 'templates'
        ];
        self::loadString($iniString)->shouldReturn($iniArray);
    }

    function it_parses_complex_ini()
    {
        $iniString = 'mode = development
logs_dir = logs

[site]
site_name = AnimeBytes Status
site_root =

[templates]
cache_path = /tmp/cache

[tracker]
ns[cloudflare] = 1.1.1.1
ns[google] = 8.8.8.8';
        $iniArray = [
            'mode' => 'development',
            'logs_dir' => 'logs',
            'site.site_name' => 'AnimeBytes Status',
            'site.site_root' => '',
            'templates.cache_path' => '/tmp/cache',
            'tracker.ns' => [
                'cloudflare' => '1.1.1.1',
                'google' => '8.8.8.8',
            ],
        ];
        self::loadString($iniString)->shouldReturn($iniArray);
    }
}
