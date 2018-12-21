<?php
namespace myApp\Route;

class Main extends Base
{
  protected function addRoutes()
  {
    $app = $this->app;

    $app->group('', function () {
      /** @var \Slim\App $this */
      $this->get('/', \myApp\Controller\IndexCtrl::class . ':index')->setName('index');
      $this->get('/json', \myApp\Controller\IndexCtrl::class . ':indexJson')->setName('index:json');
    });
  }
}
