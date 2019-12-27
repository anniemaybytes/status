<?php

namespace Status\Route;

use Slim\App;

/**
 * Class Base
 *
 * @package Status\Route
 */
abstract class Base
{
    /**
     * @var App
     */
    protected $app;

    /**
     * Base constructor.
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->addRoutes();
    }

    abstract protected function addRoutes();
}
