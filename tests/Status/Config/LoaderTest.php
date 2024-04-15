<?php

declare(strict_types=1);

use org\bovigo\vfs;
use Status\Config\Loader;

/**
 * Class ConfigLoaderTest
 */
class LoaderTest extends PHPUnit\Framework\TestCase
{
    private vfs\vfsStreamDirectory $root;

    public function setup(): void
    {
        $this->root = vfs\vfsStream::setup(self::class);
        vfs\vfsStream::copyFromFileSystem(__DIR__ . '/config', $this->root);
    }

    /**
     * Config loader should bork if configs are missing
     */
    public function test_should_fail_if_config_missing(): void
    {
        $this->expectException(RuntimeException::class);
        Loader::load($this->root->url() . '/fail.ini');
    }

    /**
     * Config loader should correctly load file
     */
    public function test_check_load_file(): void
    {
        $c = Loader::parse(Loader::load($this->root->url() . '/load.ini'));
        self::assertArrayHasKey('mode', $c);
        self::assertEquals('development', $c['mode']);
    }

    /**
     * Config loader should correctly parse simple structures
     */
    public function test_parses_simple_ini(): void
    {
        self::assertEquals(
            [
                'mode' => 'development',
                'logs_dir' => '/code/logs',
                'app.site_name' => 'AnimeBytes',
                'static.location' => '/static/'
            ],
            Loader::parse(Loader::load($this->root->url() . '/simple.ini'))
        );
    }

    /**
     * Config loader should correctly parse complex structures
     */
    public function test_parses_complex_ini(): void
    {
        self::assertEquals(
            [

                'tracker.domain' => 'tracker.animebytes.local:34000',
                'tracker.ns' => [
                    'localhost' => '10.0.0.1',
                ],
            ],
            Loader::parse(Loader::load($this->root->url() . '/complex.ini'))
        );
    }
}
