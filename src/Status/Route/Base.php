<?php

declare(strict_types=1);

namespace Status\Route;

use Slim\App;

/**
 * Class Base
 *
 * @package Status\Route
 */
abstract class Base
{
    protected App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->addRoutes();
    }

    abstract protected function addRoutes(): void;
}
