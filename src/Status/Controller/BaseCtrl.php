<?php

namespace Status\Controller;

use Slim\Http\Environment;
use Slim\Views\Twig;
use Status\Cache\Apc;
use Status\Utilities\View;

/**
 * Class BaseCtrl
 *
 * @package Status\Controller
 */
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

    /**
     * @param $di
     */
    public function setDependencies($di)
    {
        $this->config = $di['config'];
        $this->view_functions = $di['utility.view'];
        $this->environment = $di['environment'];
        $this->view = $di['view'];
        $this->cache = $di['cache'];
    }

    /**
     * BaseCtrl constructor.
     *
     * @param $di
     */
    public function __construct($di)
    {
        $this->di = $di;

        $this->setDependencies($di);
    }
}
