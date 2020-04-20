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
    private $root;

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
     * Config loader should bork if private.ini is missing
     */
    public function testShouldFailIfPrivateMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->root->removeChild('config');
        vfs\vfsStream::create(
            [
                'config' => [
                    'config.ini' => '[site]
hi=true',
                ]
            ]
        );
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
                    'config.ini' => '[site]
test=true',
                    'private.ini' => '',
                ]
            ]
        );
        $c = ConfigLoader::load(vfs\vfsStream::url('configLoaderTest/config/'));
        $this->assertArrayHasKey('site.test', $c);
        $this->assertEquals(true, $c['site.test']);
    }

    /**
     * Config loader should merge config.ini and private.ini
     */
    public function testCheckMergeFiles(): void
    {
        $this->root->removeChild('config');
        vfs\vfsStream::create(
            [
                'config' => [
                    'config.ini' => '[site]
overridden=false',
                    'private.ini' => '[site]
private=true
overridden=true',
                ]
            ]
        );
        $c = ConfigLoader::load(vfs\vfsStream::url('configLoaderTest/config/'));
        $this->assertArrayHasKey('site.private', $c);
        $this->assertArrayHasKey('site.overridden', $c);
        $this->assertEquals(true, $c['site.private']);
        $this->assertEquals(true, $c['site.overridden']);
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
                    'config.ini' => '[site]
assets_root = assets',
                    'private.ini' => '[templates]
path = templates',
                ]
            ]
        );
        $c = ConfigLoader::load(vfs\vfsStream::url('configLoaderTest/config/'));
        $this->assertEquals(
            [
                'site.assets_root' => 'assets',
                'templates.path' => 'templates'
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
                    'config.ini' => '',
                    'private.ini' => 'mode = development
logs_dir = logs

[site]
site_name = AnimeBytes Status
site_root =

[templates]
cache_path = /tmp/cache

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
                'logs_dir' => 'logs',
                'site.site_name' => 'AnimeBytes Status',
                'site.site_root' => '',
                'templates.cache_path' => '/tmp/cache',
                'tracker.ns' => [
                    'cloudflare' => '1.1.1.1',
                    'google' => '8.8.8.8',
                ],
            ],
            $c
        );
    }
}
