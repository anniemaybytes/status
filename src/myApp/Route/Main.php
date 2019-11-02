<?php

namespace myApp\Route;

use myApp\Controller\IndexCtrl;
use Slim\App;

class Main extends Base
{
    protected function addRoutes()
    {
        $app = $this->app;

        $app->group('', function () {
            /** @var App $this */
            $this->get('/', IndexCtrl::class . ':index')->setName('index');
            /** @var App $this */
            $this->get('/json', IndexCtrl::class . ':indexJson')->setName('index:json');
        });
    }
}
