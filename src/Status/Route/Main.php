<?php /** @noinspection StaticClosureCanBeUsedInspection */

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
    protected function addRoutes(): void /** @formatter:off */
    {
        $app = $this->app;

        $app->group('', function (RouteCollectorProxy $group) {
            $group->get('/', IndexCtrl::class . ':index')->setName('index');
            $group->get('/json', IndexCtrl::class . ':indexJson')->setName('index:json');
        });
    }
    /** @formatter:on */
}
