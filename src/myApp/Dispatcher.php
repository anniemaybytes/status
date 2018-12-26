<?php
namespace myApp;

use myApp\Route as R;

class Dispatcher extends Singleton
{
    /** @var \Slim\App $app */
    private $app;

    private $config;

    private $di;

    /**
     * Returns the slim application object
     *
     * @return \Slim\App
     */
    public static function app()
    {
        return self::getInstance()->app;
    }

    public static function config($key)
    {
        return self::getInstance()->config[$key];
    }

    public static function getConfig()
    {
        return self::getInstance()->config;
    }

    /**
     * Returns the container object
     *
     * @return \Slim\Container
     */
    public static function di()
    {
        return self::getInstance()->di;
    }

    private function initConfig()
    {
        $config = ConfigLoader::load();
        $config['templates.path'] = BASE_ROOT . '/' . $config['templates.path'];
        $config['templates.cache_path'] = BASE_ROOT . '/' . $config['templates.cache_path'];
        if(!isset($config['site_root'])) $config['site_root'] = '';
        $this->config = $config;
    }

    private function initDependencyInjection()
    {
        $di = DependencyInjection::get($this->config);
        $this->di = $di;
    }

    private function initApplication()
    {
        $app = new \Slim\App($this->di);

        $routes = array(
            new R\Main($app),
        );

        $this->di['routes'] = $routes;

        $this->app = $app;
    }

    protected function __construct($args)
    {
        $this->initConfig();
        $this->initDependencyInjection();
        $this->initApplication();
        parent::__construct($args);
    }
}