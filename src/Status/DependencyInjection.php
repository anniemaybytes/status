<?php /** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace Status;

use DI;
use Exception;
use Psr\Container\ContainerInterface as Container;
use RunTracy\Helpers\TwigPanel;
use Slim\Views\Twig;
use Status\Cache\Apc;
use Status\Cache\IKeyStore;
use Tracy\Debugger;
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
                Utilities\Assets::class => DI\autowire(),
                Utilities\View::class => DI\autowire(),
                // runtime
                IKeyStore::class => DI\autowire(Apc::class)->constructorParameter('keyPrefix', ''),
                Twig::class => function (Container $di) {
                    $dir = $di->get('config')['templates.path'];

                    $config = [
                        'cache' => $di->get('config')['templates.cache_path'],
                        'strict_variables' => true,
                    ];

                    if ($di->get('config')['mode'] === 'development') {
                        $config['debug'] = true;
                        $config['auto_reload'] = true;
                    }

                    $view = Twig::create($dir, $config);

                    if ($di->get('config')['mode'] === 'development') {
                        $profiler = new TwigProfile();
                        $view->addExtension(new ProfilerExtension($profiler));
                        Debugger::getBar()->addPanel(new TwigPanel($profiler));
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
