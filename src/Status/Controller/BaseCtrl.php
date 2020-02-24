<?php

declare(strict_types=1);

namespace Status\Controller;

use Slim\Views\Twig;
use Status\Cache\IKeyStore;
use Status\Utilities\View;

/**
 * Class BaseCtrl
 *
 * @package Status\Controller
 */
abstract class BaseCtrl
{
    /**
     * @Inject
     * @var IKeyStore
     */
    protected $cache;

    /**
     * @Inject
     * @var Twig
     */
    protected $view;

    /**
     * @Inject
     * @var View
     */
    protected $view_functions;

    /**
     * The configuration array
     *
     * @Inject("config")
     * @var array
     */
    protected $config;
}
