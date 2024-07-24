<?php

declare(strict_types=1);

namespace Status\Route;

use Psr\Container\ContainerInterface;
use Slim\App;

/**
 * Class Base
 *
 * @package Status\Route
 */
abstract class Base
{
    /** @var App<ContainerInterface> */
    protected App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->addRoutes();
    }

    abstract protected function addRoutes(): void;
}
