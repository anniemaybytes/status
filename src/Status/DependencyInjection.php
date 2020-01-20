<?php declare(strict_types=1);

namespace Status;

use DI;
use Exception;
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
     * @return DI\Container
     * @throws Exception
     */
    public static function setup(array $config): DI\Container
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
            function (DI\Container $di) {
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
     * @param DI\Container $di
     *
     * @return DI\Container
     */
    private static function setUtilities(DI\Container $di) : DI\Container /** @formatter:off */
    {
        Profiler::start('setUtilities');

        $di->set(Utilities\Assets::class, DI\autowire());
        $di->set(Utilities\View::class, DI\autowire());

        Profiler::finish('setUtilities');

        return $di;
    }
    /** @formatter:on */
}
