<?php declare(strict_types=1);

namespace Status\Middleware;

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RunTracy\Helpers\Profiler\Profiler;
use Slim\MiddlewareDispatcher;

/**
 * Class RequestHelper
 *
 * Provides accurate 'request' to the Container
 * It should be last middleware to run
 *
 * @package Status\Middleware
 */
class RequestHelper
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * RouteHelper constructor.
     *
     * @param Container $di
     */
    public function __construct(Container &$di)
    {
        $this->di = &$di;
    }

    /**
     * @param Request $request
     * @param MiddlewareDispatcher $handler
     *
     * @return Response
     */
    public function __invoke(Request $request, $handler): Response
    {
        Profiler::start('requestHelperMiddleware');
        $this->di->set('request', $request);
        Profiler::finish('requestHelperMiddleware');
        return $handler->handle($request);
    }
}