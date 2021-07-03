<?php

declare(strict_types=1);

namespace Status\Middleware;

use Exception;
use ParagonIE\CSPBuilder\CSPBuilder;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RunTracy\Helpers\Profiler\Profiler;
use Tracy\Debugger;

/**
 * Class ContentSecurityPolicy
 *
 * @package Status\Middleware
 */
final class SecurityHeaders implements MiddlewareInterface
{
    protected static array $corsAllowList = [
        '/api',
    ];
    protected static array $cspOverrideList = [
        '/api',
    ];
    private Container $di;

    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $response = $handler->handle($request);

        Profiler::start('cspMiddleware');
        $response = self::applyHeaders($this->di, $request, $response);
        Profiler::finish('cspMiddleware');

        return $response;
    }

    public static function applyHeaders(Container $di, Request $request, Response $response): Response
    {
        $uri = $request->getUri();

        /*
         *  -------------------------------------------------------
         * |        Cross-Origin Resource Sharing                 |
         * -------------------------------------------------------
         */
        foreach (self::$corsAllowList as $path) {
            if (str_starts_with($uri->getPath(), $path)) {
                $response = $response->withHeader('Access-Control-Allow-Origin', '*');
            }
        }

        /*
         *  -------------------------------------------------------
         * |             Content Security Policy                  |
         * -------------------------------------------------------
         */
        foreach (self::$cspOverrideList as $path) {
            if (str_starts_with($uri->getPath(), $path)) {
                return $response;
            }
        }

        try {
            $csp = CSPBuilder::fromFile('../config/csp.json');

            if ($di->get('config')['mode'] === 'development') {
                $csp->setAllowUnsafeInline('style-src', true);
                $csp->setAllowUnsafeInline('script-src', true);
                $csp->setDataAllowed('img-src', true);
            }

            $headers = $csp->getHeaderArray();
            foreach ($headers as $key => $value) {
                $response = $response->withHeader($key, $value);
            }
        } catch (Exception $e) {
            Debugger::log($e, Debugger::WARNING);
        }

        return $response;
    }
}
