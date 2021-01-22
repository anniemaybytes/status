<?php

declare(strict_types=1);

namespace Status;

use DI;
use Exception;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;
use RunTracy\Helpers\TwigPanel;
use Slim\Views\Twig;
use Status\Cache\Apc;
use Status\Cache\IKeyStore;
use Tracy\Debugger;
use Twig\Environment;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile as TwigProfile;

/**
 * Class DependencyInjection
 *
 * @package Status
 */
final class DependencyInjection
{
    /**
     * @param array $config
     *
     * @return Container
     * @throws Exception
     */
    public static function setup(array $config): Container
    {
        $builder = new DI\ContainerBuilder();
        $builder->useAnnotations(true);
        if ($config['mode'] === 'production') {
            $builder->enableCompilation(BASE_ROOT);
        }
        $builder->addDefinitions(
            [
                // utilities
                Utilities\Assets::class => DI\autowire()->constructorParameter('config', DI\get('config')),
                Utilities\View::class => DI\autowire(),
                // runtime
                IKeyStore::class => DI\autowire(Apc::class)->constructorParameter('keyPrefix', ''),
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
                        'cache' => $di->get('config')['templates.cache_path'] ?? '/tmp/twig-cache',
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

                    $view->addExtension(new TwigExtension($di->get(Utilities\View::class)));

                    return $view;
                }
            ]
        );
        $di = $builder->build();

        // dynamic definitions
        $di->set('config', $config);
        $di->set('obLevel', ob_get_level());

        return $di;
    }
}
