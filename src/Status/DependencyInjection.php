<?php declare(strict_types=1);

namespace Status;

use RunTracy\Helpers\Profiler\Exception\ProfilerException;
use RunTracy\Helpers\Profiler\Profiler;
use Slim\Container;
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
     * @param array $args
     *
     * @return Container
     * @throws ProfilerException
     */
    public static function get(array $config, array $args = []) : Container
    {
        if (!$args) {
            $args = [
                'settings' => [
                    'displayErrorDetails' => ($config['mode'] == 'development'),
                    'determineRouteBeforeAppMiddleware' => true,
                    'addContentLengthHeader' => false,
                    'xdebugHelperIdeKey' => 'status',
                ]
            ];
        }

        $di = new Container($args);

        $di['obLevel'] = ob_get_level();

        $di['config'] = $config;

        $di = self::setUtilities($di);

        if ($di['config']['mode'] == 'development') {
            $di['twig_profile'] = function () {
                return new Profile();
            };
        }

        $di['view'] = function ($di) {
            $dir = $di['config']['templates.path'];

            $config = [
                'cache' => $di['config']['templates.cache_path'],
                'strict_variables' => true,
            ];

            if ($di['config']['mode'] == 'development') {
                $config['debug'] = true;
                $config['auto_reload'] = true;
            }

            $view = new Twig($dir, $config);

            $view->addExtension(new TwigExtension($di['utility.view']));

            $view->getEnvironment()->addGlobal('di', $di);

            if ($di['config']['mode'] == 'development') {
                $view->addExtension(new DebugExtension());
                $view->addExtension(new ProfilerExtension($di['twig_profile']));
            }

            return $view;
        };

        $di['cache'] = new Cache\Apc();

        $di['notFoundHandler'] = function () {
            // delegate to the error handler
            throw new Exception\NotFound();
        };
        $di['notAllowedHandler'] = function ($di) {
            // let's pretend it doesn't exist
            throw new Exception\NotFound();
        };

        if ($config['mode'] != 'development') {
            $di['errorHandler'] = function ($di) {
                $ctrl = new Controller\ErrorCtrl($di);
                return [$ctrl, 'handleException'];
            };
            $di['phpErrorHandler'] = function ($di) {
                $ctrl = new Controller\FatalErrorCtrl($di);
                return [$ctrl, 'handleError'];
            };
        } else {
            unset($di['errorHandler']);
            unset($di['phpErrorHandler']);
        }

        return $di;
    }

    /**
     * @param Container $di
     *
     * @return Container
     * @throws ProfilerException
     */
    private static function setUtilities(Container $di) : Container /** @formatter:off */
    {
        Profiler::start('setUtilities');

        $di['utility.assets'] = function (Container $di) {
            return new Utilities\Assets($di);
        };

        $di['utility.view'] = function (Container $di) {
            return new Utilities\View($di);
        };

        Profiler::finish('setUtilities');

        return $di;
    }
    /** @formatter:on */
}
