<?php declare(strict_types=1);

namespace Status\Controller;

use DI\Container;
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

    public function setDependencies()
    {
        $this->config = $this->di->get('config');
        $this->view_functions = $this->di->get('utility.view');
        $this->view = $this->di->get('view');
        $this->cache = $this->di->get('cache');
    }

    /**
     * BaseCtrl constructor.
     *
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;

        $this->setDependencies();
    }
}
