<?php
namespace myApp;

use Slim\Container;
use Slim\Views\Twig;

class DependencyInjection
{
    /**
     * @param $config
     * @param array $args
     * @return mixed|Container
     */
    public static function get($config, $args = array())
    {
        if (!$args) {
            $args = array(
                'settings' => array(
                    'displayErrorDetails' => ($config['mode'] == 'development'),
                    'determineRouteBeforeAppMiddleware' => true,
                    'addContentLengthHeader' => false,
                )
            );
        }

        $di = new Container($args);

        $di['config'] = $config;

        $di = self::setUtilities($di);

        $di['view'] = function ($di) {
            $dir = $di['config']['templates.path'];

            $config = array(
                'cache' => $di['config']['templates.cache_path'],
            );

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

            return $view;
        };

        $di['cache'] = new Cache\Apc();

        $di['notFoundHandler'] = function () {
            // delegate to the error handler
            throw new Exception\NotFound();
        };

        if ($config['mode'] != 'development') {
            $di['errorHandler'] = function ($di) {
                $ctrl = new Controller\ErrorCtrl($di);
                return array($ctrl, 'handleException');
            };
        }

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
