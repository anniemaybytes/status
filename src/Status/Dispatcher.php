<?php

declare(strict_types=1);

namespace Status;

use ArrayAccess;
use Exception;
use Psr\Container\ContainerInterface as Container;
use Singleton\SingletonInterface;
use Singleton\SingletonTrait;
use Status\Config\Config;
use Status\Environment\CLI;
use Status\Environment\SAPI;

/**
 * Class Dispatcher
 *
 * @package Status
 */
final class Dispatcher implements SingletonInterface
{
    use SingletonTrait;

    private ArrayAccess $config;
    private Container $di;

    /** @throws Exception */
    protected function __construct()
    {
        $this->config = new Config();
        $this->di = DependencyInjection::build(
            PHP_SAPI === 'cli' ? CLI::definitions() : SAPI::definitions(),
            $this->config,
            $this->config['mode'] === 'production' && PHP_SAPI !== 'cli'
        );
    }

    public static function config(?string $key = null): mixed
    {
        if ($key) {
            return self::getInstance()->config[$key];
        }

        return self::getInstance()->config;
    }

    public static function di(): Container
    {
        return self::getInstance()->di;
    }
}
