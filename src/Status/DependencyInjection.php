<?php

namespace Status;

use Slim\Container;
use Slim\Views\Twig;
use Twig\Extension\DebugExtension;
use Twig\Extension\ProfilerExtension;
use Twig\Profiler\Profile;

class DependencyInjection
{
    /**
     * @param $config
     * @param array $args
     * @return mixed|Container
     */
    public static function get($config, $args = [])
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
            ];

            if ($di['config']['mode'] == 'development') {
                $config['debug'] = true;
                $config['auto_reload'] = true;
                $config['strict_variables'] = true;
            }

            $view = new Twig($dir, $config);

            $view->addExtension(new TwigExtension($di['utility.view']));

            $view->getEnvironment()->addGlobal('di', $di);

            $constants = get_defined_constants();
            foreach ($constants as $name => $value) {
                $view->getEnvironment()->addGlobal($name, $value);
            }

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

        unset($di['phpErrorHandler']);
        if ($config['mode'] != 'development') {
            $di['errorHandler'] = function ($di) {
                $ctrl = new Controller\ErrorCtrl($di);
                return [$ctrl, 'handleException'];
            };
        } else unset($di['errorHandler']);

        return $di;
    }

    private static function setUtilities($di)
    {
        $di['utility.assets'] = function ($di) {
            return new Utilities\Assets($di);
        };

        $di['utility.view'] = function ($di) {
            return new Utilities\View($di);
        };

        return $di;
    }
}
