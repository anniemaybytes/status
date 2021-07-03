<?php

declare(strict_types=1);

namespace Status\Route;

use Slim\Routing\RouteCollectorProxy;
use Status\Controller\IndexCtrl;

/**
 * Class Main
 *
 * @package Status\Route
 */
final class Main extends Base
{
    protected function addRoutes(): void
    {
        $app = $this->app;

        $app->group(
            '',
            function (RouteCollectorProxy $group) {
                $group->get('/', IndexCtrl::class . ':index')->setName('index');
                $group->group(
                    '/api',
                    function (RouteCollectorProxy $group) {
                        $group->get('/status', IndexCtrl::class . ':json')->setName('api:status');
                    }
                );
            }
        );
    }
}
