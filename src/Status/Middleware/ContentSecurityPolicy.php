<?php

declare(strict_types=1);

namespace Status\Middleware;

use ArrayAccess;
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
final class ContentSecurityPolicy implements MiddlewareInterface
{
    private Container $di;

    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $response = $handler->handle($request);

        Profiler::start(__CLASS__ . '::' . __METHOD__);
        $response = self::applyHeaders($this->di->get('config'), $request, $response);
        Profiler::finish(__CLASS__ . '::' . __METHOD__);

        return $response;
    }

    public static function applyHeaders(ArrayAccess $config, Request $request, Response $response): Response
    {
        try {
            $csp = CSPBuilder::fromFile('../config/csp.json');

            if ($config['mode'] === 'development') {
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
