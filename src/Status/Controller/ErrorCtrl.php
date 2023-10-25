<?php

declare(strict_types=1);

namespace Status\Controller;

use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Throwable;
use Tracy\Debugger;

/**
 * Class ErrorCtrl
 *
 * @package Status\Controller
 */
final class ErrorCtrl extends BaseCtrl
{
    private int $obLevel;

    private Container $di;

    public function __construct(Container $di)
    {
        $this->di = $di;
        $this->obLevel = $di->get('obLevel');
    }

    public function handleException(Request $request, Response $response, Throwable $exception): Response
    {
        try {
            $code = 500;
            if ($exception instanceof HttpException) {
                $code = $exception->getCode();
            }

            if ($code === 500) {
                Debugger::log($exception, Debugger::EXCEPTION);
            }

            // clear the body first
            $body = $response->getBody();
            $body->rewind();
            $response = $response->withBody($body);

            // clear output buffer
            while (ob_get_level() > $this->obLevel) {
                $status = ob_get_status();
                if (in_array($status['name'], ['ob_gzhandler', 'zlib output compression'], true)) {
                    break;
                }
                if (!@ob_end_clean()) { // @ may be not removable
                    break;
                }
            }

            return $response->withStatus($code);
        } catch (Throwable $e) {
            return (new FatalErrorCtrl($this->di))->handleError($request, $response, $e);
        }
    }
}
