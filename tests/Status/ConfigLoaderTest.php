<?php

declare(strict_types=1);

use org\bovigo\vfs;
use Status\ConfigLoader;

/**
 * Class ConfigLoaderTest
 */
class ConfigLoaderTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var vfs\vfsStreamDirectory
     */
    private vfs\vfsStreamDirectory $root;

    public function setup(): void
    {
        $this->root = vfs\vfsStream::setup('configLoaderTest');
    }

    /**
     * Config loader should bork if configs are missing
     */
    public function test_should_fail_if_config_missing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->root->removeChild('config');
        $str = new vfs\vfsStreamFile('config');
        $this->root->addChild($str);
        ConfigLoader::load(vfs\vfsStream::url('configLoaderTest/config/'));
    }

    /**
     * Config loader should correctly load file
     */
    public function test_check_load_file(): void
    {
        $this->root->removeChild('config');
        vfs\vfsStream::create(
            [
                'config' => [
                    'private.ini' => '
[app]
test = true
',
                ]
            ]
        );
        $c = ConfigLoader::load(vfs\vfsStream::url('configLoaderTest/config/'));
        self::assertArrayHasKey('app.test', $c);
        self::assertEquals(true, $c['app.test']);
    }

    /**
     * Config loader should correctly parse simple structures
     */
    public function test_parses_simple_ini(): void
    {
        $this->root->removeChild('config');
        vfs\vfsStream::create(
            [
                'config' => [
                    'private.ini' => '
mode = development
logs_dir = /code/logs

[app]
site_name = AnimeBytes Status

[static]
location = /static/
',
                ]
            ]
        );
        $c = ConfigLoader::load(vfs\vfsStream::url('configLoaderTest/config/'));
        self::assertEquals(
            [
                'mode' => 'development',
                'logs_dir' => '/code/logs',
                'app.site_name' => 'AnimeBytes Status',
                'static.location' => '/static/'
            ],
            $c
        );
    }

    /**
     * Config loader should correctly parse complex structures
     */
    public function test_parses_complex_ini(): void
    {
        $this->root->removeChild('config');
        vfs\vfsStream::create(
            [
                'config' => [
                    'private.ini' => '
[tracker]
domain = tracker.animebytes.local:34000
ns[localhost] = 10.0.0.1
',
                ]
            ]
        );
        $c = ConfigLoader::load(vfs\vfsStream::url('configLoaderTest/config/'));
        self::assertEquals(
            [

                'tracker.domain' => 'tracker.animebytes.local:34000',
                'tracker.ns' => [
                    'localhost' => '10.0.0.1',
                ],
            ],
            $c
        );
    }
}
