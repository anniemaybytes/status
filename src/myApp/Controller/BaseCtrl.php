<?php

namespace myApp\Controller;

use myApp\Cache\Apc;
use myApp\Utilities\View;
use Slim\Http\Environment;
use Slim\Views\Twig;

abstract class BaseCtrl
{
    protected $di;

    /**
     * @var Apc
     */
    protected $cache;

    /**
     * @var Twig
     */
    protected $view;

    /**
     * @var View
     */
    protected $view_functions;

    /**
     * The configuration array
     */
    protected $config;

    /**
     * @var Environment
     */
    protected $environment;

    public function setDependencies($di)
    {
        $this->config = $di['config'];
        $this->view_functions = $di['utility.view'];
        $this->environment = $di['environment'];
        $this->view = $di['view'];
        $this->cache = $di['cache'];
    }

    public function __construct($di)
    {
        $this->di = $di;

        $this->setDependencies($di);
    }
}
