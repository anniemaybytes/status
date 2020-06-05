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

    public function setUp(): void
    {
        $this->root = vfs\vfsStream::setup('configLoaderTest');
    }

    /**
     * Config loader should bork if configs are missing
     */
    public function testShouldFailIfConfigMissing(): void
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
    public function testCheckLoadFile(): void
    {
        $this->root->removeChild('config');
        vfs\vfsStream::create(
            [
                'config' => [
                    'private.ini' => '[site]
test=true',
                ]
            ]
        );
        $c = ConfigLoader::load(vfs\vfsStream::url('configLoaderTest/config/'));
        $this->assertArrayHasKey('site.test', $c);
        $this->assertEquals(true, $c['site.test']);
    }

    /**
     * Config loader should correctly parse simple structures
     */
    public function testParsesSimpleIni(): void
    {
        $this->root->removeChild('config');
        vfs\vfsStream::create(
            [
                'config' => [
                    'private.ini' => '[static]
location = /static/',
                ]
            ]
        );
        $c = ConfigLoader::load(vfs\vfsStream::url('configLoaderTest/config/'));
        $this->assertEquals(
            [
                'static.location' => '/static/'
            ],
            $c
        );
    }

    /**
     * Config loader should correctly parse complex structures
     */
    public function testParsesComplexIni(): void
    {
        $this->root->removeChild('config');
        vfs\vfsStream::create(
            [
                'config' => [
                    'private.ini' => 'mode = development
logs_dir = /code/logs

[site]
site_name = AnimeBytes Status

[templates]
cache_path = /tmp/twig-cache

[tracker]
ns[cloudflare] = 1.1.1.1
ns[google] = 8.8.8.8',
                ]
            ]
        );
        $c = ConfigLoader::load(vfs\vfsStream::url('configLoaderTest/config/'));
        $this->assertEquals(
            [
                'mode' => 'development',
                'logs_dir' => '/code/logs',
                'site.site_name' => 'AnimeBytes Status',
                'templates.cache_path' => '/tmp/twig-cache',
                'tracker.ns' => [
                    'cloudflare' => '1.1.1.1',
                    'google' => '8.8.8.8',
                ],
            ],
            $c
        );
    }
}
