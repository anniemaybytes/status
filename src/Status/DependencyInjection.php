<?php declare(strict_types=1);

namespace Status;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use RunTracy\Helpers\Profiler\Profiler;
use Slim\Views\Twig;
use Twig\Extension\DebugExtension;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;

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
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $builder->addDefinitions(
            [
                'settings' => [
                    'xdebugHelperIdeKey' => 'status',
                ]
            ]
        );
        $di = $builder->build();

        $di->set('config', $config);
        $di->set('obLevel', ob_get_level());

        $di = self::setUtilities($di);

        if ($di->get('config')['mode'] == 'development') {
            $di->set(
                'twig_profile',
                function () {
                    return new Profile();
                }
            );
        }

        $di->set(
            'view',
            function ($di) {
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

                $view->addExtension(new TwigExtension($di->get('utility.view')));

                $view->getEnvironment()->addGlobal('di', $di);

                if ($di->get('config')['mode'] == 'development') {
                    $view->addExtension(new DebugExtension());
                    $view->addExtension(new ProfilerExtension($di->get('twig_profile')));
                }

                return $view;
            }
        );

        $di->set('cache', new Cache\Apc());

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

        $di->set('utility.assets', function (Container $di) {
            return new Utilities\Assets($di);
        });

        $di->set('utility.view', function (Container $di) {
            return new Utilities\View($di);
        });

        Profiler::finish('setUtilities');

        return $di;
    }
    /** @formatter:on */
}
