<?php declare(strict_types=1);

namespace Status;

use Exception;
use RunTracy\Helpers\Profiler\Exception\ProfilerException;
use RunTracy\Helpers\Profiler\Profiler;
use Slim\App;
use Slim\Container;
use Status\Route as R;

/**
 * Class Dispatcher
 *
 * @package Status
 */
class Dispatcher extends Singleton
{
    /** @var App $app */
    private $app;

    private $config;

    private $di;

    /**
     * Returns the slim application object
     *
     * @return App
     */
    public static function app() : App
    {
        return self::getInstance()->app;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public static function config($key)
    {
        return self::getInstance()->config[$key];
    }

    /**
     * @return array
     */
    public static function getConfig() : array
    {
        return self::getInstance()->config;
    }

    /**
     * Returns the container object
     *
     * @return Container
     */
    public static function &di() : Container
    {
        return self::getInstance()->di;
    }

    /**
     * @throws ProfilerException
     */
    private function initConfig()
    {
        Profiler::start('initConfig');
        $config = ConfigLoader::load();
        Profiler::finish('initConfig');

        $config['templates.path'] = BASE_ROOT . '/' . $config['templates.path'];
        $config['logs_dir'] = BASE_ROOT . '/' . $config['logs_dir'];
        $this->config = $config;
    }

    /**
     * @throws ProfilerException
     */
    private function initDependencyInjection()
    {
        Profiler::start('initDependencyInjection');
        $this->di = DependencyInjection::get($this->config);
        Profiler::finish('initDependencyInjection');
    }

    /**
     * @throws ProfilerException
     */
    private function initApplication()
    {
        $app = new App($this->di);

        Profiler::start('initRoutes');
        $routes = [
            new R\Main($app),
        ];
        Profiler::finish('initRoutes');

        $this->di['routes'] = $routes;

        $this->app = $app;
    }

    /**
     * Dispatcher constructor.
     *
     * @param $args
     *
     * @throws Exception
     */
    protected function __construct($args)
    {
        $this->initConfig();
        $this->initDependencyInjection();
        $this->initApplication();
        parent::__construct($args);
    }
}
