<?php declare(strict_types=1);

namespace Status\Route;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
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

        $app->group('', function (RouteCollectorProxy $group) {
            /** @var App $this */
            $group->get('/', IndexCtrl::class . ':index')->setName('index');
            /** @var App $this */
            $group->get('/json', IndexCtrl::class . ':indexJson')->setName('index:json');
        });
    }
    /** @formatter:on */
}
