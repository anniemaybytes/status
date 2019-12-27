<?php

namespace Status\Route;

use Slim\App;
use Status\Controller\IndexCtrl;

/**
 * Class Main
 *
 * @package Status\Route
 */
class Main extends Base
{
    protected function addRoutes() /** @formatter:off */
    {
        $app = $this->app;

        $app->group('', function () {
            /** @var App $this */
            $this->get('/', IndexCtrl::class . ':index')->setName('index');
            /** @var App $this */
            $this->get('/json', IndexCtrl::class . ':indexJson')->setName('index:json');
        });
    }
    /** @formatter:on */
}
