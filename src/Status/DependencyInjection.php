<?php declare(strict_types=1);

namespace Status;

use DI;
use Exception;
use Psr\Container\ContainerInterface as Container;
use RunTracy\Helpers\Profiler\Profiler;
use Slim\Views\Twig;
use Status\Cache\IKeyStore;
use Twig\Extension\DebugExtension;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile as TwigProfile;

/**
 * Class DependencyInjection
 *
 * @package Status
 */
class DependencyInjection
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
        $builder->addDefinitions(
            [
                'settings' => [
                    'xdebugHelperIdeKey' => 'status',
                ],
                'config' => $config,
                'obLevel' => ob_get_level(),
                IKeyStore::class => DI\autowire("\Status\Cache\Apc")->constructorParameter('keyPrefix', '')
            ]
        );
        $di = $builder->build();

        $di = self::setUtilities($di);

        if ($di->get('config')['mode'] == 'development') {
            $di->set(TwigProfile::class, new TwigProfile());
        }

        $di->set(
            Twig::class,
            function (Container $di) {
                $dir = $di->get('config')['templates.path'];

                $config = [
                    'cache' => $di->get('config')['templates.cache_path'],
                    'strict_variables' => true,
                ];

                if ($di->get('config')['mode'] == 'development') {
                    $config['debug'] = true;
                    $config['auto_reload'] = true;
                }

                $view = Twig::create($dir, $config);

                $view->addExtension(new TwigExtension($di->get(Utilities\View::class)));

                $view->getEnvironment()->addGlobal('di', $di);

                if ($di->get('config')['mode'] == 'development') {
                    $view->addExtension(new DebugExtension());
                    $view->addExtension(new ProfilerExtension($di->get(TwigProfile::class)));
                }

                return $view;
            }
        );

        return $di;
    }

    /**
     * @param Container $di
     *
     * @return Container
     */
    private static function setUtilities(Container $di) : Container /** @formatter:off */
    {
        Profiler::start('setUtilities');

        $di->set(Utilities\Assets::class, DI\autowire());
        $di->set(Utilities\View::class, DI\autowire());

        Profiler::finish('setUtilities');

        return $di;
    }
    /** @formatter:on */
}
