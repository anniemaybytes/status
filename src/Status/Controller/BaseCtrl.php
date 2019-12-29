<?php declare(strict_types=1);

namespace Status\Controller;

use Slim\Container;
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
    /**
     * @var Container $di
     */
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

    public function setDependencies()
    {
        $this->config = $this->di['config'];
        $this->view_functions = $this->di['utility.view'];
        $this->environment = $this->di['environment'];
        $this->view = $this->di['view'];
        $this->cache = $this->di['cache'];
    }

    /**
     * BaseCtrl constructor.
     *
     * @param Container $di
     */
    public function __construct(Container &$di)
    {
        $this->di = &$di;

        $this->setDependencies();
    }
}
