<?php

declare(strict_types=1);

namespace Status\Controller;

use ArrayAccess;
use DI\Attribute\Inject;
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
    #[Inject]
    protected IKeyStore $cache;

    #[Inject]
    protected Twig $view;

    #[Inject]
    protected View $viewFunctions;

    #[Inject("config")]
    protected ArrayAccess $config;
}
