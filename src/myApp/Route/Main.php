<?php
namespace myApp\Route;

use myApp\Controller\IndexCtrl;

class Main extends Base
{
    protected function addRoutes()
    {
        $app = $this->app;

        $app->group('', function () {
            /** @var \Slim\App $this */
            $this->get('/', IndexCtrl::class . ':index')->setName('index');
            $this->get('/json', IndexCtrl::class . ':indexJson')->setName('index:json');
        });
    }
}
