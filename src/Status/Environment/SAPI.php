<?php

declare(strict_types=1);

namespace Status\Environment;

use Psr\Container\ContainerInterface as Container;
use RuntimeException;
use RunTracy\Helpers\TwigPanel;
use Slim\HttpCache\CacheProvider;
use Slim\Views\Twig;
use Status\Cache\Apc;
use Status\Cache\IKeyStore;
use Status\TwigExtension;
use Status\Utilities\Assets;
use Status\Utilities\View;
use Tracy\Debugger;
use Twig\Environment;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile as TwigProfile;

use function DI\autowire;
use function DI\get;

/**
 * Class SAPI
 *
 * @package Status\Environment
 */
final class SAPI
{
    public static function definitions(): array
    {
        return [
            // utilities
            Assets::class => autowire()->constructorParameter('config', get('config')),
            View::class => autowire(),
            // slim
            CacheProvider::class => autowire(),
            // runtime
            IKeyStore::class => autowire(Apc::class),
            Twig::class => function (Container $di) {
                $dir = BASE_ROOT . '/templates';
                $dirs = [$dir];

                $dh = opendir($dir);
                if (!$dh) {
                    throw new RuntimeException('Unable to open templates path');
                }

                while (false !== ($filename = readdir($dh))) {
                    $fullPath = "$dir/$filename";
                    if ($filename[0] !== '.' && is_dir($fullPath)) {
                        $dirs[$filename] = $fullPath;
                    }
                }

                $config = [
                    'cache' => $di->get('config')['templates.cache_path'],
                    'strict_variables' => true,
                ];

                if ($di->get('config')['mode'] === 'development') {
                    $config['debug'] = true;
                    $config['auto_reload'] = true;
                }

                $view = Twig::create($dirs, $config);

                if ($di->get('config')['mode'] === 'development') {
                    $profiler = new TwigProfile();
                    $view->addExtension(new ProfilerExtension($profiler));
                    Debugger::getBar()->addPanel(new TwigPanel($profiler, Environment::VERSION));
                }

                $view->addExtension(new TwigExtension($di->get(View::class)));

                return $view;
            }
        ];
    }
}
